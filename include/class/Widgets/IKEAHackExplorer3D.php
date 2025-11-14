<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * IKEA Hack Explorer 3D
 */
class IKEAHackExplorer3D extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'ikea-hack-explorer-3d';
    
    protected static array $js_dependencies = ['three', 'gsap'];
    
    protected static function needs_threejs(): bool { return true; }
    protected static function needs_hammerjs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('ikea_hack_explorer', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'line' => '',
            'limit' => '12',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'ikea-hack-explorer-3d.js',
            'ikea-hack-explorer-3d.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="ikea-hack-explorer" data-line="<?php echo esc_attr($atts['line']); ?>"
             data-limit="<?php echo esc_attr($atts['limit']); ?>">
            <div class="explorer-container" id="explorer-container-<?php echo uniqid(); ?>"></div>
            <div class="explorer-filters">
                <button class="filter-btn" data-line="billy">BILLY</button>
                <button class="filter-btn" data-line="kallax">KALLAX</button>
                <button class="filter-btn" data-line="besta">BESTA</button>
                <button class="filter-btn" data-line="pax">PAX</button>
                <button class="filter-btn" data-line="metod">METOD</button>
                <button class="filter-btn" data-line="enhet">ENHET</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

