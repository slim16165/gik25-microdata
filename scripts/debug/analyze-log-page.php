<?php
/**
 * Pagina WordPress temporanea per analizzare un file di log
 * 
 * NOTA: Questo script è stato spostato in scripts/debug/
 * 
 * Utilizzo:
 * 1. Carica questo file nel browser: /wp-content/plugins/gik25-microdata/scripts/debug/analyze-log-page.php
 * 2. Oppure aggiungi ?analyze_log=1 alla URL di admin
 * 
 * ATTENZIONE: Questo script è principalmente per debugging. L'analisi log principale
 * è disponibile tramite Health Check nella pagina admin WordPress.
 */

// Carica WordPress
// scripts/debug/analyze-log-page.php -> 4 livelli su per arrivare alla root WordPress
$wp_load_paths = [
    __DIR__ . '/../../../../wp-load.php',
    __DIR__ . '/../../../../../wp-load.php',
    dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $wp_path) {
    if (file_exists($wp_path)) {
        require_once($wp_path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('WordPress non trovato. Assicurati di eseguire questo script dalla directory del plugin.');
}

// Verifica che l'utente sia autenticato e abbia i permessi
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('Accesso negato. Devi essere loggato come amministratore.');
}

// Percorso del file di log
$log_file_path = isset($_GET['log_file']) 
    ? sanitize_text_field($_GET['log_file'])
    : 'C:\Users\g.salvi\Dropbox\Siti internet\Altri siti\Principali\TotalDesign.it\wp-content\plugins\gik25-microdata\logs\apache_wordpress-1340912-5319280.cloudwaysapps.com.error.log';

// Converti percorso Windows
$log_file_path = str_replace('\\', '/', $log_file_path);

// Verifica che la classe esista
if (!class_exists('\gik25microdata\Logs\Analysis\CloudwaysLogParser')) {
    wp_die('Errore: CloudwaysLogParser non trovato. Verifica che il plugin sia attivo.');
}

use gik25microdata\Logs\Analysis\CloudwaysLogParser;

// Esegui l'analisi
$result = CloudwaysLogParser::analyze_specific_log_file($log_file_path, 10000);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Analisi Log - CloudwaysLogParser</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 20px;
            background: #f0f0f1;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1d2327;
            border-bottom: 2px solid #2271b1;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .status.success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .status.error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .issue {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid #2271b1;
            background: #f9f9f9;
        }
        .issue.error {
            border-left-color: #dc3232;
        }
        .issue.warning {
            border-left-color: #f56e28;
        }
        .issue h3 {
            margin-top: 0;
            color: #1d2327;
        }
        .issue-meta {
            color: #646970;
            font-size: 14px;
            margin: 10px 0;
        }
        .examples {
            margin-top: 15px;
            background: white;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .example {
            padding: 8px;
            margin: 5px 0;
            font-family: monospace;
            font-size: 12px;
            background: #f5f5f5;
            border-left: 3px solid #2271b1;
            word-break: break-all;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .summary-item {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
            text-align: center;
        }
        .summary-item strong {
            display: block;
            font-size: 24px;
            color: #2271b1;
            margin-bottom: 5px;
        }
        code {
            background: #f0f0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Analisi File di Log</h1>
        
        <div class="status <?php echo esc_attr($result['status']); ?>">
            <?php echo esc_html($result['message']); ?>
        </div>
        
        <div class="summary">
            <div class="summary-item">
                <strong><?php echo esc_html($result['total_errors'] ?? 0); ?></strong>
                <span>Errori</span>
            </div>
            <div class="summary-item">
                <strong><?php echo esc_html($result['total_warnings'] ?? 0); ?></strong>
                <span>Warning</span>
            </div>
            <div class="summary-item">
                <strong><?php echo esc_html(count($result['issues'] ?? [])); ?></strong>
                <span>Problemi Totali</span>
            </div>
        </div>
        
        <p><strong>File analizzato:</strong> <code><?php echo esc_html($result['file']); ?></code></p>
        
        <?php if (!empty($result['issues'])): ?>
            <h2>Problemi Trovati</h2>
            
            <?php foreach ($result['issues'] as $index => $issue): ?>
                <div class="issue <?php echo esc_attr($issue['severity']); ?>">
                    <h3>
                        <?php if ($issue['severity'] === 'error'): ?>
                            ❌ Problema #<?php echo $index + 1; ?> - <?php echo esc_html($issue['type']); ?>
                        <?php else: ?>
                            ⚠️ Problema #<?php echo $index + 1; ?> - <?php echo esc_html($issue['type']); ?>
                        <?php endif; ?>
                    </h3>
                    
                    <div class="issue-meta">
                        <strong>Severità:</strong> <?php echo strtoupper(esc_html($issue['severity'])); ?><br>
                        <strong>Messaggio:</strong> <?php echo esc_html($issue['message']); ?><br>
                        <strong>Occorrenze:</strong> <?php echo esc_html($issue['count']); ?>
                        
                        <?php if (!empty($issue['contexts'])): ?>
                            <br><strong>Contesti:</strong> <?php echo esc_html(implode(', ', $issue['contexts'])); ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($issue['examples'])): ?>
                        <div class="examples">
                            <strong>Esempi di errori (max 5):</strong>
                            <?php foreach ($issue['examples'] as $example): ?>
                                <div class="example"><?php echo esc_html($example); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>✅ Nessun problema trovato nel file di log!</p>
        <?php endif; ?>
        
        <hr>
        <p><small>Analisi completata il <?php echo date('d/m/Y H:i:s'); ?></small></p>
    </div>
</body>
</html>

