<?php
/**
 * Script per analizzare un file di log specifico usando CloudwaysLogParser
 * 
 * Utilizzo:
 * php analyze-log.php "percorso/al/file.log"
 * 
 * Oppure modifica la variabile $log_file_path sotto
 */

// Carica WordPress se eseguito nel contesto WordPress
// scripts/debug/analyze-log.php -> 4 livelli su per arrivare alla root del plugin
if (file_exists(__DIR__ . '/../../../../wp-load.php')) {
    require_once(__DIR__ . '/../../../../wp-load.php');
}

// Verifica che la classe esista
if (!class_exists('\gik25microdata\Logs\Analysis\CloudwaysLogParser')) {
    // Prova a caricare il file direttamente
    // scripts/debug/analyze-log.php -> 4 livelli su per arrivare alla root del plugin
    $parser_file = __DIR__ . '/../../include/class/Logs/Analysis/CloudwaysLogParser.php';
    if (file_exists($parser_file)) {
        require_once($parser_file);
    } else {
        die("Errore: CloudwaysLogParser non trovato. Esegui questo script dalla directory del plugin.\n");
    }
}

use gik25microdata\Logs\Analysis\CloudwaysLogParser;

// Percorso del file di log da analizzare
// Puoi passarlo come argomento da riga di comando o modificarlo qui
$log_file_path = $argv[1] ?? 'C:\Users\g.salvi\Dropbox\Siti internet\Altri siti\Principali\TotalDesign.it\wp-content\plugins\gik25-microdata\logs\apache_wordpress-1340912-5319280.cloudwaysapps.com.error.log';

// Converti il percorso Windows in formato Unix se necessario
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    // Su Linux/Mac, non fare nulla
} else {
    // Su Windows, normalizza il percorso
    $log_file_path = str_replace('\\', '/', $log_file_path);
}

echo "Analisi del file di log: $log_file_path\n";
echo str_repeat('=', 80) . "\n\n";

// Verifica che il file esista
if (!file_exists($log_file_path)) {
    die("Errore: Il file di log non esiste: $log_file_path\n");
}

// Analizza il file
$result = CloudwaysLogParser::analyze_specific_log_file($log_file_path, 10000);

// Mostra i risultati
echo "STATO: " . strtoupper($result['status']) . "\n";
echo "Messaggio: " . $result['message'] . "\n";
echo "File analizzato: " . $result['file'] . "\n";
echo "Errori totali: " . ($result['total_errors'] ?? 0) . "\n";
echo "Warning totali: " . ($result['total_warnings'] ?? 0) . "\n\n";

if (!empty($result['issues'])) {
    echo str_repeat('-', 80) . "\n";
    echo "PROBLEMI TROVATI:\n";
    echo str_repeat('-', 80) . "\n\n";
    
    $issue_num = 1;
    foreach ($result['issues'] as $issue) {
        $severity_icon = $issue['severity'] === 'error' ? '❌' : '⚠️';
        echo "$severity_icon PROBLEMA #$issue_num\n";
        echo "   Tipo: " . $issue['type'] . "\n";
        echo "   Severità: " . strtoupper($issue['severity']) . "\n";
        echo "   Messaggio: " . $issue['message'] . "\n";
        echo "   Occorrenze: " . $issue['count'] . "\n";
        
        if (!empty($issue['contexts'])) {
            echo "   Contesti: " . implode(', ', $issue['contexts']) . "\n";
        }
        
        if (!empty($issue['examples'])) {
            echo "   Esempi:\n";
            foreach ($issue['examples'] as $example_index => $example) {
                echo "      " . ($example_index + 1) . ". " . substr($example, 0, 150) . "\n";
                if (strlen($example) > 150) {
                    echo "         ... (troncato)\n";
                }
            }
        }
        
        echo "\n";
        $issue_num++;
    }
} else {
    echo "\n✅ Nessun problema trovato nel file di log!\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "Analisi completata.\n";

