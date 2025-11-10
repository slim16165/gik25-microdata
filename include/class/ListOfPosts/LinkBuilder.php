<?php
namespace gik25microdata\ListOfPosts;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\Utility\MyString;

/**
 * Builder unificato per la creazione di link
 * 
 * Astrae la logica di creazione link specifica dei singoli siti,
 * rendendola riutilizzabile e configurabile.
 */
class LinkBuilder
{
    private LinkConfig $linkConfig;
    
    /**
     * @param bool $removeIfSelf Se true, rimuove il link se punta al post corrente
     * @param bool $withImage Se true, include l'immagine in evidenza
     * @param bool $linkSelf Se true, crea comunque un link anche se punta al post corrente
     * @param string $listOfPostsStyle Stile CSS per la lista
     */
    public function __construct(
        bool $removeIfSelf = false,
        bool $withImage = true,
        bool $linkSelf = false,
        string $listOfPostsStyle = ''
    ) {
        $this->linkConfig = new LinkConfig($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle);
    }
    
    /**
     * Crea un singolo link con immagine
     * 
     * @param string|null $target_url URL del post di destinazione
     * @param string|null $nome Testo del link
     * @param string|null $commento Commento opzionale
     * @return string HTML del link
     */
    public function buildLink(?string $target_url, ?string $nome, ?string $commento = ""): string
    {
        if ($target_url === null || $nome === null) {
            return "";
        }
        
        list($target_post, $noLink, $debugMsg) = WPPostsHelper::GetPostData($target_url, $this->linkConfig->removeIfSelf);
        
        $commento = ListOfPostsHelper::ParseComment($commento ?? "");
        
        if ($debugMsg) {
            $target_post = null;
        }
        
        if ($this->linkConfig->withImage) {
            return HtmlTemplate::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
        } else {
            return HtmlTemplate::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);
        }
    }
    
    /**
     * Crea un link semplice (senza immagine) che non punta a se stesso
     * 
     * @param string $url URL del post
     * @param string $nome Testo del link
     * @return string HTML del link o testo se punta al post corrente
     */
    public function buildSimpleLink(string $url, string $nome): string
    {
        global $post;
        
        if (!$post || !isset($post->ID)) {
            return "<a href=\"{$url}\">{$nome}</a>";
        }
        
        $permalink = get_permalink($post->ID);
        $url = WPPostsHelper::ReplaceTargetUrlIfStaging($url);
        
        if ($permalink !== $url) {
            return "<a href=\"{$url}\">{$nome}</a>";
        } else {
            return $nome;
        }
    }
    
    /**
     * Crea un link con immagine per caroselli (compatibile con ColorWidget)
     * 
     * @param string $target_url URL del post
     * @param string $nome Testo del link
     * @return string HTML del link per carosello
     */
    public function buildCarouselLink(string $target_url, string $nome): string
    {
        $target_url = WPPostsHelper::ReplaceTargetUrlIfStaging($target_url);
        $target_postid = url_to_postid($target_url);
        
        if ($target_postid == 0) {
            return "";
        }
        
        $target_post = get_post($target_postid);
        
        if (!$target_post || $target_post->post_status !== "publish") {
            return "";
        }
        
        $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
        
        return $this->getCarouselLinkTemplate($target_url, $nome, $featured_img_url);
    }
    
    /**
     * Template per link carosello (compatibile con ColorWidget)
     * 
     * @param string $target_url URL del post
     * @param string $nome Testo del link
     * @param string|null $featured_img_url URL dell'immagine
     * @return string HTML
     */
    private function getCarouselLinkTemplate(string $target_url, string $nome, ?string $featured_img_url): string
    {
        if ($featured_img_url === null) {
            $featured_img_url = plugins_url() . '/gik25-microdata/assets/images/placeholder-200x200.png';
        }
        
        // Sanitizza i dati per sicurezza
        $safe_url = esc_url($target_url);
        $safe_nome = esc_html($nome);
        $safe_img_url = esc_url($featured_img_url);
        
        return <<<HTML
<div class="tile" role="button" tabindex="0" aria-label="Vai a {$safe_nome}">        
    <a href="{$safe_url}" class="tile__link" aria-label="{$safe_nome}">
        <div class="tile__media">
            <img class="tile__img" src="{$safe_img_url}" alt="{$safe_nome}" loading="lazy" />
        </div>        
        <div class="tile__details">
            <div class="tile__title">
                {$safe_nome}
            </div>
        </div>
    </a>        
</div>
HTML;
    }
    
    /**
     * Crea una lista di link da un array di dati
     * 
     * @param array<array{target_url: string, nome: string, commento?: string}> $links_data Array di link
     * @return string HTML della lista
     */
    public function buildLinksList(array $links_data): string
    {
        $helper = new ListOfPostsHelper(
            $this->linkConfig->removeIfSelf,
            $this->linkConfig->withImage,
            $this->linkConfig->linkSelf,
            $this->linkConfig->listOfPostsStyle
        );
        
        return $helper->GetLinksWithImages($links_data);
    }
    
    /**
     * Crea una lista di link da una Collection
     * 
     * @param \Illuminate\Support\Collection<LinkBase> $collection Collection di link
     * @return string HTML della lista
     */
    public function buildLinksFromCollection(\Illuminate\Support\Collection $collection): string
    {
        $helper = new ListOfPostsHelper(
            $this->linkConfig->removeIfSelf,
            $this->linkConfig->withImage,
            $this->linkConfig->linkSelf,
            $this->linkConfig->listOfPostsStyle
        );
        
        return $helper->getLinksWithImagesCurrentColumn($collection);
    }
    
    /**
     * Factory method per creare un builder con configurazione predefinita
     * 
     * @param string $preset Nome del preset ('default', 'carousel', 'simple', 'no_image')
     * @return self
     */
    public static function create(string $preset = 'default'): self
    {
        return match($preset) {
            'carousel' => new self(false, true, false, ''),
            'simple' => new self(false, false, false, ''),
            'no_image' => new self(false, false, false, ''),
            default => new self(false, true, false, ''),
        };
    }
}
