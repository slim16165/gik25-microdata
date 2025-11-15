<?php
/**
 * Error Detector - Detects broken links
 *
 * @package gik25microdata\InternalLinks\Monitoring
 */

namespace gik25microdata\InternalLinks\Monitoring;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Error Detector class
 */
class ErrorDetector
{
    /**
     * Detect errors in links
     *
     * @return array Errors found
     */
    public function detectErrors()
    {
        global $wpdb;

        $errors = [];

        // Find links with HTTP status errors
        $broken_links = $wpdb->get_results(
            "SELECT l.*, p.post_title 
             FROM {$wpdb->prefix}gik25_il_links l
             LEFT JOIN {$wpdb->prefix}posts p ON l.source_post_id = p.ID
             WHERE l.http_status IS NOT NULL 
             AND l.http_status NOT IN (200, 301, 302)
             AND l.is_broken = 0",
            ARRAY_A
        );

        foreach ($broken_links as $link) {
            $this->markBroken($link['id']);
            $errors[] = $link;
        }

        return $errors;
    }

    /**
     * Mark link as broken
     *
     * @param int $link_id Link ID
     * @return bool Success
     */
    public function markBroken($link_id)
    {
        global $wpdb;

        return $wpdb->update(
            $wpdb->prefix . 'gik25_il_links',
            ['is_broken' => 1],
            ['id' => $link_id],
            ['%d'],
            ['%d']
        ) !== false;
    }

    /**
     * Get error report
     *
     * @param array $filters Filters
     * @return array Error report
     */
    public function getErrorReport($filters = [])
    {
        global $wpdb;

        $where = ['is_broken = 1'];
        $params = [];

        if (!empty($filters['post_type'])) {
            $where[] = "p.post_type = %s";
            $params[] = $filters['post_type'];
        }

        $where_clause = implode(' AND ', $where);

        $query = "SELECT 
                    l.*,
                    p.post_title as source_title,
                    p.post_type
                  FROM {$wpdb->prefix}gik25_il_links l
                  LEFT JOIN {$wpdb->prefix}posts p ON l.source_post_id = p.ID
                  WHERE {$where_clause}
                  ORDER BY l.updated_at DESC";

        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_results($query, ARRAY_A);
    }
}

