<?php
namespace gik25microdata\Widgets\Psycho;

if (!defined('ABSPATH')) {
    exit;
}

use gik25microdata\Widgets\AdvancedWidgetsBase;

/**
 * Mood Color Tracker
 * Traccia umore e associa colori per psicocultura.it
 */
class MoodColorTracker extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'mood-color-tracker';
    
    protected static array $js_dependencies = ['gsap', 'd3'];
    
    protected static function needs_d3(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('mood_tracker', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'days' => '30',
            'show-chart' => 'true',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'psycho/mood-color-tracker.js',
            'psycho/mood-color-tracker.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="mood-color-tracker" data-days="<?php echo esc_attr($atts['days']); ?>"
             data-show-chart="<?php echo esc_attr($atts['show-chart']); ?>">
            <div class="mood-selector">
                <h3>Come ti senti oggi?</h3>
                <div class="mood-options">
                    <button class="mood-btn" data-mood="happy" data-color="#FFD700">😊 Felice</button>
                    <button class="mood-btn" data-mood="calm" data-color="#4CAF50">😌 Calmo</button>
                    <button class="mood-btn" data-mood="energetic" data-color="#FF5722">⚡ Energico</button>
                    <button class="mood-btn" data-mood="sad" data-color="#2196F3">😢 Triste</button>
                    <button class="mood-btn" data-mood="anxious" data-color="#9C27B0">😰 Ansioso</button>
                    <button class="mood-btn" data-mood="peaceful" data-color="#00BCD4">🧘 Sereno</button>
                </div>
            </div>
            <div class="mood-chart" id="mood-chart"></div>
            <div class="mood-insights" id="mood-insights"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

