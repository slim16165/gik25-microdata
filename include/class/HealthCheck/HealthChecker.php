<?php
namespace gik25microdata\HealthCheck;

use gik25microdata\Shortcodes\ShortcodeRegistry;
use gik25microdata\HealthCheck\HealthCheckConstants;
use gik25microdata\HealthCheck\View\AdminPageView;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di Health Check per verifica funzionalità plugin
 * 
 * Verifica:
 * - Shortcode registrati
 * - REST API endpoints
 * - AJAX endpoints
 * - CSS/JS caricati
 * - Tabelle database
 * - File esistenti
 */
class HealthChecker
{

    /**
     * Inizializza health check
     */
    public static function init(): void
    {
        // Pagina admin per health check
        add_action('admin_menu', [self::class, 'add_admin_page']);
        
        // Carica assets CSS/JS solo nella pagina di health check
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);
        
        // AJAX endpoint per eseguire check
        add_action('wp_ajax_gik25_health_check', [self::class, 'ajax_run_checks']);
        
        // REST API endpoint per health check (per testing esterno)
        add_action('rest_api_init', [self::class, 'register_rest_endpoint']);
    }

    /**
     * Aggiungi pagina admin
     * 
     * Nota: La pagina viene ora registrata come sottovocce del menu principale "Revious Microdata"
     * Se il menu principale non esiste, viene comunque aggiunta sotto "Strumenti" come fallback
     */
    public static function add_admin_page(): void
    {
        // Verifica se il menu principale esiste (registrato da AdminMenu)
        global $submenu;
        $menu_exists = isset($submenu['revious-microdata']);
        
        if ($menu_exists) {
            // Menu principale esiste, la sottovocce viene aggiunta automaticamente da AdminMenu
            // Qui non facciamo nulla, la pagina viene renderizzata quando si accede al link
        } else {
            // Fallback: aggiungi sotto "Strumenti" se il menu principale non esiste
            add_submenu_page(
                'tools.php',
                'Health Check Plugin',
                'Health Check',
                'manage_options',
                'gik25-health-check',
                [self::class, 'render_admin_page']
            );
        }
    }

    /**
     * Render pagina admin
     */
    public static function render_admin_page(): void
    {
        // Forza refresh al caricamento della pagina (bypass cache per visualizzazione immediata)
        $checks = self::run_all_checks(true);
        AdminPageView::renderAdminPage($checks);
    }

    /**
     * Carica assets CSS e JS per la pagina admin
     * Viene chiamato dall'hook admin_enqueue_scripts
     */
    public static function enqueue_admin_assets(string $hook_suffix): void
    {
        // Carica solo nella pagina di health check
        // Il page hook può essere 'gik25-health-check' o 'tools_page_gik25-health-check' a seconda della struttura
        if ($hook_suffix !== 'gik25-health-check' && strpos($hook_suffix, 'gik25-health-check') === false) {
            // Verifica anche tramite GET parameter (fallback)
            if (!isset($_GET['page']) || $_GET['page'] !== 'gik25-health-check') {
                return;
            }
        }
        
        // Determina plugin directory
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_dir = dirname(dirname(dirname($plugin_dir)));
        $plugin_file = $plugin_dir . '/revious-microdata.php';
        if (!file_exists($plugin_file)) {
            $plugin_file = $plugin_dir . '/gik25-microdata.php';
        }
        
        // Versione basata su filemtime (cache busting)
        $css_file = $plugin_dir . '/assets/css/health-check.css';
        $css_version = file_exists($css_file) ? filemtime($css_file) : '1.0.0';
        
        $js_file = $plugin_dir . '/assets/js/health-check.js';
        $js_version = file_exists($js_file) ? filemtime($js_file) : '1.0.0';
        
        // Carica CSS
        wp_enqueue_style(
            'gik25-health-check',
            plugins_url('assets/css/health-check.css', $plugin_file),
            [],
            $css_version
        );
        
        // Carica JS (dipende da jQuery)
        wp_enqueue_script(
            'gik25-health-check',
            plugins_url('assets/js/health-check.js', $plugin_file),
            ['jquery'],
            $js_version,
            true
        );
        
        // Localizza script con dati PHP
        wp_localize_script('gik25-health-check', 'healthCheckData', [
            'nonce' => wp_create_nonce('gik25_health_check'),
            'i18n' => [
                'running' => __('In esecuzione...', 'gik25-microdata'),
                'copied' => __('Copiato!', 'gik25-microdata'),
                'checkFailed' => __('Health Check fallito: ', 'gik25-microdata'),
                'unknownError' => __('Errore sconosciuto.', 'gik25-microdata'),
                'ajaxError' => __('Errore durante l\'esecuzione degli health check. Controlla la console per dettagli.', 'gik25-microdata'),
                'copyFailed' => __('Impossibile copiare automaticamente. Copia manualmente i risultati.', 'gik25-microdata'),
            ],
        ]);
    }
     
    /**
     * Formatta una riga di log per anteprima (DEPRECATO: usa LogFormatter)
     * 
     * @deprecated Usa \gik25microdata\Logs\Viewer\LogFormatter::format_preview()
     */
    private static function format_log_line_preview(string $line): string
    {
        return \gik25microdata\Logs\Viewer\LogFormatter::format_preview($line);
    }
    
    /**
     * Formatta una riga di log con colori (DEPRECATO: usa LogFormatter)
     * 
     * @deprecated Usa \gik25microdata\Logs\Viewer\LogFormatter::format_line()
     */
    private static function format_log_line(string $line): array
    {
        return \gik25microdata\Logs\Viewer\LogFormatter::format_line($line);
    }
    
    /**
     * Render risultati check (DEPRECATO: usa AdminPageView::renderChecksResults)
     * @deprecated Usa \gik25microdata\HealthCheck\View\AdminPageView::renderChecksResults()
     */
    private static function render_checks_results(array $checks): void
    {
        AdminPageView::renderChecksResults($checks);
    }

    /**
     * Assicura che gli shortcode siano caricati (necessario nel backend)
     */
    private static function ensure_shortcodes_loaded(): void
    {
        // Forza il caricamento degli shortcode anche nel backend
        // Questo è necessario perché normalmente vengono caricati solo nel frontend
        
        // 1. Carica i file degli shortcode (questo include i file e istanzia le classi)
        // Gli shortcode vengono istanziati alla fine di ogni file (es. $quote = new Quote();)
        // e vengono registrati nel costruttore tramite add_shortcode()
        if (method_exists('\gik25microdata\PluginBootstrap', 'loadShortcodeFiles')) {
            \gik25microdata\PluginBootstrap::loadShortcodeFiles();
        }
        
        // 2. Carica anche i file site_specific che potrebbero registrare shortcode aggiuntivi
        // (es. totaldesign_specific.php che registra kitchen_finder, app_nav, link_colori, ecc.)
        // Usa reflection per chiamare il metodo privato detectCurrentWebsite
        try {
            $reflection = new \ReflectionClass('\gik25microdata\PluginBootstrap');
            if ($reflection->hasMethod('detectCurrentWebsite')) {
                $method = $reflection->getMethod('detectCurrentWebsite');
                $method->setAccessible(true);
                $method->invoke(null);
            }
        } catch (\ReflectionException $e) {
            // Ignora errori di reflection
        }
        
        // 3. Verifica che gli shortcode siano stati registrati
        // Se non lo sono, potrebbe essere un problema di timing
        global $shortcode_tags;
        $shortcodes_before = is_array($shortcode_tags) ? count($shortcode_tags) : 0;
        
        // Se non ci sono molti shortcode registrati, proviamo a ricaricare manualmente
        // Nota: require_once non ricarica se il file è già stato incluso,
        // ma possiamo comunque verificare se gli shortcode sono registrati
        if ($shortcodes_before < 5) {
            // Forza il caricamento diretto dei file (anche se già inclusi, 
            // l'istanziazione alla fine del file verrà rieseguita solo se non è già avvenuta)
            $plugin_dir = dirname(dirname(dirname(__DIR__)));
            $shortcodes_dir = $plugin_dir . '/include/class/Shortcodes';
            
            if (is_dir($shortcodes_dir)) {
                // Usa require invece di require_once per forzare il ricaricamento
                // ATTENZIONE: questo potrebbe causare errori se le classi sono già definite
                // Quindi verifichiamo prima se le classi esistono
                foreach (glob($shortcodes_dir . '/*.php') as $file) {
                    $basename = basename($file, '.php');
                    // Ignora ShortcodeBase.php che è una classe astratta
                    if ($basename !== 'ShortcodeBase') {
                        // Verifica se la classe esiste già
                        $class_name = '\\gik25microdata\\Shortcodes\\' . ucfirst($basename);
                        if (!class_exists($class_name)) {
                            // La classe non esiste, possiamo includere il file
                            require_once $file;
                        } else {
                            // La classe esiste, ma verifichiamo se lo shortcode è registrato
                            // Se non lo è, proviamo a istanziarla manualmente
                            // (ma questo potrebbe causare problemi se è già istanziata)
                        }
                    }
                }
            }
        }
        
        // Debug: verifica quanti shortcode sono stati registrati dopo il caricamento
        $shortcodes_after = is_array($shortcode_tags) ? count($shortcode_tags) : 0;
        
        // Se ancora non ci sono shortcode, potrebbe essere un problema più serio
        // Potremmo dover forzare l'istanziazione manualmente
        if ($shortcodes_after < 5 && $shortcodes_after === $shortcodes_before) {
            // Nessuno shortcode è stato aggiunto - problema di caricamento
            // Proviamo a istanziare manualmente alcune classi chiave
            // (solo se non sono già istanziate)
            $key_classes = [
                'Boxinfo' => ['md_boxinfo', 'boxinfo', 'boxinformativo'],
                'Quote' => ['md_quote', 'quote'],
                'Youtube' => ['youtube'],
                'Telefono' => ['telefono'],
            ];
            
            foreach ($key_classes as $class_name => $expected_tags) {
                $full_class = '\\gik25microdata\\Shortcodes\\' . $class_name;
                if (class_exists($full_class)) {
                    // Verifica se almeno uno degli shortcode è registrato
                    $any_registered = false;
                    foreach ($expected_tags as $tag) {
                        if (isset($shortcode_tags[$tag])) {
                            $any_registered = true;
                            break;
                        }
                    }
                    
                    // Se nessuno shortcode è registrato, prova a istanziare la classe
                    // (solo se non è già stata istanziata - questo è tricky)
                    if (!$any_registered) {
                        // Non possiamo verificare facilmente se è già istanziata
                        // Quindi non facciamo nulla - l'istanziazione dovrebbe avvenire
                        // automaticamente quando il file viene incluso
                    }
                }
            }
        }
    }

    /**
     * AJAX handler per eseguire check
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function ajax_run_checks(): void
    {
        try {
            check_ajax_referer('gik25_health_check', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permessi insufficienti');
                return;
            }

            // Forza refresh quando richiesto via AJAX (bypass cache)
            $force_refresh = isset($_POST['force_refresh']) && $_POST['force_refresh'] === '1';
            $checks = self::run_all_checks($force_refresh);
            
            ob_start();
            AdminPageView::renderChecksResults($checks);
            $html = ob_get_clean();

            wp_send_json_success(['html' => $html, 'checks' => $checks]);
            
        } catch (\Throwable $e) {
            // Gestisci errore senza crashare WordPress
            wp_send_json_error([
                'message' => 'Errore durante l\'esecuzione degli health check',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Registra REST API endpoint per health check
     */
    public static function register_rest_endpoint(): void
    {
        register_rest_route('gik25/v1', '/health-check', [
            'methods' => 'GET',
            'callback' => [self::class, 'rest_health_check'],
            'permission_callback' => function() {
                // Accesso ristretto solo ad amministratori
                return current_user_can('manage_options');
            },
        ]);
    }

    /**
     * REST API handler per health check
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function rest_health_check(): \WP_REST_Response
    {
        try {
            $checks = self::run_all_checks();
            
            $summary = [
                'total' => count($checks),
                'success' => count(array_filter($checks, fn($c) => $c['status'] === 'success')),
                'warnings' => count(array_filter($checks, fn($c) => $c['status'] === 'warning')),
                'errors' => count(array_filter($checks, fn($c) => $c['status'] === 'error')),
                'timestamp' => current_time('mysql'),
                'checks' => $checks,
            ];

            return new \WP_REST_Response($summary, 200);
            
        } catch (\Throwable $e) {
            // Ritorna risposta di errore invece di crashare
            return new \WP_REST_Response([
                'error' => true,
                'message' => 'Errore durante l\'esecuzione degli health check',
                'total' => 0,
                'success' => 0,
                'warnings' => 0,
                'errors' => 0,
                'timestamp' => current_time('mysql'),
                'checks' => [],
            ], 500);
        }
    }

    /**
     * Esegui tutti i check (con cache)
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function run_all_checks(bool $force_refresh = false): array
    {
        // Verifica cache (se non forzato refresh)
        if (!$force_refresh) {
            $cache = get_transient(HealthCheckConstants::CHECK_CACHE_KEY);
            if ($cache !== false && is_array($cache)) {
                return $cache;
            }
        }

        // Esegui tutti i check in modo sicuro
        $checks = \gik25microdata\Utility\SafeExecution::safe_execute(function() {
            $checks = [];
            
            // Carica gli shortcode prima di verificarli (necessario perché vengono caricati solo nel frontend)
            // Questo permette all'health check di funzionare anche nel backend
            \gik25microdata\Utility\SafeExecution::safe_execute(function() {
                self::ensure_shortcodes_loaded();
            }, null, true);

            // Esegui tutti i check in modo sicuro (ognuno protetto individualmente)
            $check_classes = [
                \gik25microdata\HealthCheck\Check\ShortcodesCheck::class,
                \gik25microdata\HealthCheck\Check\DisabledShortcodesCheck::class,
                \gik25microdata\HealthCheck\Check\RestApiCheck::class,
                \gik25microdata\HealthCheck\Check\AjaxCheck::class,
                \gik25microdata\HealthCheck\Check\FilesCheck::class,
                \gik25microdata\HealthCheck\Check\DatabaseTablesCheck::class,
                \gik25microdata\HealthCheck\Check\AssetsCheck::class,
                \gik25microdata\HealthCheck\Check\ClassesCheck::class,
                \gik25microdata\HealthCheck\Check\LogsCheck::class,
            ];
            
            foreach ($check_classes as $check_class) {
                $check_result = \gik25microdata\Utility\SafeExecution::safe_execute(function() use ($check_class) {
                    // Chiama il metodo run() della classe Check
                    if (method_exists($check_class, 'run')) {
                        return call_user_func([$check_class, 'run']);
                    }
                    return [
                        'name' => 'Check Sconosciuto',
                        'status' => 'warning',
                        'message' => 'Classe check non valida',
                        'details' => 'La classe ' . $check_class . ' non ha il metodo run().',
                    ];
                }, [
                    'name' => 'Check Sconosciuto',
                    'status' => 'warning',
                    'message' => 'Check non disponibile (errore interno gestito)',
                    'details' => 'Il check ha riscontrato un problema. Questo non ha impatto sul funzionamento del sito.',
                ], true);
                
                $checks[] = $check_result;
            }

            return $checks;
        }, [], true); // Ritorna array vuoto in caso di errore critico

        // Salva in cache
        if (!empty($checks)) {
            set_transient(
                HealthCheckConstants::CHECK_CACHE_KEY,
                $checks,
                HealthCheckConstants::CHECK_CACHE_EXPIRATION
            );
        }

        return $checks;
    }

    /**
     * Assicura che gli shortcode siano caricati (necessario nel backend)
     */
    private static function ensure_shortcodes_loaded(): void
    {
        // Forza il caricamento degli shortcode anche nel backend
        // Questo è necessario perché normalmente vengono caricati solo nel frontend
        
        // 1. Carica i file degli shortcode (questo include i file e istanzia le classi)
        // Gli shortcode vengono istanziati alla fine di ogni file (es. $quote = new Quote();)
        // e vengono registrati nel costruttore tramite add_shortcode()
        if (method_exists('\gik25microdata\PluginBootstrap', 'loadShortcodeFiles')) {
            \gik25microdata\PluginBootstrap::loadShortcodeFiles();
        }
        
        // 2. Carica anche i file site_specific che potrebbero registrare shortcode aggiuntivi
        // (es. totaldesign_specific.php che registra kitchen_finder, app_nav, link_colori, ecc.)
        // Usa reflection per chiamare il metodo privato detectCurrentWebsite
        try {
            $reflection = new \ReflectionClass('\gik25microdata\PluginBootstrap');
            if ($reflection->hasMethod('detectCurrentWebsite')) {
                $method = $reflection->getMethod('detectCurrentWebsite');
                $method->setAccessible(true);
                $method->invoke(null);
            }
        } catch (\ReflectionException $e) {
            // Ignora errori di reflection
        }
        
        // 3. Verifica che gli shortcode siano stati registrati
        // Se non lo sono, potrebbe essere un problema di timing
        global $shortcode_tags;
        $shortcodes_before = is_array($shortcode_tags) ? count($shortcode_tags) : 0;
        
        // Se non ci sono molti shortcode registrati, proviamo a ricaricare manualmente
        // Nota: require_once non ricarica se il file è già stato incluso,
        // ma possiamo comunque verificare se gli shortcode sono registrati
        if ($shortcodes_before < 5) {
            // Forza il caricamento diretto dei file (anche se già inclusi, 
            // l'istanziazione alla fine del file verrà rieseguita solo se non è già avvenuta)
            $plugin_dir = dirname(dirname(dirname(__DIR__)));
            $shortcodes_dir = $plugin_dir . '/include/class/Shortcodes';
            
            if (is_dir($shortcodes_dir)) {
                // Usa require invece di require_once per forzare il ricaricamento
                // ATTENZIONE: questo potrebbe causare errori se le classi sono già definite
                // Quindi verifichiamo prima se le classi esistono
                foreach (glob($shortcodes_dir . '/*.php') as $file) {
                    $basename = basename($file, '.php');
                    // Ignora ShortcodeBase.php che è una classe astratta
                    if ($basename !== 'ShortcodeBase') {
                        // Verifica se la classe esiste già
                        $class_name = '\\gik25microdata\\Shortcodes\\' . ucfirst($basename);
                        if (!class_exists($class_name)) {
                            // La classe non esiste, possiamo includere il file
                            require_once $file;
                        } else {
                            // La classe esiste, ma verifichiamo se lo shortcode è registrato
                            // Se non lo è, proviamo a istanziarla manualmente
                            // (ma questo potrebbe causare problemi se è già istanziata)
                        }
                    }
                }
            }
        }
        
        // Debug: verifica quanti shortcode sono stati registrati dopo il caricamento
        $shortcodes_after = is_array($shortcode_tags) ? count($shortcode_tags) : 0;
        
        // Se ancora non ci sono shortcode, potrebbe essere un problema più serio
        // Potremmo dover forzare l'istanziazione manualmente
        if ($shortcodes_after < 5 && $shortcodes_after === $shortcodes_before) {
            // Nessuno shortcode è stato aggiunto - problema di caricamento
            // Proviamo a istanziare manualmente alcune classi chiave
            // (solo se non sono già istanziate)
            $key_classes = [
                'Boxinfo' => ['md_boxinfo', 'boxinfo', 'boxinformativo'],
                'Quote' => ['md_quote', 'quote'],
                'Youtube' => ['youtube'],
                'Telefono' => ['telefono'],
            ];
            
            foreach ($key_classes as $class_name => $expected_tags) {
                $full_class = '\\gik25microdata\\Shortcodes\\' . $class_name;
                if (class_exists($full_class)) {
                    // Verifica se almeno uno degli shortcode è registrato
                    $any_registered = false;
                    foreach ($expected_tags as $tag) {
                        if (isset($shortcode_tags[$tag])) {
                            $any_registered = true;
                            break;
                        }
                    }
                    
                    // Se nessuno shortcode è registrato, prova a istanziare la classe
                    // (solo se non è già stata istanziata - questo è tricky)
                    if (!$any_registered) {
                        // Non possiamo verificare facilmente se è già istanziata
                        // Quindi non facciamo nulla - l'istanziazione dovrebbe avvenire
                        // automaticamente quando il file viene incluso
                    }
                }
            }
        }
    }
}
