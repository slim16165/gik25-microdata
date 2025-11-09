<?php
namespace gik25microdata\Utility;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe per eseguire codice in modo sicuro senza bloccare WordPress
 * 
 * Gestisce:
 * - Try-catch automatici
 * - Disabilitazione logging durante esecuzione
 * - Ripristino stato originale
 * - Prevenzione loop infiniti
 */
class SafeExecution
{
    /**
     * Esegue una callback in modo sicuro, catturando tutti gli errori
     * 
     * @param callable $callback Funzione da eseguire
     * @param mixed $default_return Valore di ritorno in caso di errore (default: null)
     * @param bool $silent Se true, non logga errori (default: true per evitare loop)
     * @return mixed Risultato della callback o $default_return in caso di errore
     */
    public static function safe_execute(callable $callback, $default_return = null, bool $silent = true)
    {
        // Salva stato originale
        $original_state = self::disable_error_logging();
        
        try {
            return $callback();
        } catch (\Throwable $e) {
            // In modalità silenziosa, non fa nulla
            // In modalità non silenziosa, potrebbe loggare (ma con attenzione)
            if (!$silent && function_exists('error_log')) {
                // Log solo se non siamo già in una situazione di errore
                $error_context = self::get_error_context();
                if (!$error_context['in_error_handler']) {
                    @error_log(sprintf(
                        '[Revious Microdata] Errore gestito: %s in %s:%d',
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    ));
                }
            }
            
            return $default_return;
        } finally {
            // RIPRISTINA SEMPRE lo stato originale
            self::restore_error_logging($original_state);
        }
    }
    
    /**
     * Esegue una callback con limite di tempo e memoria
     * 
     * @param callable $callback Funzione da eseguire
     * @param int $time_limit Limite di tempo in secondi (default: 30)
     * @param string $memory_limit Limite di memoria (default: '256M')
     * @param mixed $default_return Valore di ritorno in caso di errore
     * @return mixed Risultato della callback o $default_return
     */
    public static function safe_execute_with_limits(
        callable $callback,
        int $time_limit = 30,
        string $memory_limit = '256M',
        $default_return = null
    ) {
        return self::safe_execute(function() use ($callback, $time_limit, $memory_limit) {
            // Salva limiti originali
            $old_time_limit = @ini_get('max_execution_time');
            $old_memory_limit = @ini_get('memory_limit');
            
            try {
                // Imposta nuovi limiti
                @set_time_limit($time_limit);
                @ini_set('memory_limit', $memory_limit);
                
                return $callback();
            } finally {
                // Ripristina limiti originali
                if ($old_time_limit !== false) {
                    @set_time_limit((int)$old_time_limit);
                }
                if ($old_memory_limit !== false) {
                    @ini_set('memory_limit', $old_memory_limit);
                }
            }
        }, $default_return, true);
    }
    
    /**
     * Wrappa un hook WordPress per eseguirlo in modo sicuro
     * 
     * @param string $hook_name Nome dell'hook
     * @param callable $callback Callback originale
     * @param int $priority Priorità (default: 10)
     * @param int $accepted_args Numero di argomenti (default: 1)
     */
    public static function safe_add_action(
        string $hook_name,
        callable $callback,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        // Proteggi anche la registrazione dell'action
        self::safe_execute(function() use ($hook_name, $callback, $priority, $accepted_args) {
            // Verifica che add_action sia disponibile (potrebbe non esserlo durante l'inizializzazione precoce)
            if (!function_exists('add_action')) {
                return; // WordPress non è ancora completamente caricato, skip
            }
            add_action($hook_name, function(...$args) use ($callback) {
                self::safe_execute(function() use ($callback, $args) {
                    return call_user_func_array($callback, $args);
                }, null, true);
            }, $priority, $accepted_args);
        }, null, true);
    }
    
    /**
     * Wrappa un filter WordPress per eseguirlo in modo sicuro
     * 
     * @param string $filter_name Nome del filter
     * @param callable $callback Callback originale
     * @param int $priority Priorità (default: 10)
     * @param int $accepted_args Numero di argomenti (default: 1)
     */
    public static function safe_add_filter(
        string $filter_name,
        callable $callback,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        // Proteggi anche la registrazione del filter
        self::safe_execute(function() use ($filter_name, $callback, $priority, $accepted_args) {
            // Verifica che add_filter sia disponibile (potrebbe non esserlo durante l'inizializzazione precoce)
            if (!function_exists('add_filter')) {
                return; // WordPress non è ancora completamente caricato, skip
            }
            add_filter($filter_name, function($value, ...$args) use ($callback) {
                return self::safe_execute(function() use ($callback, $value, $args) {
                    return call_user_func_array($callback, array_merge([$value], $args));
                }, $value, true); // Ritorna valore originale in caso di errore
            }, $priority, $accepted_args);
        }, null, true);
    }
    
