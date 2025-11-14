<?php
namespace gik25microdata\Widgets\Advanced;

if (!defined('ABSPATH')) {
    exit;
}

use gik25microdata\Widgets\AdvancedWidgetsBase;

class ColorEmotionMapper extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'color-emotion-mapper';
    protected static array $js_dependencies = ['d3'];
    protected static function needs_d3(): bool { return true; }
    
    public static function init(): void {
        add_shortcode('emotion_mapper', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string {
        static::enqueue_external_libs();
        static::enqueue_assets('advanced/color-emotion-mapper.js', 'advanced/color-emotion-mapper.css', '1.0.0');
        
        ob_start();
        ?>
        <div class="color-emotion-mapper">
            <div class="mapper-canvas" id="mapper-canvas"></div>
            <div class="emotion-legend" id="emotion-legend"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

