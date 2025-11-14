<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product Comparison Cinematic
 */
class ProductComparisonCinematic extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'product-comparison-cinematic';
    
    protected static array $js_dependencies = ['gsap'];
    
    public static function init(): void
    {
        add_shortcode('product_comparison', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'products' => '',
            'animation' => 'cinematic',
        ], $atts);
        
        static::enqueue_external_libs();
        static::enqueue_assets(
            'product-comparison-cinematic.js',
            'product-comparison-cinematic.css',
            '1.0.0'
        );
        
        ob_start();
        ?>
        <div class="product-comparison" data-animation="<?php echo esc_attr($atts['animation']); ?>">
            <div class="comparison-container" id="comparison-container-<?php echo uniqid(); ?>">
                <!-- Products will be loaded dynamically -->
            </div>
            <div class="comparison-controls">
                <button class="comparison-btn prev">←</button>
                <button class="comparison-btn next">→</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

