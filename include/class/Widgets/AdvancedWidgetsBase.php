<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Base class per tutti i widget avanzati
 * 
 * Fornisce funzionalità comuni:
 * - Enqueue scripts/styles
 * - Lazy loading
 * - Performance optimization
 * - Accessibility support
 */
abstract class AdvancedWidgetsBase
{
    /**
     * @var string Nome del widget (slug)
     */
    protected static string $widget_name;
    
    /**
     * @var array<string> Dipendenze JavaScript esterne
     */
    protected static array $js_dependencies = [];
    
    /**
     * @var array<string> Dipendenze CSS esterne
     */
    protected static array $css_dependencies = [];
    
    /**
     * Inizializza il widget
     */
    abstract public static function init(): void;
    
    /**
     * Renderizza il widget
     * 
     * @param array $atts Attributi shortcode
     * @return string HTML del widget
     */
    abstract public static function render(array $atts = []): string;
    
    /**
     * Enqueue scripts e styles per il widget
     * 
     * @param string $js_file File JavaScript (relativo a assets/js/)
     * @param string $css_file File CSS (relativo a assets/css/)
     * @param string $version Versione per cache busting
     */
    protected static function enqueue_assets(
        string $js_file,
        string $css_file,
        string $version = '1.0.0'
    ): void {
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_url = plugins_url('', $plugin_dir . '../..');
        
        // Enqueue CSS
        if ($css_file) {
            wp_enqueue_style(
                static::$widget_name . '-style',
                $plugin_url . '/assets/css/' . $css_file,
                static::$css_dependencies,
                $version
            );
        }
        
        // Enqueue JS
        if ($js_file) {
            wp_enqueue_script(
                static::$widget_name . '-script',
                $plugin_url . '/assets/js/' . $js_file,
                array_merge(static::$js_dependencies, ['jquery']),
                $version,
                true
            );
        }
    }
    
    /**
     * Enqueue librerie esterne comuni
     */
    protected static function enqueue_external_libs(): void
    {
        // GSAP (animazioni avanzate)
        if (!wp_script_is('gsap', 'enqueued')) {
            wp_enqueue_script(
                'gsap',
                'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
                [],
                '3.12.5',
                true
            );
        }
        
        // Three.js (3D rendering)
        if (static::needs_threejs()) {
            if (!wp_script_is('three', 'enqueued')) {
                wp_enqueue_script(
                    'three',
                    'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js',
                    [],
                    '0.160.0',
                    true
                );
            }
        }
        
        // D3.js (grafici)
        if (static::needs_d3()) {
            if (!wp_script_is('d3', 'enqueued')) {
                wp_enqueue_script(
                    'd3',
                    'https://d3js.org/d3.v7.min.js',
                    [],
                    '7.9.0',
                    true
                );
            }
        }
        
        // Matter.js (fisica 2D)
        if (static::needs_matterjs()) {
            if (!wp_script_is('matter', 'enqueued')) {
                wp_enqueue_script(
                    'matter',
                    'https://cdn.jsdelivr.net/npm/matter-js@0.19.0/build/matter.min.js',
                    [],
                    '0.19.0',
                    true
                );
            }
        }
        
        // Hammer.js (gesture recognition)
        if (static::needs_hammerjs()) {
            if (!wp_script_is('hammer', 'enqueued')) {
                wp_enqueue_script(
                    'hammer',
                    'https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js',
                    [],
                    '2.0.8',
                    true
                );
            }
        }
    }
    
    /**
     * Verifica se il widget necessita Three.js
     */
    protected static function needs_threejs(): bool
    {
        return false;
    }
    
    /**
     * Verifica se il widget necessita D3.js
     */
    protected static function needs_d3(): bool
    {
        return false;
    }
    
    /**
     * Verifica se il widget necessita Matter.js
     */
    protected static function needs_matterjs(): bool
    {
        return false;
    }
    
    /**
     * Verifica se il widget necessita Hammer.js
     */
    protected static function needs_hammerjs(): bool
    {
        return false;
    }
    
    /**
     * Genera attributi data per inizializzazione JavaScript
     * 
     * @param array $options Opzioni widget
     * @return string Attributi HTML
     */
    protected static function data_attributes(array $options): string
    {
        $attrs = [];
        foreach ($options as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $value = json_encode($value);
            }
            $attrs[] = sprintf('data-%s="%s"', esc_attr($key), esc_attr($value));
        }
        return implode(' ', $attrs);
    }
    
    /**
     * Verifica se l'utente ha ridotto le animazioni
     */
    protected static function prefers_reduced_motion(): bool
    {
        if (!isset($_SERVER['HTTP_ACCEPT'])) {
            return false;
        }
        return strpos($_SERVER['HTTP_ACCEPT'], 'prefers-reduced-motion') !== false;
    }
}

