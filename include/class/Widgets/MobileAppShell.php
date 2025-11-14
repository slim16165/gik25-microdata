<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mobile App Shell
 * 
 * App JS avanzatissima tipo mobile che unisce tutti i widget
 * PWA-ready con service worker, offline support, e UI mobile-first
 */
class MobileAppShell extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'mobile-app-shell';
    
    protected static array $js_dependencies = ['gsap'];
    
    public static function init(): void
    {
        add_shortcode('mobile_app', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_app_assets']);
    }
    
    public static function enqueue_app_assets(): void
    {
        static::enqueue_external_libs();
        static::enqueue_assets(
            'mobile-app-shell.js',
            'mobile-app-shell.css',
            '1.0.0'
        );
    }
    
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'theme' => 'dark',
            'mode' => 'full',
        ], $atts);
        
        ob_start();
        ?>
        <div class="mobile-app-shell" data-theme="<?php echo esc_attr($atts['theme']); ?>"
             data-mode="<?php echo esc_attr($atts['mode']); ?>">
            <div class="app-header">
                <button class="app-menu-btn" id="app-menu-btn" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
                <h1 class="app-title">TotalDesign App</h1>
                <button class="app-search-btn" id="app-search-btn" aria-label="Cerca">
                    🔍
                </button>
            </div>
            <nav class="app-nav" id="app-nav">
                <div class="nav-items">
                    <a href="#home" class="nav-item active" data-page="home">
                        <span class="nav-icon">🏠</span>
                        <span class="nav-label">Home</span>
                    </a>
                    <a href="#colors" class="nav-item" data-page="colors">
                        <span class="nav-icon">🎨</span>
                        <span class="nav-label">Colori</span>
                    </a>
                    <a href="#ikea" class="nav-item" data-page="ikea">
                        <span class="nav-icon">🏪</span>
                        <span class="nav-label">IKEA</span>
                    </a>
                    <a href="#rooms" class="nav-item" data-page="rooms">
                        <span class="nav-icon">🏠</span>
                        <span class="nav-label">Stanze</span>
                    </a>
                    <a href="#psycho" class="nav-item" data-page="psycho">
                        <span class="nav-icon">🧠</span>
                        <span class="nav-label">Psico</span>
                    </a>
                </div>
            </nav>
            <main class="app-content" id="app-content">
                <div class="app-page active" id="page-home">
                    <div class="page-header">
                        <h2>Benvenuto</h2>
                        <p>Esplora i nostri widget avanzati</p>
                    </div>
                    <div class="widget-grid" id="widget-grid-home"></div>
                </div>
                <div class="app-page" id="page-colors">
                    <div class="widget-container" data-widget="color-harmony"></div>
                    <div class="widget-container" data-widget="palette-generator"></div>
                    <div class="widget-container" data-widget="color-picker-3d"></div>
                </div>
                <div class="app-page" id="page-ikea">
                    <div class="widget-container" data-widget="ikea-explorer"></div>
                    <div class="widget-container" data-widget="ikea-configurator"></div>
                </div>
                <div class="app-page" id="page-rooms">
                    <div class="widget-container" data-widget="room-simulator"></div>
                    <div class="widget-container" data-widget="lighting-simulator"></div>
                </div>
                <div class="app-page" id="page-psycho">
                    <div class="widget-container" data-widget="psycho-mood-tracker"></div>
                    <div class="widget-container" data-widget="psycho-color-therapy"></div>
                </div>
            </main>
            <div class="app-overlay" id="app-overlay"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

