<?php
namespace gik25microdata\Widgets\Psycho;

if (!defined('ABSPATH')) {
    exit;
}

use gik25microdata\Widgets\AdvancedWidgetsBase;

class MeditationTimerColors extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'meditation-timer-colors';
    protected static array $js_dependencies = ['gsap'];
    
    public static function init(): void {
        add_shortcode('meditation_timer', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string {
        static::enqueue_external_libs();
        static::enqueue_assets('psycho/meditation-timer-colors.js', 'psycho/meditation-timer-colors.css', '1.0.0');
        
        ob_start();
        ?>
        <div class="meditation-timer-colors">
            <div class="timer-display" id="timer-display">05:00</div>
            <div class="timer-controls">
                <button class="timer-btn" data-time="5">5 min</button>
                <button class="timer-btn" data-time="10">10 min</button>
                <button class="timer-btn" data-time="15">15 min</button>
                <button class="timer-btn" data-time="20">20 min</button>
            </div>
            <div class="timer-actions">
                <button class="action-btn" id="start-btn">▶️ Inizia</button>
                <button class="action-btn" id="pause-btn" style="display:none">⏸️ Pausa</button>
                <button class="action-btn" id="reset-btn">🔄 Reset</button>
            </div>
            <canvas class="timer-visualization" id="timer-viz"></canvas>
        </div>
        <?php
        return ob_get_clean();
    }
}

