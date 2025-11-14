<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fluid Color Mixer
 */
class FluidColorMixer extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'fluid-color-mixer';
    
    protected static array $js_dependencies = [];
    
    public static function init(): void
    {
        add_shortcode('fluid_color_mixer', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'viscosity' => 'medium',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'fluid-color-mixer.js',
            'fluid-color-mixer.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="fluid-color-mixer" data-viscosity="<?php echo esc_attr($atts['viscosity']); ?>">
            <canvas class="fluid-canvas" id="fluid-canvas-<?php echo uniqid(); ?>"></canvas>
            <div class="fluid-controls">
                <div class="color-pickers">
                    <input type="color" class="color-input" id="color-1" value="#FF0000">
                    <input type="color" class="color-input" id="color-2" value="#0000FF">
                </div>
                <button class="fluid-btn" id="mix-colors">🌊 Mescola</button>
                <button class="fluid-btn" id="reset-fluid">🔄 Reset</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

