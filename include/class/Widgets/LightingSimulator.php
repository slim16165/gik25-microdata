<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lighting Simulator Real-Time
 */
class LightingSimulator extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'lighting-simulator';
    
    protected static array $js_dependencies = ['three'];
    
    protected static function needs_threejs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('lighting_simulator', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'room' => 'soggiorno',
            'time' => 'day',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'lighting-simulator.js',
            'lighting-simulator.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="lighting-simulator" data-room="<?php echo esc_attr($atts['room']); ?>"
             data-time="<?php echo esc_attr($atts['time']); ?>">
            <div class="lighting-container" id="lighting-container-<?php echo uniqid(); ?>"></div>
            <div class="lighting-controls">
                <input type="range" class="time-slider" min="0" max="24" value="12" id="time-slider">
                <label for="time-slider">Ora del giorno: <span id="time-display">12:00</span></label>
                <button class="lighting-btn" data-action="add-light">💡 Aggiungi Luce</button>
                <button class="lighting-btn" data-action="change-color">🎨 Cambia Colore</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

