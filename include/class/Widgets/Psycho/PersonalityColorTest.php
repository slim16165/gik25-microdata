<?php
namespace gik25microdata\Widgets\Psycho;

if (!defined('ABSPATH')) {
    exit;
}

use gik25microdata\Widgets\AdvancedWidgetsBase;

/**
 * Personality Color Test
 * Test personalità basato su colori per psicocultura.it
 */
class PersonalityColorTest extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'personality-color-test';
    
    protected static array $js_dependencies = ['gsap'];
    
    public static function init(): void
    {
        add_shortcode('personality_test', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        static::enqueue_external_libs();
        static::enqueue_assets(
            'psycho/personality-color-test.js',
            'psycho/personality-color-test.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="personality-color-test">
            <div class="test-progress">
                <div class="progress-bar" id="progress-bar"></div>
                <span class="progress-text" id="progress-text">Domanda 1 di 10</span>
            </div>
            <div class="test-question" id="test-question">
                <h3 id="question-text">Caricamento...</h3>
            </div>
            <div class="test-options" id="test-options"></div>
            <div class="test-result" id="test-result" style="display: none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

