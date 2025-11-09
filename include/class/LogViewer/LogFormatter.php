<?php
namespace gik25microdata\LogViewer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility per formattazione log
 * 
 * Estrae logica di formattazione da HealthChecker per riutilizzo
 */
class LogFormatter
{
    /**
     * Formatta una riga di log per anteprima (troncata a 200 caratteri)
     * 
     * @param string $line Riga di log
     * @return string Riga formattata per anteprima
     */
    public static function format_preview(string $line): string
    {
        // Rimuovi timestamp Apache se presente per semplificare
        $line = preg_replace('/\[[^\]]+\]\s+\[[^\]]+\]\s+\[[^\]]+\]\s+\[[^\]]+\]\s+/', '', $line);
        
        // Tronca a 200 caratteri per anteprima
        if (strlen($line) > 200) {
            $line = substr($line, 0, 197) . '...';
        }
        
        return $line;
    }
    
    /**
     * Formatta una riga di log con colori ed evidenziazione
     * 
     * @param string $line Riga di log
     * @return array{html: string, class: string, severity: string, color: string, bg_color: string} HTML formattato con colori
     */
    public static function format_line(string $line): array
    {
        $severity = 'info';
        $color = '#666';
        $bg_color = '#f5f5f5';
        $class = 'log-line-info';
        
        // Identifica tipo di errore
        if (preg_match('/PHP Fatal error/i', $line)) {
            $severity = 'fatal';
            $color = '#8b0000';
            $bg_color = '#ffe6e6';
            $class = 'log-line-fatal';
        } elseif (preg_match('/PHP Parse error/i', $line)) {
            $severity = 'parse';
            $color = '#dc3232';
            $bg_color = '#fff0f0';
            $class = 'log-line-parse';
        } elseif (preg_match('/Uncaught Error/i', $line) || preg_match('/Uncaught Exception/i', $line)) {
            $severity = 'error';
            $color = '#ff6600';
            $bg_color = '#fff5e6';
            $class = 'log-line-error';
        } elseif (preg_match('/PHP Warning/i', $line)) {
            $severity = 'warning';
            $color = '#ffb900';
            $bg_color = '#fffbf0';
            $class = 'log-line-warning';
        } elseif (preg_match('/WordPress database error/i', $line) || preg_match('/database error/i', $line)) {
            $severity = 'database';
            $color = '#8b008b';
            $bg_color = '#f5f0ff';
            $class = 'log-line-database';
        } elseif (preg_match('/\s(5\d{2})\s/', $line)) {
            $severity = 'http5xx';
            $color = '#dc3232';
            $bg_color = '#fff0f0';
            $class = 'log-line-http5xx';
        }
        
        // Costruisci HTML segmentando la riga e escapando solo le parti di testo
        $parts = [];
        $last_pos = 0;
        
        // Trova tutti i match per file PHP
        preg_match_all('/([\/\w\-\.]+\.php)(?::(\d+))?/i', $line, $file_matches, PREG_OFFSET_CAPTURE);
        
        // Trova tutti i match per classi
        preg_match_all('/([a-zA-Z_][a-zA-Z0-9_\\\\]*\\\\[a-zA-Z_][a-zA-Z0-9_]*)/', $line, $class_matches, PREG_OFFSET_CAPTURE);
        
        // Combina tutti i match e ordina per posizione
        $all_matches = [];
        foreach ($file_matches[0] as $idx => $match) {
            $all_matches[] = [
                'pos' => $match[1],
                'len' => strlen($match[0]),
                'type' => 'file',
                'content' => $match[0],
                'file' => $file_matches[1][$idx][0],
                'line' => isset($file_matches[2][$idx]) ? $file_matches[2][$idx][0] : null,
            ];
        }
        foreach ($class_matches[0] as $match) {
            $all_matches[] = [
                'pos' => $match[1],
                'len' => strlen($match[0]),
                'type' => 'class',
                'content' => $match[0],
            ];
        }
        
        // Ordina per posizione
        usort($all_matches, function($a, $b) {
            return $a['pos'] - $b['pos'];
        });
        
        // Costruisci HTML
        foreach ($all_matches as $match) {
            // Aggiungi testo prima del match
            if ($match['pos'] > $last_pos) {
                $text_before = substr($line, $last_pos, $match['pos'] - $last_pos);
                $parts[] = esc_html($text_before);
            }
            
            // Aggiungi match formattato
            if ($match['type'] === 'file') {
                $file_html = esc_html($match['file']);
                $line_html = $match['line'] ? ':' . esc_html($match['line']) : '';
                $parts[] = '<strong style="color: ' . esc_attr($color) . ';">' . $file_html . $line_html . '</strong>';
            } elseif ($match['type'] === 'class') {
                $class_html = esc_html($match['content']);
                $parts[] = '<code style="background: rgba(0,0,0,0.1); padding: 1px 3px; border-radius: 2px;">' . $class_html . '</code>';
            }
            
            $last_pos = $match['pos'] + $match['len'];
        }
        
        // Aggiungi testo rimanente
        if ($last_pos < strlen($line)) {
            $text_after = substr($line, $last_pos);
            $parts[] = esc_html($text_after);
        }
        
        $html = implode('', $parts);
        
        // Se non ci sono match, escapa tutta la riga
        if (empty($all_matches)) {
            $html = esc_html($line);
        }
        
        return [
            'html' => $html,
            'class' => $class,
            'severity' => $severity,
            'color' => $color,
            'bg_color' => $bg_color,
        ];
    }
    
    /**
     * Estrae severity da una riga di log
     * 
     * @param string $line Riga di log
     * @return string Severity: fatal, parse, error, warning, database, http5xx, info
     */
    public static function extract_severity(string $line): string
    {
        if (preg_match('/PHP Fatal error/i', $line)) {
            return 'fatal';
        } elseif (preg_match('/PHP Parse error/i', $line)) {
            return 'parse';
        } elseif (preg_match('/Uncaught Error/i', $line) || preg_match('/Uncaught Exception/i', $line)) {
            return 'error';
        } elseif (preg_match('/PHP Warning/i', $line)) {
            return 'warning';
        } elseif (preg_match('/WordPress database error/i', $line) || preg_match('/database error/i', $line)) {
            return 'database';
        } elseif (preg_match('/\s(5\d{2})\s/', $line)) {
            return 'http5xx';
        }
        
        return 'info';
    }
    
    /**
     * Formatta severity come badge HTML
     * 
     * @param string $severity Severity: fatal, parse, error, warning, database, http5xx, info
     * @return string HTML badge
     */
    public static function format_severity_badge(string $severity): string
    {
        $colors = [
            'fatal' => ['bg' => '#8b0000', 'text' => '#fff'],
            'parse' => ['bg' => '#dc3232', 'text' => '#fff'],
            'error' => ['bg' => '#ff6600', 'text' => '#fff'],
            'warning' => ['bg' => '#ffb900', 'text' => '#000'],
            'database' => ['bg' => '#8b008b', 'text' => '#fff'],
            'http5xx' => ['bg' => '#dc3232', 'text' => '#fff'],
            'info' => ['bg' => '#666', 'text' => '#fff'],
        ];
        
        $color = $colors[$severity] ?? $colors['info'];
        $label = strtoupper($severity);
        
        return sprintf(
            '<span style="display: inline-block; padding: 2px 8px; border-radius: 3px; background: %s; color: %s; font-size: 11px; font-weight: bold;">%s</span>',
            esc_attr($color['bg']),
            esc_attr($color['text']),
            esc_html($label)
        );
    }
}

