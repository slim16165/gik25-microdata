<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Palette Generator con Effetti Particellari
 */
class PaletteGeneratorParticles extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'palette-generator-particles';
    
    protected static array $js_dependencies = ['gsap'];
    
    public static function init(): void
    {
        add_shortcode('palette_generator', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'particles' => '200',
            'audio' => 'true',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'palette-generator-particles.js',
            'palette-generator-particles.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="palette-generator" data-particles="<?php echo esc_attr($atts['particles']); ?>" 
             data-audio="<?php echo esc_attr($atts['audio']); ?>">
            <canvas class="palette-canvas" id="palette-canvas-<?php echo uniqid(); ?>"></canvas>
            <div class="palette-controls">
                <button class="palette-btn" id="generate-palette">🎨 Genera Palette</button>
                <button class="palette-btn" id="mix-colors">🌊 Mescola Colori</button>
                <button class="palette-btn" id="export-palette">💾 Esporta</button>
            </div>
            <div class="palette-display" id="palette-colors"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