    /**
     * Disabilita il logging degli errori in modo sicuro
     * 
     * @return array Stato originale da ripristinare
     */
    private static function disable_error_logging(): array
    {
        return [
            'error_reporting' => @error_reporting(0),
            'display_errors' => @ini_get('display_errors'),
            'log_errors' => @ini_get('log_errors'),
            'error_log' => @ini_get('error_log'),
        ];
    }
    
    /**
     * Ripristina il logging degli errori
     * 
     * @param array $state Stato originale da ripristinare
     */
    private static function restore_error_logging(array $state): void
    {
        if (isset($state['error_reporting'])) {
            @error_reporting($state['error_reporting']);
        }
        if (isset($state['display_errors'])) {
            @ini_set('display_errors', $state['display_errors']);
        }
        if (isset($state['log_errors'])) {
            @ini_set('log_errors', $state['log_errors']);
        }
        if (isset($state['error_log'])) {
            @ini_set('error_log', $state['error_log']);
        }
    }
    
    /**
     * Ottiene il contesto dell'errore corrente
     * 
     * @return array Contesto dell'errore
     */
    private static function get_error_context(): array
    {
        static $in_error_handler = false;
        
        return [
            'in_error_handler' => $in_error_handler,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
        ];
    }
    
    /**
     * Esegue un'operazione AJAX in modo sicuro
     * 
     * @param callable $callback Callback AJAX
     * @param string $action Nome dell'azione AJAX
     * @param bool $require_login Se true, richiede login (default: true)
     */
    public static function safe_ajax_handler(
        callable $callback,
        string $action,
        bool $require_login = true
    ): void {
        self::safe_execute(function() use ($callback, $action, $require_login) {
            if (!function_exists('add_action')) {
                return; // WordPress non è ancora completamente caricato
            }
            add_action('wp_ajax_' . $action, function() use ($callback, $require_login) {
                self::safe_execute(function() use ($callback, $require_login) {
                    if ($require_login && function_exists('is_user_logged_in') && !is_user_logged_in()) {
                        if (function_exists('wp_send_json_error')) {
                            wp_send_json_error('Non autenticato');
                        }
                        return;
                    }
                    
                    return $callback();
                }, null, true);
            });
            
            // Se supporta utenti non loggati
            add_action('wp_ajax_nopriv_' . $action, function() use ($callback) {
                self::safe_execute(function() use ($callback) {
                    return $callback();
                }, null, true);
            });
        }, null, true);
    }
    
    /**
     * Esegue un'operazione REST API in modo sicuro
     * 
     * @param string $namespace Namespace REST API
     * @param string $route Route REST API
     * @param callable $callback Callback
     * @param string $methods Metodi HTTP (default: 'GET')
     * @param callable|null $permission_callback Callback permessi (default: null = pubblico)
     */
    public static function safe_rest_route(
        string $namespace,
        string $route,
        callable $callback,
        string $methods = 'GET',
        ?callable $permission_callback = null
    ): void {
        self::safe_execute(function() use ($namespace, $route, $callback, $methods, $permission_callback) {
            if (!function_exists('add_action')) {
                return; // WordPress non è ancora completamente caricato
            }
            add_action('rest_api_init', function() use ($namespace, $route, $callback, $methods, $permission_callback) {
                self::safe_execute(function() use ($namespace, $route, $callback, $methods, $permission_callback) {
                    if (!function_exists('register_rest_route')) {
                        return; // REST API non disponibile
                    }
                    register_rest_route($namespace, $route, [
                        'methods' => $methods,
                        'callback' => function(...$args) use ($callback) {
                            return self::safe_execute(function() use ($callback, $args) {
                                return call_user_func_array($callback, $args);
                            }, new \WP_Error('execution_error', 'Errore durante l\'esecuzione'), true);
                        },
                        'permission_callback' => $permission_callback ?? function() {
                            return true;
                        },
                    ]);
                }, null, true);
            });
        }, null, true);
    }
}

