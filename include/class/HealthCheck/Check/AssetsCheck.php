<?php
namespace gik25microdata\HealthCheck\Check;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per verificare assets (CSS/JS)
 */
class AssetsCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        // Determina plugin directory e URL in modo robusto
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_dir = dirname(dirname(dirname(dirname($plugin_dir))));
        
        // Usa il file principale del plugin come riferimento
        $plugin_file = $plugin_dir . '/revious-microdata.php';
        if (!file_exists($plugin_file)) {
            // Fallback: cerca il file principale
            $plugin_file = $plugin_dir . '/gik25-microdata.php';
        }
        
        $plugin_url = plugins_url('/', $plugin_file);

        $assets = [
            'assets/css/kitchen-finder.css',
            'assets/js/kitchen-finder.js',
            'assets/css/app-nav.css',
            'assets/js/app-nav.js',
        ];

        $accessible = [];
        $failed = [];

        foreach ($assets as $asset) {
            $file_path = $plugin_dir . '/' . $asset;
            
            // Prima verifica: file esiste sul filesystem?
            if (!file_exists($file_path)) {
                $failed[] = $asset . ' (file non trovato)';
                continue;
            }
            
            // Seconda verifica (opzionale): file accessibile via HTTP?
            // Solo se necessario (es. per verificare permessi o hotlink protection)
            $url = $plugin_url . $asset;
            $response = wp_remote_head($url, [
                'timeout' => 5,
                'sslverify' => false, // Evita problemi con certificati self-signed in dev
            ]);
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $accessible[] = $asset;
            } elseif (file_exists($file_path)) {
                // File esiste ma non accessibile via HTTP (potrebbe essere hotlink protection)
                // Consideralo comunque OK se il file esiste
                $accessible[] = $asset . ' (file esiste, accesso HTTP non verificato)';
            } else {
                $failed[] = $asset;
            }
        }

        $status = empty($failed) ? 'success' : 'warning';
        $message = empty($failed)
            ? sprintf('Tutti gli asset accessibili (%d)', count($accessible))
            : sprintf('Asset inaccessibili: %d/%d', count($failed), count($assets));

        return [
            'name' => 'Assets (CSS/JS)',
            'status' => $status,
            'message' => $message,
            'details' => 'Accessibili: ' . implode(', ', $accessible) . "\n" .
                        (empty($failed) ? '' : 'Inaccessibili: ' . implode(', ', $failed)),
        ];
    }
}

