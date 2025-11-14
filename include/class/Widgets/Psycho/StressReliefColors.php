<?php
namespace gik25microdata\Widgets\Psycho;

if (!defined('ABSPATH')) {
    exit;
}

use gik25microdata\Widgets\AdvancedWidgetsBase;

/**
 * Stress Relief Colors
 * Widget per ridurre stress con colori per psicocultura.it
 */
class StressReliefColors extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'stress-relief-colors';
    
    protected static array $js_dependencies = ['gsap'];
    
    public static function init(): void
    {
        add_shortcode('stress_relief', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        static::enqueue_external_libs();
        static::enqueue_assets(
            'psycho/stress-relief-colors.js',
            'psycho/stress-relief-colors.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="stress-relief-colors">
            <div class="stress-level-selector">
                <h3>Quanto ti senti stressato?</h3>
                <div class="stress-levels">
                    <button class="stress-btn" data-level="low">Basso</button>
                    <button class="stress-btn" data-level="medium">Medio</button>
                    <button class="stress-btn" data-level="high">Alto</button>
                    <button class="stress-btn" data-level="very-high">Molto Alto</button>
                </div>
            </div>
            <div class="relief-visualization" id="relief-viz"></div>
            <div class="relief-tips" id="relief-tips"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

