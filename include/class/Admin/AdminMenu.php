<?php
namespace gik25microdata\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gestione menu admin principale del plugin
 * 
 * Crea una voce primaria nel menu admin con sottovoci:
 * - Dashboard (home)
 * - Settings
 * - Health Check
 */
class AdminMenu
{
    public const MENU_SLUG = 'revious-microdata';
    private const MENU_TITLE = 'Revious Microdata';
    private const MENU_ICON = 'dashicons-admin-generic';
    private const CAPABILITY = 'manage_options';

    /**
     * Inizializza il menu admin
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'register_menu'], 9); // Priorità 9 per registrare prima delle sottovoci
    }

    /**
     * Registra il menu principale e le sottovoci
     */
    public static function register_menu(): void
    {
        // Menu principale
        add_menu_page(
            self::MENU_TITLE,
            self::MENU_TITLE,
            self::CAPABILITY,
            self::MENU_SLUG,
            [self::class, 'render_dashboard'],
            self::MENU_ICON,
            30 // Posizione nel menu (dopo "Strumenti")
        );

        // Dashboard (home) - stessa pagina del menu principale (ora con tab: Dashboard/Impostazioni/Strumenti)
        add_submenu_page(
            self::MENU_SLUG,
            'Dashboard',
            'Dashboard',
            self::CAPABILITY,
            self::MENU_SLUG,
            [self::class, 'render_dashboard']
        );

        // Shortcodes unificata (tab: Gestione/Utilizzo)
        add_submenu_page(
            self::MENU_SLUG,
            __('Shortcodes', 'gik25-microdata'),
            __('Shortcodes', 'gik25-microdata'),
            self::CAPABILITY,
            'revious-microdata-shortcodes',
            ['\gik25microdata\Admin\ShortcodesUnifiedPage', 'render_page']
        );

        // Health Check - sposta la pagina health check esistente
        self::register_health_check_submenu();

        // Rimuovi le voci di menu vecchie (se esistono)
        remove_submenu_page('options-general.php', 'revious-microdata-setting-admin');
        remove_submenu_page('tools.php', 'gik25-health-check');
    }


    /**
     * Registra sottovocce Health Check
     */
    private static function register_health_check_submenu(): void
    {
        add_submenu_page(
            self::MENU_SLUG,
            'Health Check',
            'Health Check',
            self::CAPABILITY,
            'gik25-health-check',
            [self::class, 'redirect_to_health_check']
        );
    }

    /**
     * Redirect alla pagina health check
     */
    public static function redirect_to_health_check(): void
    {
        if (class_exists('\gik25microdata\HealthCheck\HealthChecker')) {
            \gik25microdata\HealthCheck\HealthChecker::render_admin_page();
        }
    }

