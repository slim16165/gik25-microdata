<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Color Picker 3D Interattivo
 */
class ColorPicker3D extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'color-picker-3d';
    
    protected static array $js_dependencies = ['three', 'gsap'];
    
    protected static function needs_threejs(): bool { return true; }
    protected static function needs_hammerjs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('color_picker_3d', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'audio' => 'true',
            'particles' => 'true',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'color-picker-3d.js',
            'color-picker-3d.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="color-picker-3d" data-audio="<?php echo esc_attr($atts['audio']); ?>"
             data-particles="<?php echo esc_attr($atts['particles']); ?>">
            <div class="picker-container" id="picker-container-<?php echo uniqid(); ?>"></div>
            <div class="picker-info">
                <div class="selected-color" id="selected-color">
                    <span class="color-hex">#FFFFFF</span>
                    <span class="color-rgb">RGB(255, 255, 255)</span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

