<?php
namespace gik25microdata\ListOfPosts;

use gik25microdata\ListOfPosts\Renderer\LinkRendererInterface;
use gik25microdata\ListOfPosts\Renderer\StandardLinkRenderer;
use gik25microdata\ListOfPosts\Renderer\CarouselLinkRenderer;
use gik25microdata\ListOfPosts\Renderer\SimpleLinkRenderer;
use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Ul;
use Yiisoft\Html\Tag\Div;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builder unificato per la creazione di link
 * Astrae la logica di creazione link e permette diversi stili di rendering
 */
class LinkBuilder
{
    private LinkRendererInterface $renderer;
    private array $defaultOptions;
    
    /**
     * @param string $style Stile di rendering: 'standard', 'carousel', 'simple'
     * @param array $options Opzioni di default per il renderer
     */
    public function __construct(string $style = 'standard', array $options = [])
    {
        $this->defaultOptions = $options;
        $this->renderer = self::createRenderer($style, $options);
    }
    
    /**
     * Factory method per creare il renderer appropriato
     */
    private static function createRenderer(string $style, array $options): LinkRendererInterface
    {
        switch ($style) {
            case 'carousel':
                return new CarouselLinkRenderer();
                
            case 'simple':
                $removeIfSelf = $options['removeIfSelf'] ?? true;
                return new SimpleLinkRenderer($removeIfSelf);
                
            case 'standard':
            default:
                $removeIfSelf = $options['removeIfSelf'] ?? true;
                $withImage = $options['withImage'] ?? true;
                $linkSelf = $options['linkSelf'] ?? false;
                return new StandardLinkRenderer($removeIfSelf, $withImage, $linkSelf);
        }
    }
    
    /**
     * Crea un link singolo
     * 
     * @param string $url URL del link
     * @param string $title Titolo del link
     * @param string $comment Commento opzionale
     * @param array $options Opzioni aggiuntive
     * @return string HTML del link
     */
    public function createLink(string $url, string $title, string $comment = '', array $options = []): string
    {
        $link = new LinkBase($title, $url, $comment);
        $mergedOptions = array_merge($this->defaultOptions, $options);
        return $this->renderer->render($link, $mergedOptions);
    }
    
    /**
     * Crea una lista di link da array
     * 
     * @param array $links Array di array ['target_url' => ..., 'nome' => ..., 'commento' => ...]
     * @param array $options Opzioni aggiuntive
     * @return string HTML della lista
     */
    public function createLinksFromArray(array $links, array $options = []): string
    {
        $collection = new Collection();
        foreach ($links as $linkData) {
            $url = $linkData['target_url'] ?? $linkData['url'] ?? '';
            $title = $linkData['nome'] ?? $linkData['title'] ?? '';
            $comment = $linkData['commento'] ?? $linkData['comment'] ?? '';
            if (!empty($url) && !empty($title)) {
                $collection->add(new LinkBase($title, $url, $comment));
            }
        }
        return $this->createLinksFromCollection($collection, $options);
    }
    
    /**
     * Crea una lista di link da Collection
     * 
     * @param Collection $links Collezione di LinkBase
     * @param array $options Opzioni aggiuntive (es. ['nColumns' => 2, 'ulClass' => 'nicelist'])
     * @return string HTML della lista
     */
    public function createLinksFromCollection(Collection $links, array $options = []): string
    {
        if ($links->isEmpty()) {
            return '';
        }
        
        $mergedOptions = array_merge($this->defaultOptions, $options);
        $nColumns = $mergedOptions['nColumns'] ?? 1;
        $ulClass = $mergedOptions['ulClass'] ?? 'thumbnail-list';
        $wrapInDiv = $mergedOptions['wrapInDiv'] ?? true;
        
        $linksHtml = $this->renderer->renderCollection($links, $mergedOptions);
        
        if ($nColumns > 1 && $this->renderer->supports('nColumns')) {
            $linksHtml = $this->renderMultiColumn($links, $nColumns, $ulClass, $mergedOptions);
        } else {
            if ($wrapInDiv) {
                $linksHtml = Html::div()->class($ulClass)->content($linksHtml)->encode(false)->render();
            }
            $linksHtml = Html::ul()->class($ulClass)->content($linksHtml)->encode(false)->render();
        }
        
        return $linksHtml;
    }
    
    /**
     * Renderizza link in più colonne
     */
    private function renderMultiColumn(Collection $links, int $nColumns, string $ulClass, array $options): string
    {
        $links_per_column_arr = Util::PaginateArray($links, $nColumns);
        $links_html = '';
        $cssDivClass = sprintf("list-of-posts-layout-%s", $nColumns);
        
        foreach ($links_per_column_arr as $links_col_x) {
            $currentColumn = $this->renderer->renderCollection($links_col_x, $options);
            $columnHtml = Html::ul()->class($ulClass)->content($currentColumn)->encode(false)->render();
            $links_html .= Div::tag()
                ->class($cssDivClass)
                ->content($columnHtml)
                ->encode(false)
                ->render();
        }
        
        return $links_html;
    }
    
    /**
     * Factory method per creare un builder con stile standard
     */
    public static function standard(array $options = []): self
    {
        return new self('standard', $options);
    }
    
    /**
     * Factory method per creare un builder con stile carousel
     */
    public static function carousel(array $options = []): self
    {
        return new self('carousel', $options);
    }
    
    /**
     * Factory method per creare un builder con stile semplice
     */
    public static function simple(array $options = []): self
    {
        return new self('simple', $options);
    }
}
