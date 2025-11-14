<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Color Room Recommender Widget
 * Raccomanda combinazioni colore-stanza con visualizzazione avanzata
 */
class ColorRoomRecommender extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'color-room-recommender';
    
    protected static array $js_dependencies = ['gsap', 'd3'];
    
    protected static function needs_d3(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('color_room_recommender', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'color' => '',
            'room' => '',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'color-room-recommender.js',
            'color-room-recommender.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="color-room-recommender" data-color="<?php echo esc_attr($atts['color']); ?>"
             data-room="<?php echo esc_attr($atts['room']); ?>">
            <div class="recommender-visualization" id="recommender-viz-<?php echo uniqid(); ?>"></div>
            <div class="recommender-suggestions" id="recommender-suggestions"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

