<?php
namespace gik25microdata\Shortcodes;

use gik25microdata\Database\CarouselCollections;
use gik25microdata\ColorWidget;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode generico per caroselli/liste configurabili via database
 * 
 * Utilizzo:
 * [carousel collection="colori"]
 * [carousel collection="architetti" category="moderni"]
 * [list collection="programmi-3d" display="grid"]
 */
class GenericCarousel extends ShortcodeBase
{
    protected string $shortcode = 'carousel';

    public function __construct()
    {
        parent::__construct();
        
        // Registra anche alias 'list' e 'grid'
        add_shortcode('list', [$this, 'ShortcodeHandler']);
        add_shortcode('grid', [$this, 'ShortcodeHandler']);
    }

    public function ShortcodeHandler($atts = [], $content = null): string
    {
        $atts = shortcode_atts([
            'collection' => '',
            'category' => null,
            'display' => 'carousel', // carousel, list, grid
            'limit' => 0, // 0 = tutti
            'title' => '',
            'css_class' => '',
        ], $atts);

        if (empty($atts['collection'])) {
            return '<!-- Errore: attributo "collection" mancante -->';
        }

        // Se display è specificato, sovrascrive il tipo dalla collezione
        if (!empty($atts['display'])) {
            $display_type = $atts['display'];
        } else {
            // Ottieni tipo dalla collezione
            $collection = CarouselCollections::get_collection_by_key($atts['collection']);
            if (!$collection) {
                return '<!-- Errore: collezione non trovata: ' . esc_html($atts['collection']) . ' -->';
            }
            $display_type = $collection['display_type'] ?? 'carousel';
        }

        // Ottieni items
        $collection = CarouselCollections::get_collection_by_key($atts['collection']);
        if (!$collection) {
            return '<!-- Errore: collezione non trovata: ' . esc_html($atts['collection']) . ' -->';
        }

        $items = CarouselCollections::get_collection_items(
            (int) $collection['id'],
            $atts['category']
        );

        if (empty($items)) {
            return '<!-- Nessun item trovato per collezione: ' . esc_html($atts['collection']) . ' -->';
        }

        // Limita items se specificato
        if ($atts['limit'] > 0) {
            $items = array_slice($items, 0, (int) $atts['limit']);
        }

        // Raggruppa per categoria se necessario
        $grouped_items = [];
        if (!empty($atts['category'])) {
            // Mostra solo la categoria specificata
            $grouped_items[$atts['category']] = $items;
        } else {
            // Raggruppa per categoria
            foreach ($items as $item) {
                $cat = $item['category'] ?? 'default';
                if (!isset($grouped_items[$cat])) {
                    $grouped_items[$cat] = [];
                }
                $grouped_items[$cat][] = $item;
            }
        }

        // Carica CSS se necessario
        $css = '';
        if ($display_type === 'carousel') {
            $css = ColorWidget::get_carousel_css();
        }

        // Genera HTML in base al tipo di display
        $output = '';
        if ($css) {
            $output .= "<style>{$css}</style>";
        }

        $title = !empty($atts['title']) 
            ? $atts['title'] 
            : ($collection['collection_name'] ?? ucfirst($atts['collection']));

        $output .= '<div class="generic-carousel-container' . ($atts['css_class'] ? ' ' . esc_attr($atts['css_class']) : '') . '">';
        
        if ($title) {
            $output .= '<h3>' . esc_html($title) . '</h3>';
        }

        // Rendering in base al tipo
        switch ($display_type) {
            case 'list':
                $output .= $this->render_list($grouped_items, $collection);
                break;
            case 'grid':
                $output .= $this->render_grid($grouped_items, $collection);
                break;
            case 'carousel':
            default:
                $output .= $this->render_carousel($grouped_items, $collection);
                break;
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render carosello
     */
    private function render_carousel(array $grouped_items, array $collection): string
    {
        $output = '';
        
        foreach ($grouped_items as $category => $items) {
            if (count($grouped_items) > 1) {
                $output .= '<p>' . esc_html($category ?: 'Altri') . '</p>';
            }
            
            $output .= '<div class="row"><div class="row__inner">';
            
            foreach ($items as $item) {
                $output .= ColorWidget::GetLinkWithImageCarousel(
                    $item['item_url'],
                    $item['item_title']
                );
            }
            
            $output .= '</div></div>';
        }
        
        return $output;
    }

    /**
     * Render lista
     */
    private function render_list(array $grouped_items, array $collection): string
    {
        $output = '<ul class="generic-carousel-list">';
        
        foreach ($grouped_items as $category => $items) {
            if (count($grouped_items) > 1) {
                $output .= '<li class="category-title"><strong>' . esc_html($category ?: 'Altri') . '</strong></li>';
            }
            
            foreach ($items as $item) {
                $output .= '<li><a href="' . esc_url($item['item_url']) . '">' . esc_html($item['item_title']) . '</a>';
                if (!empty($item['item_description'])) {
                    $output .= ' - <span class="description">' . esc_html($item['item_description']) . '</span>';
                }
                $output .= '</li>';
            }
        }
        
        $output .= '</ul>';
        return $output;
    }

    /**
     * Render griglia
     */
    private function render_grid(array $grouped_items, array $collection): string
    {
        $output = '<div class="generic-carousel-grid">';
        
        foreach ($grouped_items as $category => $items) {
            if (count($grouped_items) > 1) {
                $output .= '<h4>' . esc_html($category ?: 'Altri') . '</h4>';
            }
            
            $output .= '<div class="grid-items">';
            
            foreach ($items as $item) {
                $output .= '<div class="grid-item">';
                $output .= '<a href="' . esc_url($item['item_url']) . '">';
                
                if (!empty($item['item_image_url'])) {
                    $output .= '<img src="' . esc_url($item['item_image_url']) . '" alt="' . esc_attr($item['item_title']) . '" />';
                }
                
                $output .= '<span class="item-title">' . esc_html($item['item_title']) . '</span>';
                $output .= '</a>';
                $output .= '</div>';
            }
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        return $output;
    }

    // Metodi richiesti da ShortcodeBase
    public function styles(): void
    {
        // CSS caricato dinamicamente nel render se necessario
    }

    public function admin_scripts(): void
    {
        // Nessun script admin necessario
    }

    public function register_plugin($plugin_array)
    {
        return $plugin_array;
    }

    public function register_button($buttons)
    {
        return $buttons;
    }
}

// Auto-instantiate
new GenericCarousel();

