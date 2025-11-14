<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Isometric IKEA Configurator
 * Configuratore IKEA avanzato in vista isometrica
 */
class IsometricIKEAConfigurator extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'isometric-ikea-configurator';
    
    protected static array $js_dependencies = ['three', 'gsap', 'matter'];
    
    protected static function needs_threejs(): bool { return true; }
    protected static function needs_matterjs(): bool { return true; }
    protected static function needs_hammerjs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('ikea_configurator', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'line' => 'billy',
            'room' => 'soggiorno',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'isometric-ikea-configurator.js',
            'isometric-ikea-configurator.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="ikea-configurator" data-line="<?php echo esc_attr($atts['line']); ?>"
             data-room="<?php echo esc_attr($atts['room']); ?>">
            <div class="configurator-container" id="configurator-container-<?php echo uniqid(); ?>"></div>
            <div class="configurator-panel">
                <div class="panel-section">
                    <h3>Linea IKEA</h3>
                    <select class="line-selector" id="line-selector">
                        <option value="billy">BILLY</option>
                        <option value="kallax">KALLAX</option>
                        <option value="besta">BESTA</option>
                        <option value="pax">PAX</option>
                        <option value="metod">METOD</option>
                        <option value="enhet">ENHET</option>
                    </select>
                </div>
                <div class="panel-section">
                    <h3>Colori</h3>
                    <div class="color-options" id="color-options"></div>
                </div>
                <div class="panel-section">
                    <h3>Accessori</h3>
                    <div class="accessories-list" id="accessories-list"></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

