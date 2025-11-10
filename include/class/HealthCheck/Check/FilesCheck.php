<?php
namespace gik25microdata\HealthCheck\Check;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per verificare file critici
 */
class FilesCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_dir = dirname(dirname(dirname(dirname($plugin_dir))));

        $critical_files = [
            'include/class/PluginBootstrap.php',
            'include/class/Shortcodes/KitchenFinder.php',
            'include/class/Shortcodes/AppNav.php',
            'assets/css/kitchen-finder.css',
            'assets/js/kitchen-finder.js',
            'assets/css/app-nav.css',
            'assets/js/app-nav.js',
        ];

        $existing = [];
        $missing = [];

        foreach ($critical_files as $file) {
            $path = $plugin_dir . '/' . $file;
            if (file_exists($path)) {
                $existing[] = $file;
            } else {
                $missing[] = $file;
            }
        }

        $status = empty($missing) ? 'success' : 'error';
        $message = empty($missing)
            ? sprintf('Tutti i file critici presenti (%d)', count($existing))
            : sprintf('File mancanti: %d/%d', count($missing), count($critical_files));

        return [
            'name' => 'File Critici',
            'status' => $status,
            'message' => $message,
            'details' => 'Presenti: ' . implode(', ', $existing) . "\n" .
                        (empty($missing) ? '' : 'Mancanti: ' . implode(', ', $missing)),
        ];
    }
}

