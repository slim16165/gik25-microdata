<?php
namespace gik25microdata;

use gik25microdata\Utility\OptimizationHelper;
use gik25microdata\Utility\SafeExecution;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Classe principale per il bootstrap e l'inizializzazione del plugin
 */
class PluginBootstrap
{
    /**
     * @var string Percorso della directory del plugin
     */
    private static string $plugin_dir;
    
    /**
     * @var string Path del file principale del plugin
     */
    private static string $plugin_file;

    /**
     * Inizializza il plugin
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function init(string $plugin_file): void
    {
        // Esegui inizializzazione in modo sicuro
        SafeExecution::safe_execute(function() use ($plugin_file) {
            self::$plugin_dir = dirname($plugin_file);
            self::$plugin_file = $plugin_file;
            
            // Registra error handlers (protetto)
            SafeExecution::safe_execute(function() {
                self::registerErrorHandlers();
            }, null, true);
            
            // Verifica e gestisce dipendenze Composer (protetto)
            $dependencies_ok = SafeExecution::safe_execute(function() {
                return self::checkComposerDependencies();
            }, false, true);
            
            if (!$dependencies_ok) {
                return; // Plugin non caricato se mancano dipendenze
            }
            
            // Carica autoloader (protetto)
            $autoload_path = self::$plugin_dir . '/vendor/autoload.php';
            if (file_exists($autoload_path)) {
                SafeExecution::safe_execute(function() use ($autoload_path) {
                    require_once $autoload_path;
                }, null, true);
            }
            
            // Inizializza tabelle database (protetto)
            SafeExecution::safe_execute(function() {
                self::initializeDatabase();
            }, null, true);
            
            // Inizializza il plugin (protetto)
            SafeExecution::safe_execute(function() {
                self::initializePlugin();
            }, null, true);
        }, null, true);
    }
    
    /**
     * Inizializza tabelle database
     */
    private static function initializeDatabase(): void
    {
        try {
            // Tabelle caroselli generici
            if (class_exists('\gik25microdata\Database\CarouselCollections')) {
                \gik25microdata\Database\CarouselCollections::init(self::$plugin_file);
            }
            
            // Tabelle template caroselli
            if (class_exists('\gik25microdata\Database\CarouselTemplates')) {
                \gik25microdata\Database\CarouselTemplates::init(self::$plugin_file);
            }
        } catch (\Throwable $e) {
            self::logError('Errore nell\'inizializzazione database caroselli', $e);
        }
    }

    /**
     * Verifica e gestisce le dipendenze Composer
     * @return bool True se le dipendenze sono disponibili, false altrimenti
     */
    private static function checkComposerDependencies(): bool
    {
        $autoload_path = self::$plugin_dir . '/vendor/autoload.php';
        
        if (file_exists($autoload_path)) {
            return true;
        }

        // Tenta installazione automatica se possibile
        $auto_install_attempted = false;
        $auto_install_result = null;

        if (is_admin() && !defined('DOING_AJAX') && current_user_can('manage_options')) {
            $nonce_value = '';
            if (isset($_GET['revious_auto_install_composer'])) {
                $nonce_value = sanitize_text_field(wp_unslash($_GET['revious_auto_install_composer']));
            }
            if ($nonce_value && wp_verify_nonce($nonce_value, 'revious_install_composer')) {
                $auto_install_result = self::autoInstallComposer();
                $auto_install_attempted = true;
            }
        }

        // Mostra notifica admin (protetto)
        SafeExecution::safe_add_action('admin_notices', function() use ($auto_install_attempted, $auto_install_result) {
            if ($auto_install_attempted && $auto_install_result && $auto_install_result['success']) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>Revious Microdata:</strong> ' . esc_html($auto_install_result['message']) . '</p>';
                echo '<p>Le dipendenze sono state installate automaticamente. La pagina verrà ricaricata.</p>';
                echo '</div>';
                echo '<script>setTimeout(function(){ location.reload(); }, 2000);</script>';
                return;
            }

            echo '<div class="notice notice-error">';
            echo '<p><strong>Revious Microdata: Dipendenze mancanti</strong></p>';

            if ($auto_install_attempted && $auto_install_result && !$auto_install_result['success']) {
                echo '<p><strong>Installazione automatica non riuscita:</strong> ' . esc_html($auto_install_result['message']) . '</p>';
                if (!empty($auto_install_result['output'])) {
                    echo '<details><summary>Dettagli output</summary><pre style="max-height: 220px; overflow: auto;">';
                    echo esc_html(implode("\n", $auto_install_result['output']));
                    echo '</pre></details>';
                }
            }

            echo '<p>La directory <code>vendor/</code> non è stata trovata.</p>';

            if (is_admin() && current_user_can('manage_options')) {
                $install_url = add_query_arg(
                    [
                        'revious_auto_install_composer' => wp_create_nonce('revious_install_composer'),
                    ],
                    self_admin_url('plugins.php')
                );
                echo '<p>';
                echo '<a href="' . esc_url($install_url) . '" class="button button-primary">Installa dipendenze automaticamente</a>';
                echo ' <span style="margin-left: 10px;">oppure esegui manualmente via SSH:</span>';
                echo '</p>';
            } else {
                echo '<p>Esegui questo comando via SSH:</p>';
            }

            echo '<pre style="background: #f5f5f5; padding: 10px; border-left: 4px solid #2271b1;">';
            echo 'cd ' . esc_html(self::$plugin_dir) . ' && composer install --no-dev';
            echo '</pre>';
            echo '<p>Se non hai Composer installato sul server, installalo prima o contatta il tuo amministratore di sistema.</p>';
            echo '<p>Puoi impostare il percorso di PHP nella pagina <em>Impostazioni ▸ Revious Microdata Settings</em> per facilitare l\'installazione automatica.</p>';
            echo '</div>';
        });

