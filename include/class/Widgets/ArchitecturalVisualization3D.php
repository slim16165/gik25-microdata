<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Architectural Visualization 3D
 */
class ArchitecturalVisualization3D extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'architectural-visualization-3d';
    
    protected static array $js_dependencies = ['three', 'gsap'];
    
    protected static function needs_threejs(): bool { return true; }
    protected static function needs_hammerjs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('architectural_viz', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'architect' => '',
            'flythrough' => 'true',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'architectural-visualization-3d.js',
            'architectural-visualization-3d.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="architectural-viz" data-architect="<?php echo esc_attr($atts['architect']); ?>"
             data-flythrough="<?php echo esc_attr($atts['flythrough']); ?>">
            <div class="viz-container" id="viz-container-<?php echo uniqid(); ?>"></div>
            <div class="viz-controls">
                <button class="viz-btn" data-action="play">▶️ Play</button>
                <button class="viz-btn" data-action="pause">⏸️ Pause</button>
                <button class="viz-btn" data-action="reset">🔄 Reset</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

