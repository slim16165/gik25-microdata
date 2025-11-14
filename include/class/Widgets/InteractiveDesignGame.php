<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interactive Design Game
 */
class InteractiveDesignGame extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'interactive-design-game';
    
    protected static array $js_dependencies = ['three', 'gsap', 'matter'];
    
    protected static function needs_threejs(): bool { return true; }
    protected static function needs_matterjs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('design_game', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'difficulty' => 'medium',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'interactive-design-game.js',
            'interactive-design-game.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="design-game" data-difficulty="<?php echo esc_attr($atts['difficulty']); ?>">
            <div class="game-container" id="game-container-<?php echo uniqid(); ?>"></div>
            <div class="game-ui">
                <div class="game-score">
                    <span>Punteggio: <span id="score">0</span></span>
                    <span>Livello: <span id="level">1</span></span>
                </div>
                <div class="game-controls">
                    <button class="game-btn" id="start-game">▶️ Inizia</button>
                    <button class="game-btn" id="pause-game">⏸️ Pausa</button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