    /**
     * Render dashboard (home page) con tab
     */
    public static function render_dashboard(): void
    {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(self::MENU_TITLE); ?></h1>
            
            <nav class="nav-tab-wrapper" style="margin: 20px 0;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::MENU_SLUG . '&tab=dashboard')); ?>" 
                   class="nav-tab <?php echo $active_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
                    📊 Dashboard
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::MENU_SLUG . '&tab=settings')); ?>" 
                   class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    ⚙️ Impostazioni
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::MENU_SLUG . '&tab=tools')); ?>" 
                   class="nav-tab <?php echo $active_tab === 'tools' ? 'nav-tab-active' : ''; ?>">
                    🔧 Strumenti
                </a>
            </nav>

            <div class="tab-content" style="margin-top: 20px;">
                <?php
                switch ($active_tab) {
                    case 'settings':
                        self::render_settings_tab();
                        break;
                    case 'tools':
                        self::render_tools_tab();
                        break;
                    case 'dashboard':
                    default:
                        self::render_dashboard_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render tab Dashboard
     */
    private static function render_dashboard_tab(): void
    {
        ?>
        <div class="revious-microdata-dashboard" style="max-width: 1200px;">
            <div class="dashboard-widgets" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                
                <!-- Widget: Informazioni Plugin -->
                <div class="postbox" style="padding: 20px;">
                    <h2 style="margin-top: 0;">📦 Informazioni Plugin</h2>
                    <p><strong>Versione:</strong> <?php echo esc_html(self::get_plugin_version()); ?></p>
                    <p><strong>Nome:</strong> Revious Microdata</p>
                    <p><strong>Autore:</strong> Gianluigi Salvi</p>
                </div>

                <!-- Widget: Shortcode Registrati -->
                <div class="postbox" style="padding: 20px;">
                    <h2 style="margin-top: 0;">🎨 Shortcode</h2>
                    <p><strong>Totale registrati:</strong> <?php echo esc_html(self::count_registered_shortcodes()); ?></p>
                    <p><a href="<?php echo esc_url(admin_url('admin.php?page=' . self::MENU_SLUG . '-shortcodes&tab=management')); ?>" class="button">Gestisci Shortcode</a></p>
                </div>

                <!-- Widget: Health Check -->
                <div class="postbox" style="padding: 20px;">
                    <h2 style="margin-top: 0;">🔍 Health Check</h2>
                    <p>Verifica lo stato delle funzionalità del plugin.</p>
                    <p><a href="<?php echo esc_url(admin_url('admin.php?page=gik25-health-check')); ?>" class="button button-primary">Esegui Health Check</a></p>
                </div>

                <!-- Widget: Statistiche -->
                <div class="postbox" style="padding: 20px;">
                    <h2 style="margin-top: 0;">📊 Statistiche</h2>
                    <p><strong>Widget TotalDesign:</strong> 18</p>
                    <p><strong>Shortcode Base:</strong> <?php echo esc_html(self::count_base_shortcodes()); ?></p>
                    <p><strong>MCP REST API:</strong> <?php echo self::is_mcp_api_enabled() ? '✅ Attiva' : '❌ Disattiva'; ?></p>
                    <p class="description" style="font-size: 11px; color: #666; margin-top: 5px;">
                        REST API per server MCP Node.js (locale)
                    </p>
                </div>

                <!-- Widget: Link Utili -->
                <div class="postbox" style="padding: 20px;">
                    <h2 style="margin-top: 0;">🔗 Link Utili</h2>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=' . self::MENU_SLUG . '&tab=settings')); ?>">Impostazioni</a></li>
                        <li><a href="https://github.com/slim16165/gik25-microdata" target="_blank">GitHub Repository</a></li>
                    </ul>
                </div>

            </div>
        </div>

        <style>
            .revious-microdata-dashboard .postbox {
                background: #fff;
                border: 1px solid #c3c4c7;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .revious-microdata-dashboard .postbox h2 {
                color: #1d2327;
                font-size: 14px;
                font-weight: 600;
                margin: 0 0 15px 0;
                padding: 0;
            }
        </style>
        <?php
    }

    /**
     * Render tab Impostazioni
     */
    private static function render_settings_tab(): void
    {
        if (class_exists('\gik25microdata\ReviousMicrodataSettingsPage')) {
            \gik25microdata\ReviousMicrodataSettingsPage::render_page();
        } else {
            echo '<div class="wrap"><h1>Errore</h1><p>Impossibile caricare la pagina settings. Classe non trovata.</p></div>';
        }
    }

    /**
     * Render tab Strumenti
     */
    private static function render_tools_tab(): void
    {
        if (class_exists('\gik25microdata\Admin\ToolsPage')) {
            \gik25microdata\Admin\ToolsPage::renderPage();
        } else {
            echo '<div class="wrap"><h1>Errore</h1><p>Impossibile caricare la pagina strumenti. Classe non trovata.</p></div>';
        }
    }

    /**
     * Ottieni versione plugin
     */
    private static function get_plugin_version(): string
    {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        $plugin_data = get_plugin_data(plugin_dir_path(__DIR__) . '../../revious-microdata.php');
        return $plugin_data['Version'] ?? 'N/A';
    }

    /**
     * Conta shortcode registrati
     */
    private static function count_registered_shortcodes(): int
    {
        global $shortcode_tags;
        return count($shortcode_tags);
    }

    /**
     * Conta shortcode base del plugin
     */
    private static function count_base_shortcodes(): int
    {
        $base_shortcodes = [
            'md_quote', 'quote', 'boxinfo', 'md_boxinfo', 'boxinformativo',
            'md_progressbar', 'progressbar', 'slidingbox', 'md_slidingbox',
            'flipbox', 'md_flipbox', 'blinkingbutton', 'md_blinkingbutton',
            'perfectpullquote', 'youtube', 'telefono', 'prezzo', 'flexlist'
        ];
        
        global $shortcode_tags;
        $count = 0;
        foreach ($base_shortcodes as $shortcode) {
            if (isset($shortcode_tags[$shortcode])) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Verifica se MCP REST API è abilitata
     * 
     * Nota importante:
     * - Questo verifica solo se la REST API WordPress (backend) è registrata
     * - La REST API gira su Cloudways e fornisce dati al server MCP Node.js
     * - Il server MCP Node.js gira localmente sul tuo PC e NON può essere verificato da qui
     * - Il server MCP Node.js si connette alla REST API WordPress via HTTP
     */
    private static function is_mcp_api_enabled(): bool
    {
        // Verifica che la classe esista
        if (!class_exists('\gik25microdata\REST\MCPApi')) {
            return false;
        }
        
        // Verifica direttamente se le route REST API sono registrate
        // Questo è il modo più affidabile per verificare se l'API è disponibile
        if (function_exists('rest_get_server')) {
            $server = rest_get_server();
            $routes = $server->get_routes();
            
            // Verifica se esiste almeno una route del namespace wp-mcp/v1
            foreach ($routes as $route => $handlers) {
                if (strpos($route, '/wp-mcp/v1/') !== false) {
                    return true; // Trovata almeno una route MCP
                }
            }
        }
        
        // Fallback: verifica se l'azione rest_api_init è registrata
        // MCPApi::init() registra 'rest_api_init' che chiama 'register_routes'
        if (has_action('rest_api_init')) {
            // Verifica se MCPApi::register_routes è registrato
            global $wp_filter;
            if (isset($wp_filter['rest_api_init'])) {
                foreach ($wp_filter['rest_api_init']->callbacks as $callbacks) {
                    foreach ($callbacks as $callback) {
                        if (is_array($callback['function']) && 
                            is_string($callback['function'][0]) &&
                            strpos($callback['function'][0], 'MCPApi') !== false) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

}

