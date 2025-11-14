<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Room Simulator Isometrico
 */
class RoomSimulatorIsometric extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'room-simulator-isometric';
    
    protected static array $js_dependencies = ['three', 'gsap', 'matter'];
    
    protected static function needs_threejs(): bool { return true; }
    protected static function needs_matterjs(): bool { return true; }
    protected static function needs_hammerjs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('room_simulator', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'room' => 'cucina',
            'width' => '100%',
            'height' => '600',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'room-simulator-isometric.js',
            'room-simulator-isometric.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="room-simulator" data-room="<?php echo esc_attr($atts['room']); ?>"
             data-width="<?php echo esc_attr($atts['width']); ?>"
             data-height="<?php echo esc_attr($atts['height']); ?>">
            <div class="room-container" id="room-container-<?php echo uniqid(); ?>"></div>
            <div class="room-toolbar">
                <button class="toolbar-btn" data-action="furniture">🪑 Mobili</button>
                <button class="toolbar-btn" data-action="colors">🎨 Colori</button>
                <button class="toolbar-btn" data-action="lighting">💡 Illuminazione</button>
                <button class="toolbar-btn" data-action="reset">🔄 Reset</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

