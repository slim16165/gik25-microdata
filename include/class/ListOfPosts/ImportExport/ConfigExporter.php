<?php
namespace gik25microdata\ListOfPosts\ImportExport;

use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di Export/Import configurazioni link (JSON)
 */
class ConfigExporter
{
    /**
     * Esporta una collezione di link in JSON
     * 
     * @param Collection $links Collezione di link
     * @param array $metadata Metadata aggiuntive
     * @return string JSON
     */
    public static function export(Collection $links, array $metadata = []): string
    {
        $data = [
            'version' => '1.0',
            'exported_at' => current_time('mysql'),
            'metadata' => $metadata,
            'links' => [],
        ];
        
        foreach ($links as $link) {
            if ($link instanceof LinkBase) {
                $data['links'][] = [
                    'url' => $link->Url,
                    'title' => $link->Title,
                    'comment' => $link->Comment,
                ];
            } elseif (is_array($link)) {
                $data['links'][] = [
                    'url' => $link['target_url'] ?? $link['url'] ?? '',
                    'title' => $link['nome'] ?? $link['title'] ?? '',
                    'comment' => $link['commento'] ?? $link['comment'] ?? '',
                ];
            }
        }
        
        return wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Importa link da JSON
     * 
     * @param string $json JSON da importare
     * @return array Risultato ['success' => bool, 'links' => Collection, 'errors' => []]
     */
    public static function import(string $json): array
    {
        $result = [
            'success' => false,
            'links' => new Collection(),
            'errors' => [],
        ];
        
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['errors'][] = 'JSON non valido: ' . json_last_error_msg();
            return $result;
        }
        
        if (!isset($data['links']) || !is_array($data['links'])) {
            $result['errors'][] = 'Formato non valido: campo "links" mancante';
            return $result;
        }
        
        $links = new Collection();
        
        foreach ($data['links'] as $index => $linkData) {
            if (!isset($linkData['url']) || !isset($linkData['title'])) {
                $result['errors'][] = "Link #{$index}: URL o titolo mancante";
                continue;
            }
            
            $links->add(new LinkBase(
                sanitize_text_field($linkData['title']),
                esc_url_raw($linkData['url']),
                sanitize_text_field($linkData['comment'] ?? '')
            ));
        }
        
        $result['success'] = true;
        $result['links'] = $links;
        $result['metadata'] = $data['metadata'] ?? [];
        
        return $result;
    }
    
    /**
     * Esporta in file
     * 
     * @param Collection $links Collezione di link
     * @param string $filename Nome file
     * @param array $metadata Metadata
     * @return string|false Percorso file o false
     */
    public static function exportToFile(Collection $links, string $filename, array $metadata = [])
    {
        $json = self::export($links, $metadata);
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/gik25-exports';
        
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }
        
        $filepath = $export_dir . '/' . sanitize_file_name($filename);
        
        if (file_put_contents($filepath, $json) !== false) {
            return $filepath;
        }
        
        return false;
    }
    
    /**
     * Importa da file
     * 
     * @param string $filepath Percorso file
     * @return array Risultato import
     */
    public static function importFromFile(string $filepath): array
    {
        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'errors' => ['File non trovato'],
            ];
        }
        
        $json = file_get_contents($filepath);
        if ($json === false) {
            return [
                'success' => false,
                'errors' => ['Impossibile leggere il file'],
            ];
        }
        
        return self::import($json);
    }
    
    /**
     * Scarica export come file
     * 
     * @param Collection $links Collezione di link
     * @param string $filename Nome file
     * @param array $metadata Metadata
     */
    public static function download(Collection $links, string $filename, array $metadata = []): void
    {
        $json = self::export($links, $metadata);
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
        header('Content-Length: ' . strlen($json));
        
        echo $json;
        exit;
    }
}
