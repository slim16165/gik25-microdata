<?php
namespace gik25microdata\Carousel;

use gik25microdata\Database\CarouselTemplates;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Engine per rendering caroselli con template configurabili
 * 
 * Gestisce il rendering di collezioni usando template CSS/DOM/JS configurabili
 * dal database, con supporto per variabili e personalizzazioni.
 */
class CarouselTemplateEngine
{
    /**
     * Renderizza una collezione usando il template associato
     * 
     * @param array $collection Dati collezione (da CarouselCollections)
     * @param array $items Items da renderizzare
     * @param array $options Opzioni aggiuntive (title, css_class, ecc.)
     * @return string HTML renderizzato
     */
    public static function render_collection(array $collection, array $items, array $options = []): string
    {
        // Determina template da usare
        $template = null;
        if (!empty($collection['template_id'])) {
            $template = CarouselTemplates::get_template_by_id((int) $collection['template_id']);
        }
        
        // Se non c'è template_id, usa template di default basato su display_type
        if (!$template) {
            $default_template_key = self::get_default_template_for_display_type($collection['display_type'] ?? 'list');
            $template = CarouselTemplates::get_template_by_key($default_template_key);
        }
        
        // Se ancora non c'è template, usa fallback semplice
        if (!$template) {
            return self::render_fallback($items, $options);
        }
        
        // Parse configurazione template
        $template_config = [];
        if (!empty($collection['template_config'])) {
            $template_config = json_decode($collection['template_config'], true) ?: [];
        }
        
        // Genera CSS
        $css = self::get_css($template, $template_config);
        
        // Genera DOM
        $dom = self::get_dom($template, $items, $template_config);
        
        // Genera JS (se presente)
        $js = self::get_js($template, $template_config);
        
        // Costruisci output finale
        $title = $options['title'] ?? $collection['collection_name'] ?? '';
        $css_class = $options['css_class'] ?? $collection['css_class'] ?? '';
        $template_key = $template['template_key'] ?? '';
        
        $output = '';
        
        // CSS
        if ($css) {
            $output .= "<style>{$css}</style>\n";
        }
        
        // Container
        $container_class = "generic-carousel-container {$template_key}";
        if ($css_class) {
            $container_class .= ' ' . esc_attr($css_class);
        }
        $output .= '<div class="' . esc_attr(trim($container_class)) . '">';
        
        // Titolo
        if ($title) {
            $output .= '<h3>' . esc_html($title) . '</h3>';
        }
        
        // DOM renderizzato
        $output .= $dom;
        
        // JavaScript
        if ($js) {
            $output .= "<script>{$js}</script>\n";
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Genera CSS da template
     */
    private static function get_css(array $template, array $config): string
    {
        $css = $template['css_content'] ?? '';
        
        if (empty($css)) {
            return '';
        }
        
        // Sostituisci variabili CSS se presenti
        if (!empty($template['css_variables'])) {
            $variables = json_decode($template['css_variables'], true) ?: [];
            // Merge con config (config ha priorità)
            $variables = array_merge($variables, $config);
            
            foreach ($variables as $key => $value) {
                $css = str_replace('{{' . $key . '}}', $value, $css);
            }
        }
        
        return $css;
    }

    /**
     * Genera DOM da template
     */
    private static function get_dom(array $template, array $items, array $config): string
    {
        $dom_template = $template['dom_structure'] ?? '';
        
        if (empty($dom_template)) {
            return self::render_fallback($items, []);
        }
        
        // Estrai il template per un singolo item (tra {ITEMS_LOOP_START} e {ITEMS_LOOP_END})
        $item_template = '';
        if (preg_match('/\{ITEMS_LOOP_START\}(.*?)\{ITEMS_LOOP_END\}/s', $dom_template, $matches)) {
            $item_template = $matches[1];
            $wrapper_template = str_replace($matches[0], '{ITEMS_PLACEHOLDER}', $dom_template);
        } else {
            // Se non c'è loop, usa tutto il template come item template
            $item_template = $dom_template;
            $wrapper_template = '{ITEMS_PLACEHOLDER}';
        }
        
        // Renderizza items
        $items_html = '';
        foreach ($items as $item) {
            $item_html = $item_template;
            
            // Sostituisci placeholder
            $item_html = str_replace('{ITEM_URL}', esc_url($item['item_url'] ?? ''), $item_html);
            $item_html = str_replace('{ITEM_TITLE}', esc_html($item['item_title'] ?? ''), $item_html);
            
            // Immagine (recupera da URL post se non presente)
            $image_tag = '';
            $image_url = $item['item_image_url'] ?? null;
            
            // Se non c'è immagine esplicita, prova a recuperarla dal post WordPress
            if (empty($image_url) && !empty($item['item_url'])) {
                $post_id = url_to_postid($item['item_url']);
                if ($post_id > 0) {
                    $image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                }
            }
            
            // Se ancora non c'è immagine, usa placeholder
            if (empty($image_url)) {
                $image_url = plugins_url('gik25-microdata/assets/images/placeholder-200x200.png');
            }
            
            // Genera tag immagine
            $image_tag = '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($item['item_title'] ?? '') . '" loading="lazy" class="li-img">';
            $item_html = str_replace('{ITEM_IMAGE_TAG}', $image_tag, $item_html);
            
            // Descrizione (se presente)
            if (!empty($item['item_description'])) {
                $item_html = str_replace('{ITEM_DESCRIPTION}', esc_html($item['item_description']), $item_html);
            } else {
                $item_html = str_replace('{ITEM_DESCRIPTION}', '', $item_html);
            }
            
            $items_html .= $item_html;
        }
        
        // Sostituisci placeholder items nel wrapper
        $dom = str_replace('{ITEMS_PLACEHOLDER}', $items_html, $wrapper_template);
        
        return $dom;
    }

    /**
     * Genera JavaScript da template
     */
    private static function get_js(array $template, array $config): string
    {
        $js = $template['js_content'] ?? '';
        
        if (empty($js)) {
            return '';
        }
        
        // Sostituisci variabili JS se presenti
        foreach ($config as $key => $value) {
            $js = str_replace('{{' . $key . '}}', $value, $js);
        }
        
        return $js;
    }

    /**
     * Ottieni template di default per display_type
     */
    private static function get_default_template_for_display_type(string $display_type): string
    {
        $map = [
            'list' => 'thumbnail-list',
            'grid' => 'grid-modern',
            'carousel' => 'thumbnail-list', // Per ora usiamo thumbnail-list anche per carousel (non ColorWidget)
        ];
        
        return $map[$display_type] ?? 'thumbnail-list';
    }

    /**
     * Render fallback semplice se template non disponibile
     */
    private static function render_fallback(array $items, array $options): string
    {
        $output = '<ul class="generic-carousel-fallback">';
        
        foreach ($items as $item) {
            $output .= '<li>';
            $output .= '<a href="' . esc_url($item['item_url'] ?? '#') . '">';
            $output .= esc_html($item['item_title'] ?? '');
            $output .= '</a>';
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        
        return $output;
    }
}

