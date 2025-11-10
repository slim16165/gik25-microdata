<?php
namespace gik25microdata\HealthCheck\Check;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per verificare AJAX endpoints
 */
class AjaxCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        // AJAX endpoints configurabili tramite filter
        $optional_actions = apply_filters('gik25/healthcheck/ajax_actions', [
            'kitchen_finder_calculate',
            'kitchen_finder_pdf',
        ]);

        $registered = [];
        $missing = [];

        foreach ($optional_actions as $action) {
            $hook_logged = 'wp_ajax_' . $action;
            $hook_nopriv = 'wp_ajax_nopriv_' . $action;
            
            // Usa has_action() per verificare se gli hook sono registrati
            $has_logged = has_action($hook_logged);
            $has_nopriv = has_action($hook_nopriv);
            
            if ($has_logged || $has_nopriv) {
                $registered[] = $action;
            } else {
                $missing[] = $action;
            }
        }

        // Se kitchen_finder shortcode non è registrato, questi endpoint sono opzionali
        global $shortcode_tags;
        $kitchen_finder_exists = isset($shortcode_tags['kitchen_finder']);
        
        if (empty($registered) && !$kitchen_finder_exists) {
            // Se kitchen_finder non esiste, questi endpoint non sono necessari
            $status = 'success';
            $message = 'Endpoint AJAX: Nessun endpoint richiesto (kitchen_finder non attivo)';
            $details = 'Kitchen Finder non è attivo su questo sito, quindi gli endpoint AJAX non sono necessari.';
        } elseif (!empty($missing) && $kitchen_finder_exists) {
            // Se kitchen_finder esiste ma gli endpoint mancano, è un errore
            $status = 'error';
            $message = sprintf('Endpoint AJAX mancanti: %s', implode(', ', $missing));
            $details = 'Kitchen Finder è attivo ma gli endpoint AJAX non sono registrati.';
        } elseif (empty($missing)) {
            $status = 'success';
            $message = sprintf('Tutti gli endpoint AJAX registrati (%d)', count($registered));
            $details = 'Registrati: ' . implode(', ', $registered);
        } else {
            // Warning se alcuni endpoint mancano ma kitchen_finder non è attivo
            $status = 'success'; // Non è un errore se kitchen_finder non è attivo
            $message = sprintf('Endpoint AJAX opzionali: %d registrati, %d mancanti (non necessari)', 
                count($registered), 
                count($missing)
            );
            $details = 'Questi endpoint sono opzionali e non sono necessari perché kitchen_finder non è attivo.';
        }

        return [
            'name' => 'AJAX Endpoints',
            'status' => $status,
            'message' => $message,
            'details' => $details . "\n" .
                        (empty($registered) ? '' : 'Registrati: ' . implode(', ', $registered) . "\n") .
                        (empty($missing) ? '' : 'Mancanti (opzionali): ' . implode(', ', $missing)),
        ];
    }
}

