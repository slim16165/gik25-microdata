<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Color Harmony Visualizer Widget
 * 
 * Visualizzatore interattivo di armonie colori con:
 * - Grafici interattivi D3.js
 * - Animazioni fluide GSAP
 * - Audio reattivo Web Audio
 * - Effetti particellari Canvas
 */
class ColorHarmonyVisualizer extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'color-harmony-visualizer';
    
    protected static array $js_dependencies = ['gsap', 'd3'];
    
    protected static array $css_dependencies = [];
    
    protected static function needs_d3(): bool
    {
        return true;
    }
    
    public static function init(): void
    {
        add_shortcode('color_harmony', [self::class, 'render']);
        add_shortcode('harmony_visualizer', [self::class, 'render']); // Alias
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'width' => '100%',
            'height' => '600',
            'audio' => 'true',
            'particles' => '100',
            'harmony' => 'complementary',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'color-harmony-visualizer.js',
            'color-harmony-visualizer.css',
            '1.0.0'
        );
        
        $options = [
            'width' => $atts['width'],
            'height' => $atts['height'],
            'audio' => $atts['audio'] === 'true',
            'particles' => (int) $atts['particles'],
            'harmony' => $atts['harmony'],
            'reduced-motion' => static::prefers_reduced_motion(),
        ];
        
        ob_start();
        ?>
        <div class="color-harmony-visualizer" <?php echo static::data_attributes($options); ?>>
            <div class="harmony-container">
                <canvas class="harmony-canvas" id="harmony-canvas-<?php echo uniqid(); ?>"></canvas>
                <div class="harmony-overlay">
                    <div class="harmony-info">
                        <h3 class="harmony-title">Color Harmony Visualizer</h3>
                        <p class="harmony-subtitle">Esplora armonie colori interattive</p>
                    </div>
                </div>
            </div>
            <div class="harmony-controls">
                <div class="harmony-type-selector">
                    <button class="harmony-btn" data-type="complementary" aria-label="Armonia complementare">
                        <span class="harmony-icon">⚡</span>
                        <span class="harmony-label">Complementari</span>
                    </button>
                    <button class="harmony-btn" data-type="analogous" aria-label="Armonia analoga">
                        <span class="harmony-icon">🌈</span>
                        <span class="harmony-label">Analoghi</span>
                    </button>
                    <button class="harmony-btn" data-type="triadic" aria-label="Armonia triadica">
                        <span class="harmony-icon">🔺</span>
                        <span class="harmony-label">Triadi</span>
                    </button>
                    <button class="harmony-btn" data-type="split-complementary" aria-label="Armonia split-complementare">
                        <span class="harmony-icon">✨</span>
                        <span class="harmony-label">Split-Complementary</span>
                    </button>
                    <button class="harmony-btn" data-type="tetradic" aria-label="Armonia tetradica">
                        <span class="harmony-icon">🔷</span>
                        <span class="harmony-label">Tetradic</span>
                    </button>
                    <button class="harmony-btn" data-type="monochromatic" aria-label="Armonia monocromatica">
                        <span class="harmony-icon">🎨</span>
                        <span class="harmony-label">Monocromatico</span>
                    </button>
                </div>
                <div class="harmony-actions">
                    <button class="harmony-action-btn" id="random-harmony" aria-label="Genera armonia casuale">
                        <span>🎲</span> Random
                    </button>
                    <button class="harmony-action-btn" id="export-palette" aria-label="Esporta palette">
                        <span>💾</span> Esporta
                    </button>
                    <button class="harmony-action-btn" id="save-palette" aria-label="Salva palette">
                        <span>⭐</span> Salva
                    </button>
                </div>
            </div>
            <div class="harmony-palette-display" id="palette-display"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

