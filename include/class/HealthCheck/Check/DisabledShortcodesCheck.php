<?php
namespace gik25microdata\HealthCheck\Check;

use gik25microdata\Shortcodes\ShortcodeRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per verificare se shortcode disabilitati sono ancora presenti in contenuti
 */
class DisabledShortcodesCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        if (!class_exists(ShortcodeRegistry::class)) {
            return [
                'name' => 'Uso shortcode disabilitati',
                'status' => 'success',
                'message' => 'Registro shortcode non disponibile (nessun controllo effettuato).',
                'details' => '',
            ];
        }

        $items = ShortcodeRegistry::getItemsForAdmin();
        $disabled = array_filter($items, static fn ($item) => empty($item['enabled']));

        if (empty($disabled)) {
            return [
                'name' => 'Uso shortcode disabilitati',
                'status' => 'success',
                'message' => 'Nessuno shortcode disabilitato.',
                'details' => '',
            ];
        }

        global $wpdb;
        $violations = [];

        foreach ($disabled as $slug => $item) {
            $like = '%[' . $wpdb->esc_like($slug) . '%';
            $sql = $wpdb->prepare(
                "SELECT ID, post_title 
                 FROM {$wpdb->posts} 
                 WHERE post_status NOT IN ('trash','auto-draft','inherit')
                   AND post_content LIKE %s
                 LIMIT 3",
                $like
            );
            $rows = $wpdb->get_results($sql, ARRAY_A);
            if (!empty($rows)) {
                $first = $rows[0];
                $violations[] = [
                    'label' => $item['label'] ?? $slug,
                    'slug' => $slug,
                    'count' => count($rows),
                    'example' => sprintf('#%d %s', (int) $first['ID'], $first['post_title'] ?? ''),
                ];
            }
        }

        if (empty($violations)) {
            return [
                'name' => 'Uso shortcode disabilitati',
                'status' => 'success',
                'message' => 'Gli shortcode disabilitati non risultano nei contenuti.',
                'details' => '',
            ];
        }

        $lines = array_map(static fn($info) => sprintf(
            '[%s] trovati in %d contenuti (esempio %s)',
            $info['label'],
            $info['count'],
            $info['example']
        ), $violations);

        return [
            'name' => 'Uso shortcode disabilitati',
            'status' => 'warning',
            'message' => 'Alcuni contenuti contengono shortcode disattivati: valuta se riabilitarli o rimuoverli.',
            'details' => implode("\n", $lines),
        ];
    }
}

