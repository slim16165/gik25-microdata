<?php
namespace gik25microdata\SiteSpecific;

use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\ListOfPosts\ListOfPostsHelper;
use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Ul;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Classe base per gestire logica comune site-specific
 * 
 * Fornisce metodi helper per:
 * - Creazione shortcode
 * - Gestione liste di link
 * - Rendering template comuni
 * 
 * @package gik25microdata\SiteSpecific
 */
abstract class SiteSpecificHandler
{
    /**
     * Nome del sito (usato per identificare configurazioni)
     */
    abstract protected static function getSiteName(): string;
    
    /**
     * Registra gli shortcode specifici del sito
     * Da implementare nelle classi figlie
     */
    abstract public static function registerShortcodes(): void;
    
    /**
     * Crea una lista di link con thumbnail
     * 
     * @param array $links Array di link con struttura: ['url' => '', 'title' => '', 'comment' => '']
     * @param array $options Opzioni: columns, list_class, title, remove_if_self
     * @return string HTML della lista
     */
    protected static function createThumbnailList(array $links, array $options = []): string
    {
        $columns = $options['columns'] ?? 1;
        $listClass = $options['list_class'] ?? 'thumbnail-list';
        $title = $options['title'] ?? '';
        $removeIfSelf = $options['remove_if_self'] ?? true;
        $withImage = $options['with_image'] ?? true;
        $linkSelf = $options['link_self'] ?? false;
        
        $html = '';
        
        if (!empty($title)) {
            $html .= Html::h3($title);
        }
        
        $html .= Html::div()->class($listClass)->open();
        
        if ($columns > 1) {
            // Usa ListOfPostsHelper per colonne multiple
            $helper = new ListOfPostsHelper($removeIfSelf, $withImage, $linkSelf, $columns);
            $collection = new Collection();
            
            foreach ($links as $link) {
                $url = $link['url'] ?? $link['target_url'] ?? '';
                $title = $link['title'] ?? $link['nome'] ?? '';
                $comment = $link['comment'] ?? $link['commento'] ?? '';
                
                if (!empty($url) && !empty($title)) {
                    $collection->add(new LinkBase($title, $url, $comment));
                }
            }
            
            $html .= $helper->GetLinksWithImagesMulticolumn($collection);
        } else {
            // Lista singola colonna
            $html .= Html::ul()->class($listClass)->open();
            
            foreach ($links as $link) {
                $url = $link['url'] ?? $link['target_url'] ?? '';
                $title = $link['title'] ?? $link['nome'] ?? '';
                $comment = $link['comment'] ?? $link['commento'] ?? '';
                
                if (!empty($url) && !empty($title)) {
                    $html .= LinkBuilder::createThumbnailLink($url, $title, $comment, $removeIfSelf);
                }
            }
            
            $html .= Ul::tag()->close();
        }
        
        $html .= Html::div()->close();
        
        return $html;
    }
    
    /**
     * Crea una lista di link per carousel
     * 
     * @param array $links Array di link
     * @param array $options Opzioni: title, css_inline
     * @return string HTML del carousel
     */
    protected static function createCarouselList(array $links, array $options = []): string
    {
        $title = $options['title'] ?? '';
        $cssInline = $options['css_inline'] ?? true;
        
        $html = '';
        
        // Aggiungi CSS se richiesto
        if ($cssInline) {
            $css = \gik25microdata\Widgets\ColorWidget::get_carousel_css();
            $html .= "<style>$css</style>";
        }
        
        $html .= "<div class='contain'>";
        
        if (!empty($title)) {
            $html .= Html::h3($title);
        }
        
        $html .= "<div class='row'><div class='row__inner'>";
        
        foreach ($links as $link) {
            $url = $link['url'] ?? $link['target_url'] ?? '';
            $title = $link['title'] ?? $link['nome'] ?? '';
            
            if (!empty($url) && !empty($title)) {
                $html .= LinkBuilder::createCarouselLink($url, $title);
            }
        }
        
        $html .= "</div></div></div>";
        
        return $html;
    }
    
    /**
     * Crea una lista semplice (senza immagini)
     * 
     * @param array $links Array di link
     * @param array $options Opzioni: list_class, title, wrapper_tag
     * @return string HTML della lista
     */
    protected static function createSimpleList(array $links, array $options = []): string
    {
        $listClass = $options['list_class'] ?? 'nicelist';
        $title = $options['title'] ?? '';
        $wrapperTag = $options['wrapper_tag'] ?? 'ul';
        
        $html = '';
        
        if (!empty($title)) {
            $html .= Html::h3($title);
        }
        
        $html .= Html::tag($wrapperTag)->class($listClass)->open();
        
        foreach ($links as $link) {
            $url = $link['url'] ?? $link['target_url'] ?? '';
            $title = $link['title'] ?? $link['nome'] ?? '';
            $comment = $link['comment'] ?? $link['commento'] ?? '';
            
            if (!empty($url) && !empty($title)) {
                $html .= LinkBuilder::createLink($url, $title, $comment, [
                    'render_type' => LinkBuilder::RENDER_STANDARD,
                ]);
            }
        }
        
        $html .= Html::tag($wrapperTag)->close();
        
        return $html;
    }
    
    /**
     * Helper per creare sezioni con titolo e lista
     * 
     * @param string $sectionTitle Titolo della sezione
     * @param array $links Lista di link
     * @param array $options Opzioni per la lista
     * @return string HTML della sezione
     */
    protected static function createSection(string $sectionTitle, array $links, array $options = []): string
    {
        $html = Html::h4($sectionTitle);
        $html .= self::createThumbnailList($links, $options);
        return $html;
    }
    
    /**
     * Helper per registrare uno shortcode con namespace
     * 
     * @param string $shortcode Nome dello shortcode
     * @param callable $callback Callback per gestire lo shortcode
     */
    protected static function registerShortcode(string $shortcode, callable $callback): void
    {
        add_shortcode($shortcode, $callback);
    }
}
