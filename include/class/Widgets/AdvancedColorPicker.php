<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Color Picker
 * Color picker avanzato con features multiple
 */
class AdvancedColorPicker extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'advanced-color-picker';
    
    protected static array $js_dependencies = ['gsap'];
    
    public static function init(): void
    {
        add_shortcode('advanced_color_picker', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'mode' => 'hsl',
            'show-palette' => 'true',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'advanced-color-picker.js',
            'advanced-color-picker.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="advanced-color-picker" data-mode="<?php echo esc_attr($atts['mode']); ?>"
             data-show-palette="<?php echo esc_attr($atts['show-palette']); ?>">
            <div class="picker-main">
                <canvas class="picker-canvas" id="picker-canvas-<?php echo uniqid(); ?>"></canvas>
                <div class="picker-controls">
                    <input type="range" class="hue-slider" id="hue-slider" min="0" max="360" value="0">
                    <input type="range" class="saturation-slider" id="saturation-slider" min="0" max="100" value="50">
                    <input type="range" class="lightness-slider" id="lightness-slider" min="0" max="100" value="50">
                </div>
            </div>
            <div class="picker-info">
                <div class="color-preview" id="color-preview"></div>
                <div class="color-values">
                    <div class="value-item">
                        <label>HEX</label>
                        <input type="text" class="hex-input" id="hex-input" readonly>
                    </div>
                    <div class="value-item">
                        <label>RGB</label>
                        <input type="text" class="rgb-input" id="rgb-input" readonly>
                    </div>
                    <div class="value-item">
                        <label>HSL</label>
                        <input type="text" class="hsl-input" id="hsl-input" readonly>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

