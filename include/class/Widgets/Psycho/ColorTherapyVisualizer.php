<?php
namespace gik25microdata\Widgets\Psycho;

if (!defined('ABSPATH')) {
    exit;
}

use gik25microdata\Widgets\AdvancedWidgetsBase;

/**
 * Color Therapy Visualizer
 * Visualizzatore terapia colori per psicocultura.it
 */
class ColorTherapyVisualizer extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'color-therapy-visualizer';
    
    protected static array $js_dependencies = ['gsap', 'three'];
    
    protected static function needs_threejs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('color_therapy', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'mode' => 'breathing',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'psycho/color-therapy-visualizer.js',
            'psycho/color-therapy-visualizer.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="color-therapy-visualizer" data-mode="<?php echo esc_attr($atts['mode']); ?>">
            <div class="therapy-container" id="therapy-container-<?php echo uniqid(); ?>"></div>
            <div class="therapy-controls">
                <button class="therapy-btn" data-mode="breathing">🫁 Respirazione</button>
                <button class="therapy-btn" data-mode="meditation">🧘 Meditazione</button>
                <button class="therapy-btn" data-mode="focus">🎯 Focus</button>
                <button class="therapy-btn" data-mode="relax">😌 Rilassamento</button>
            </div>
            <div class="therapy-info">
                <h3 id="therapy-title">Terapia Colori</h3>
                <p id="therapy-description">Scegli una modalità per iniziare</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

