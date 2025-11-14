<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pantone Hub Dinamico
 * Hub Pantone con query dinamica e visualizzazione avanzata
 */
class PantoneHubDynamic extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'pantone-hub-dynamic';
    
    protected static array $js_dependencies = ['gsap', 'd3'];
    
    protected static function needs_d3(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('pantone_hub', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'year' => '',
            'limit' => '20',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'pantone-hub-dynamic.js',
            'pantone-hub-dynamic.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="pantone-hub" data-year="<?php echo esc_attr($atts['year']); ?>"
             data-limit="<?php echo esc_attr($atts['limit']); ?>">
            <div class="pantone-timeline" id="pantone-timeline-<?php echo uniqid(); ?>"></div>
            <div class="pantone-colors" id="pantone-colors"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