        // Se l'installazione automatica è riuscita, continua
        if ($auto_install_attempted && $auto_install_result && $auto_install_result['success'] && file_exists($autoload_path)) {
            return true;
        }

        return false;
    }

    /**
     * Inizializza il plugin con gestione errori robusta
     */
    private static function initializePlugin(): void
    {
        try {
            // Costanti e setup base (sempre eseguite)
            if (!defined('MY_PLUGIN_PATH')) {
                define('MY_PLUGIN_PATH', plugins_url(basename(self::$plugin_dir) . '/revious-microdata.php'));
            }
            if (!defined('PLUGIN_NAME_PREFIX')) {
                define('PLUGIN_NAME_PREFIX', 'md_');
            }

            // Hook sempre attivi (XML-RPC, etc.) - eseguiti in tutti i contesti (protetto)
            SafeExecution::safe_add_filter('xmlrpc_methods', [self::class, 'removeXmlrpcMethods']);

            // Inizializzazione condizionale per contesto
            if (defined('DOING_AJAX') && DOING_AJAX) {
                // Assicurati che gli shortcode vengano caricati anche in contesto AJAX
                // in modo che gli hook wp_ajax_* siano registrati
                self::loadShortcodeFiles();
                // Contesto AJAX: gli endpoint AJAX vengono registrati nel costruttore delle classi 
                // (es. KitchenFinder in include/class/Shortcodes/kitchenfinder.php)
                // Queste classi vengono automaticamente caricate dall'autoloader di Composer quando 
                // WordPress cerca i callback degli endpoint. L'istanziazione alla fine dei file 
                // (es. $kitchen_finder = new KitchenFinder()) registra gli hook add_action 
                // nel costruttore prima che WordPress invochi i callback.
                // 
                // Endpoint AJAX del plugin:
                // - kitchen_finder_calculate (logged-in + non-logged-in)
                // - kitchen_finder_pdf (logged-in + non-logged-in)
                //
                // Nessun early return - permettere la registrazione degli endpoint tramite autoloader
            } elseif (is_admin()) {
                self::initializeAdmin();
            } else {
                self::initializeFrontend();
            }
        } catch (\Throwable $e) {
            // Catch generale per errori non previsti
            self::logError('Errore critico durante l\'inizializzazione del plugin', $e);
            // Il plugin si disabilita silenziosamente, WordPress continua a funzionare
            return;
        }
    }

    /**
     * Inizializza il contesto Admin
     */
    private static function initializeAdmin(): void
    {
        // Carica gli shortcode anche nel backend (necessario per health check e altre funzionalità)
        // Gli shortcode devono essere disponibili anche nel backend per essere verificati (protetto)
        SafeExecution::safe_add_action('init', function () {
            SafeExecution::safe_execute(function() {
                self::loadShortcodeFiles();
            }, null, true);
        }, 1);
        
        // Carica anche i file site_specific nel backend (per shortcode aggiuntivi) (protetto)
        SafeExecution::safe_execute(function() {
            self::detectCurrentWebsite();
        }, null, true);
        
        // Menu admin principale (deve essere registrato per primo) (protetto)
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\Admin\AdminMenu')) {
                \gik25microdata\Admin\AdminMenu::init();
            }
        }, null, true);

        // Shortcodes unified page (protetto) - gestisce sia Gestione che Utilizzo
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\Admin\ShortcodesUnifiedPage')) {
                \gik25microdata\Admin\ShortcodesUnifiedPage::init();
            }
            // Le pagine figlie vengono inizializzate automaticamente quando vengono renderizzate
            if (class_exists('\gik25microdata\Admin\ShortcodesManagerPage')) {
                \gik25microdata\Admin\ShortcodesManagerPage::init();
            }
            if (class_exists('\gik25microdata\Admin\ShortcodesUsagePage')) {
                \gik25microdata\Admin\ShortcodesUsagePage::init();
            }
        }, null, true);
        
        // Carousels Page (unificata con tab: gestione, migrazione, test) (protetto)
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\Admin\CarouselsPage')) {
                \gik25microdata\Admin\CarouselsPage::init();
            }
        }, null, true);
        
        // Health Check (solo admin) (protetto)
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\HealthCheck\HealthChecker')) {
                \gik25microdata\HealthCheck\HealthChecker::init();
            }
        }, null, true);

        // Tools tab (protetto)
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\Admin\ToolsPage')) {
                \gik25microdata\Admin\ToolsPage::init();
            }
        }, null, true);
        
        // Carica settings page (protetto)
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\Admin\SettingsPage')) {
                new \gik25microdata\Admin\SettingsPage();
            }
        }, null, true);
        
        // CarouselManager, MigrationPreview, CarouselTester sono ora unificati in CarouselsPage (inizializzato sopra)
        
        // Istanzia helper admin (con gestione errori individuale) (protetto)
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\Admin\AdminHelper')) {
                new \gik25microdata\Admin\AdminHelper();
            }
        }, null, true);
        
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\WPSettings\HeaderHelper')) {
                new \gik25microdata\WPSettings\HeaderHelper();
            }
        }, null, true);
        
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\WPSettings\WordpressBehaviourModifier')) {
                new \gik25microdata\WPSettings\WordpressBehaviourModifier();
            }
        }, null, true);
    }

    /**
     * Inizializza il contesto Front-End
     */
    private static function initializeFrontend(): void
    {
        // Ottimizzazioni - spostato su template_redirect per evitare warning is_single()
        // La funzione usa is_single() che non funziona prima che la query WordPress sia eseguita (protetto)
        SafeExecution::safe_add_action('template_redirect', function() {
            SafeExecution::safe_execute(function() {
                if (class_exists('\gik25microdata\Utility\OptimizationHelper')) {
                    OptimizationHelper::ConditionalLoadCssJsOnPostsWhichContainAnyEnabledShortcode();
                }
            }, null, true);
        }, 5); // Priorità 5 per eseguire prima di altre azioni su template_redirect
        
        // Carica tutte le classi Shortcodes (compatibilità con filesystem case-sensitive) (protetto)
        SafeExecution::safe_add_action('init', function () {
            SafeExecution::safe_execute(function() {
                self::loadShortcodeFiles();
            }, null, true);
        }, 1);
        
        // Rilevamento automatico sito (protetto)
        SafeExecution::safe_execute(function() {
            self::detectCurrentWebsite();
        }, null, true);
        
        // ColorWidget (protetto)
        SafeExecution::safe_execute(function() {
            if (class_exists('\gik25microdata\Widgets\ColorWidget')) {
                \gik25microdata\Widgets\ColorWidget::Initialize();
            } elseif (class_exists('\gik25microdata\ColorWidget')) {
                // Backward compatibility
                \gik25microdata\ColorWidget::Initialize();
            }
        }, null, true);
        
        // Debug: verifica shortcode registrati (solo se Query Monitor è attivo) (protetto)
        SafeExecution::safe_add_action('wp_loaded', function() {
            SafeExecution::safe_execute(function() {
                if (!function_exists('do_action')) {
                    return;
                }
                global $shortcode_tags;
                $plugin_shortcodes = [];
                
                // Lista degli shortcode del plugin
                $expected_shortcodes = [
                    'kitchen_finder', 'md_boxinfo', 'boxinfo', 'boxinformativo',
                    'md_quote', 'quote', 'youtube', 'telefono', 'slidingbox',
                    'progressbar', 'prezzo', 'flipbox', 'flexlist', 'blinkingbutton',
                    'perfectpullquote', 'link_colori', 'grafica3d', 'archistar'
                ];
                
                foreach ($expected_shortcodes as $tag) {
                    if (isset($shortcode_tags[$tag])) {
                        $handler = $shortcode_tags[$tag];
                        if (is_array($handler)) {
                            $handler_info = is_object($handler[0]) 
                                ? get_class($handler[0]) . '::' . $handler[1]
                                : $handler[0] . '::' . $handler[1];
                        } else {
                            $handler_info = is_string($handler) ? $handler : 'Closure';
                        }
                        $plugin_shortcodes[$tag] = $handler_info;
                    } else {
                        $plugin_shortcodes[$tag] = 'NON REGISTRATO';
                    }
                }
                
                // Formatta l'array come stringa leggibile per QM
                $debug_message = "Shortcode registrati dal plugin:\n";
                foreach ($plugin_shortcodes as $tag => $handler) {
                    $status = ($handler === 'NON REGISTRATO') ? '❌' : '✅';
                    $debug_message .= sprintf("%s [%s] => %s\n", $status, $tag, $handler);
                }
                
                do_action('qm/debug', $debug_message);
            });
        }, 999); // Priorità alta per eseguire dopo tutte le registrazioni
    }

    /**
     * Carica tutti i file degli shortcode
     * Metodo pubblico per permettere il caricamento anche nel backend (es. per health check)
     * Carica i file delle classi Shortcodes per registrare shortcode e hook AJAX
     */
    /**
     * Carica i file degli shortcode
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    public static function loadShortcodeFiles(): void
    {
        SafeExecution::safe_execute(function() {
            $shortcodes_dir = self::$plugin_dir . '/include/class/Shortcodes';
            if (is_dir($shortcodes_dir)) {
                $files = @glob($shortcodes_dir . '/*.php');
                if ($files !== false) {
                    foreach ($files as $file) {
                        SafeExecution::safe_execute(function() use ($file) {
                            require_once $file;
                        }, null, true);
                    }
                }
            }

            SafeExecution::safe_execute(function() {
                if (class_exists('\gik25microdata\Shortcodes\ShortcodeRegistry')) {
                    \gik25microdata\Shortcodes\ShortcodeRegistry::init();
                }
            }, null, true);
        }, null, true);
    }

    /**
     * Rileva automaticamente il sito corrente e carica il file specifico
     * PROTETTO: gestisce errori senza bloccare WordPress
     */
    private static function detectCurrentWebsite(): void
    {
        SafeExecution::safe_execute(function() {
            if (!isset($_SERVER['HTTP_HOST'])) {
                return; // Non possiamo determinare il dominio
            }
            
            $domain_specific_files = [
                'www.nonsolodiete.it' => 'nonsolodiete_specific.php',
                'www.superinformati.com' => 'superinformati_specific.php',
                'www.totaldesign.it' => 'totaldesign_specific.php',
                // Aggiungi altre corrispondenze qui
            ];

            $current_domain = $_SERVER['HTTP_HOST'];

            if (array_key_exists($current_domain, $domain_specific_files)) {
                $specific_file = $domain_specific_files[$current_domain];
                $file_path = self::$plugin_dir . '/include/site_specific/' . $specific_file;
                
                // Verifica che il file esista prima di richiederlo
                if (file_exists($file_path)) {
                    require_once($file_path);
                } else {
                    self::logError("File specifico per dominio non trovato: {$specific_file}");
                }
            }
        }, null, true);
    }

    /**
     * Rimuove il metodo system.multicall da XML-RPC per sicurezza
     */
    public static function removeXmlrpcMethods($methods): mixed
    {
        try {
            if (is_array($methods) && isset($methods['system.multicall'])) {
                unset($methods['system.multicall']);
            }
            return $methods;
        } catch (\Throwable $e) {
            self::logError('Errore nella rimozione dei metodi XML-RPC', $e);
            return $methods; // Restituisce i metodi originali in caso di errore
        }
    }

    /**
     * Determina il percorso del file di log utilizzato da WordPress/PHP
     * @return array{path:string,url:string,exists:bool}
     */
    private static function getLogLocation(): array
    {
        $path = '';
        $url = '';
        $exists = false;

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $path = is_string(WP_DEBUG_LOG) ? WP_DEBUG_LOG : trailingslashit(WP_CONTENT_DIR) . 'debug.log';
            if (!preg_match('#^(?:[a-zA-Z]:)?[\\\\/]#', $path)) {
                $path = trailingslashit(WP_CONTENT_DIR) . ltrim($path, '/\\');
            }
        } else {
            $ini_log = ini_get('error_log');
            if (!empty($ini_log)) {
                $path = $ini_log;
                if (!preg_match('#^(?:[a-zA-Z]:)?[\\\\/]#', $path)) {
                    $path = trailingslashit(ABSPATH) . ltrim($path, '/\\');
                }
            }
        }

        if ($path && (!function_exists('wp_is_stream') || !wp_is_stream($path))) {
            $exists = file_exists($path);
            if (defined('WP_CONTENT_DIR') && defined('WP_CONTENT_URL')) {
                $normalized_content_dir = function_exists('wp_normalize_path')
                    ? wp_normalize_path(WP_CONTENT_DIR)
                    : str_replace('\\', '/', WP_CONTENT_DIR);
                $normalized_path = function_exists('wp_normalize_path')
                    ? wp_normalize_path($path)
                    : str_replace('\\', '/', $path);

                if (strpos($normalized_path, $normalized_content_dir) === 0) {
                    $relative = ltrim(substr($normalized_path, strlen($normalized_content_dir)), '/');
                    $url = trailingslashit(WP_CONTENT_URL) . $relative;
                }
            }
        }

        return [
            'path' => $path,
            'url' => $url,
            'exists' => $exists,
        ];
    }

    /**
     * Log errori del plugin in modo sicuro senza far crashare WordPress
     * PROTETTO: non logga se siamo già in una situazione di errore per evitare loop
     */
    public static function logError(string $message, ?\Throwable $exception = null): void
    {
        // Esegui logging in modo sicuro
        SafeExecution::safe_execute(function() use ($message, $exception) {
            if (function_exists('error_log')) {
                $log_message = '[Revious Microdata] ' . $message;
                if ($exception instanceof \Throwable) {
                    $log_message .= ' | Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine();
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        $log_message .= "\n" . $exception->getTraceAsString();
                    }
                }
                @error_log($log_message); // Usa @ per evitare errori durante il logging
            }
        }, null, true);
        
        $log_location = SafeExecution::safe_execute(function() {
            return self::getLogLocation();
        }, ['path' => '', 'url' => '', 'exists' => false], true);

        // Mostra notifica admin solo in backend e non durante AJAX (protetto)
        if (is_admin() && !defined('DOING_AJAX')) {
            SafeExecution::safe_add_action('admin_notices', function() use ($message, $exception, $log_location) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>Revious Microdata:</strong> ' . esc_html($message) . '</p>';
                
                // Mostra sempre i dettagli dell'errore se disponibili
                if ($exception instanceof \Throwable) {
                    $error_file = str_replace(ABSPATH, '', $exception->getFile());
                    echo '<p><strong>Dettagli errore:</strong></p>';
                    echo '<ul style="margin-left: 20px;">';
                    echo '<li><strong>Messaggio:</strong> ' . esc_html($exception->getMessage()) . '</li>';
                    echo '<li><strong>File:</strong> <code>' . esc_html($error_file) . '</code></li>';
                    echo '<li><strong>Linea:</strong> ' . esc_html($exception->getLine()) . '</li>';
                    echo '</ul>';
                    
                    // Mostra stack trace se WP_DEBUG è attivo
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        echo '<details style="margin-top: 10px;"><summary style="cursor: pointer; color: #0073aa;">Stack trace (click per espandere)</summary>';
                        echo '<pre style="background: #f5f5f5; padding: 10px; max-height: 300px; overflow: auto; font-size: 11px;">';
                        echo esc_html($exception->getTraceAsString());
                        echo '</pre></details>';
                    }
                }
                
                echo '<p><em>Nota: Il componente specifico è stato disabilitato, ma il resto del plugin continua a funzionare.</em></p>';

                if (!empty($log_location['path'])) {
                    echo '<p><strong>Log dettagliato:</strong> ';
                    if (!empty($log_location['url']) && (!isset($log_location['exists']) || $log_location['exists'])) {
                        echo '<a href="' . esc_url($log_location['url']) . '" target="_blank" rel="noopener noreferrer">';
                        echo esc_html($log_location['path']);
                        echo '</a>';
                    } else {
                        echo '<code>' . esc_html($log_location['path']) . '</code>';
                        if ($log_location['exists'] === false) {
                            echo ' (non ancora creato)';
                        }
                    }
                    echo '</p>';
                } else {
                    // Prova a trovare il log PHP standard
                    $php_log = ini_get('error_log');
                    if (!empty($php_log)) {
                        echo '<p><strong>Log PHP del server:</strong> <code>' . esc_html($php_log) . '</code></p>';
                    } else {
                        echo '<p><em>Non è stato possibile determinare il file di log. Controlla il log PHP del server o abilita WP_DEBUG_LOG in wp-config.php.</em></p>';
                    }
                }

                echo '</div>';
            });
        }
    }

    /**
     * Recupera il percorso di PHP da utilizzare per eseguire Composer
     */
    private static function getPhpBinary(): string
    {
        $php_path = '';

        $options = get_option('revious_microdata_option_name');
        if (is_array($options) && !empty($options['php_binary_path'])) {
            $php_path = trim((string) $options['php_binary_path']);
        } elseif (defined('REVIOUS_MICRODATA_PHP_BINARY') && REVIOUS_MICRODATA_PHP_BINARY) {
            $php_path = trim((string) REVIOUS_MICRODATA_PHP_BINARY);
        } elseif (defined('PHP_BINARY') && PHP_BINARY) {
            $php_path = trim((string) PHP_BINARY);
        }

        if ($php_path === '') {
            return 'php';
        }

        return trim($php_path, "\"' ");
    }

    /**
     * Effettua l'escape di un argomento per la shell supportando ambienti Windows e Unix
     */
    private static function escapeShellArg(string $arg): string
    {
        if ($arg === '') {
            return DIRECTORY_SEPARATOR === '\\' ? '""' : "''";
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $escaped = str_replace('"', '""', $arg);
            $escaped = preg_replace('/(\\\\+)$/', '$1$1', $escaped);
            return '"' . $escaped . '"';
        }

        return "'" . str_replace("'", "'\\''", $arg) . "'";
    }

    /**
     * Costruisce un comando per eseguire un file .phar tramite PHP
     */
    private static function buildPhpCommand(string $php_binary, string $phar_path): string
    {
        $phar_arg = self::escapeShellArg($phar_path);

        $normalized_php = trim($php_binary);
        if ($normalized_php === '' || strtolower($normalized_php) === 'php') {
            return 'php ' . $phar_arg;
        }

        $php_arg = self::escapeShellArg(trim($normalized_php, "\"' "));

        return $php_arg . ' ' . $phar_arg;
    }

    /**
     * Individua l'eseguibile di Composer disponibile nel sistema
     */
    private static function findComposer(): ?string
    {
        if (!function_exists('exec')) {
            return null;
        }

        $php_binary = self::getPhpBinary();

        $commands = [];

        $local_phar = self::$plugin_dir . '/composer.phar';
        if (file_exists($local_phar)) {
            $commands[] = self::buildPhpCommand($php_binary, $local_phar);
        }

        $parent_phar = self::$plugin_dir . '/../composer.phar';
        if (file_exists($parent_phar)) {
            $commands[] = self::buildPhpCommand($php_binary, $parent_phar);
        }

        $commands[] = 'composer';

        foreach ($commands as $command) {
            $output = [];
            $status = 0;
            @exec($command . ' --version 2>&1', $output, $status);
            if ((int) $status === 0 && !empty($output)) {
                return $command;
            }
        }

        return null;
    }

    /**
     * Esegue composer install in modo controllato dal backend
     * @return array{success:bool,message:string,output:array<int,string>}
     */
    private static function autoInstallComposer(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'output' => [],
        ];

        if (!is_admin() || defined('DOING_AJAX') || defined('DOING_CRON')) {
            $result['message'] = 'Composer install può essere eseguito solo da amministratori nell\'area backend.';
            return $result;
        }

        if (!function_exists('exec')) {
            $result['message'] = 'La funzione PHP exec() è disabilitata sul server; impossibile eseguire Composer automaticamente.';
            return $result;
        }

        if (!current_user_can('manage_options')) {
            $result['message'] = 'Permessi insufficienti per eseguire Composer install.';
            return $result;
        }

        $composer = self::findComposer();
        if (!$composer) {
            $result['message'] = 'Composer non trovato. Imposta il percorso di PHP nelle impostazioni del plugin o assicurati che Composer sia disponibile.';
            return $result;
        }

        $command = sprintf(
            '%s --working-dir=%s install --no-dev --no-interaction --no-scripts --prefer-dist 2>&1',
            $composer,
            self::escapeShellArg(self::$plugin_dir)
        );

        $output = [];
        $status = 0;
        $start = microtime(true);
        $max_execution_time = 120;
        $previous_limit = ini_get('max_execution_time');
        if ((int) $previous_limit > 0 && (int) $previous_limit < $max_execution_time) {
            @set_time_limit($max_execution_time);
        }

        @exec($command, $output, $status);

        if ((int) $previous_limit > 0) {
            @set_time_limit((int) $previous_limit);
        }

        $duration = (int) round(microtime(true) - $start);
        $result['output'] = $output;

        if ((int) $status === 0) {
            clearstatcache(true, self::$plugin_dir . '/vendor/autoload.php');
            if (file_exists(self::$plugin_dir . '/vendor/autoload.php')) {
                $result['success'] = true;
                $result['message'] = sprintf(
                    'Composer install completato con successo in %d secondi.',
                    max(1, $duration)
                );
                $php_used = self::getPhpBinary();
                if (!empty($php_used)) {
                    $result['message'] .= ' PHP utilizzato: ' . $php_used;
                }
                if (function_exists('error_log')) {
                    error_log('[Revious Microdata] ' . $result['message']);
                }
            } else {
                $result['message'] = 'Composer install completato ma vendor/autoload.php non è stato trovato.';
            }
        } else {
            $result['message'] = sprintf(
                'Errore durante Composer install (exit code: %d).',
                (int) $status
            );
            $php_used = self::getPhpBinary();
            if (!empty($php_used)) {
                $result['message'] .= ' PHP utilizzato: ' . $php_used;
            }
            if (function_exists('error_log')) {
                error_log('[Revious Microdata] ' . $result['message'] . "\n" . implode("\n", $output));
            }
        }

        return $result;
    }

    /**
     * Registra gli error handler per catturare errori del plugin
     */
    private static function registerErrorHandlers(): void
    {
        /**
         * Error handler per catturare errori non fatali del plugin
         */
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            // Ignora errori che non sono del plugin
            if (strpos($errfile, self::$plugin_dir) !== 0 && strpos($errfile, 'gik25-microdata') === false) {
                return false; // Lascia che WordPress gestisca l'errore
            }
            
            // Log solo errori gravi (warning e superiori)
            if (in_array($errno, [E_WARNING, E_USER_WARNING, E_ERROR, E_USER_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                self::logError(
                    "Errore PHP [{$errno}]: {$errstr}",
                    new \ErrorException($errstr, 0, $errno, $errfile, $errline)
                );
            }
            
            // Non interferiamo con il normale flusso di WordPress
            return false;
        }, E_WARNING | E_USER_WARNING | E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);

        /**
         * Registra un error handler per catturare errori fatali del plugin
         * Questo evita che il plugin causi errori critici in WordPress
         */
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $file = $error['file'] ?? '';
                // Verifica se l'errore è nel plugin
                if (strpos($file, self::$plugin_dir) === 0 || strpos($file, 'gik25-microdata') !== false) {
                    self::logError(
                        'Errore fatale: ' . $error['message'],
                        new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
                    );
                }
            }
        });
    }
}

