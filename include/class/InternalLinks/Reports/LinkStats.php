<?php
/**
 * Link Stats - Generates link statistics
 *
 * @package gik25microdata\InternalLinks\Reports
 */

namespace gik25microdata\InternalLinks\Reports;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Link Stats class
 */
class LinkStats
{
    /**
     * Get post statistics
     *
     * @param int $post_id Post ID
     * @return array Statistics
     */
    public function getPostStats($post_id)
    {
        global $wpdb;

        $stats = [
            'total_links' => 0,
            'internal_links' => 0,
            'external_links' => 0,
            'autolinks' => 0,
            'manual_links' => 0,
            'inbound_links' => 0,
            'outbound_links' => 0,
        ];

        // Get links from registry
        $links = $wpdb->get_results($wpdb->prepare(
            "SELECT link_type, COUNT(*) as count 
             FROM {$wpdb->prefix}gik25_il_links 
             WHERE source_post_id = %d 
             GROUP BY link_type",
            $post_id
        ), ARRAY_A);

        foreach ($links as $link) {
            $stats['total_links'] += intval($link['count']);
            if ($link['link_type'] === 'autolink') {
                $stats['autolinks'] += intval($link['count']);
            } else {
                $stats['manual_links'] += intval($link['count']);
            }
        }

        // Get inbound links
        $stats['inbound_links'] = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gik25_il_links WHERE target_post_id = %d",
            $post_id
        )));

        // Get outbound links
        $stats['outbound_links'] = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gik25_il_links WHERE source_post_id = %d",
            $post_id
        )));

        return $stats;
    }

    /**
     * Get site statistics
     *
     * @return array Statistics
     */
    public function getSiteStats()
    {
        global $wpdb;

        $stats = [
            'total_links' => 0,
            'total_autolinks' => 0,
            'total_posts' => 0,
            'total_autolink_rules' => 0,
        ];

        $stats['total_links'] = intval($wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gik25_il_links"
        ));

        $stats['total_autolinks'] = intval($wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gik25_il_links WHERE link_type = 'autolink'"
        ));

        $stats['total_posts'] = intval($wpdb->get_var(
            "SELECT COUNT(DISTINCT source_post_id) FROM {$wpdb->prefix}gik25_il_links"
        ));

        $stats['total_autolink_rules'] = intval($wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gik25_il_autolinks WHERE enabled = 1"
        ));

        return $stats;
    }

    /**
     * Get link counts
     *
     * @param int $post_id Post ID
     * @return array Link counts
     */
    public function getLinkCounts($post_id)
    {
        return $this->getPostStats($post_id);
    }
}

