<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Color Explosion Effect
 * Effetto esplosione colori avanzato con particelle
 */
class ColorExplosionEffect extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'color-explosion-effect';
    
    protected static array $js_dependencies = ['gsap'];
    
    public static function init(): void
    {
        add_shortcode('color_explosion', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'color' => '#FF0000',
            'particles' => '500',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'color-explosion-effect.js',
            'color-explosion-effect.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="color-explosion" data-color="<?php echo esc_attr($atts['color']); ?>"
             data-particles="<?php echo esc_attr($atts['particles']); ?>">
            <canvas class="explosion-canvas" id="explosion-canvas-<?php echo uniqid(); ?>"></canvas>
            <button class="explosion-trigger">💥 Esplodi Colore</button>
        </div>
        <?php
        return ob_get_clean();
    }
}

