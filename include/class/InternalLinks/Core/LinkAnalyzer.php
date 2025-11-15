<?php
/**
 * Link Analyzer - Analyzes links and generates statistics
 *
 * @package gik25microdata\InternalLinks\Core
 */

namespace gik25microdata\InternalLinks\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Link Analyzer class
 */
class LinkAnalyzer
{
    /**
     * Analyze links in a post
     *
     * @param int $post_id Post ID
     * @return array Analysis results
     */
    public function analyzeLinks($post_id)
    {
        $post = get_post($post_id);
        if (!$post) {
            return [];
        }

        $content = $post->post_content;
        $processor = new LinkProcessor();

        // Extract links
        $links = $processor->extractLinks($content);

        // Count link types
        $stats = [
            'total_links' => count($links),
            'internal_links' => 0,
            'external_links' => 0,
            'manual_links' => 0,
            'autolinks' => 0,
        ];

        foreach ($links as $link) {
            if ($processor->isInternal($link['url'])) {
                $stats['internal_links']++;
            } else {
                $stats['external_links']++;
            }
            // TODO: Detect autolinks vs manual links
            $stats['manual_links']++;
        }

        // Update post meta
        update_post_meta($post_id, '_gik25_il_link_stats', $stats);

        return $stats;
    }

    /**
     * Get link statistics for a post
     *
     * @param int $post_id Post ID
     * @return array Link statistics
     */
    public function getLinkStats($post_id)
    {
        $stats = get_post_meta($post_id, '_gik25_il_link_stats', true);
        if (empty($stats)) {
            $stats = $this->analyzeLinks($post_id);
        }
        return $stats;
    }

    /**
     * Get post link count
     *
     * @param int $post_id Post ID
     * @return int Link count
     */
    public function getPostLinkCount($post_id)
    {
        $stats = $this->getLinkStats($post_id);
        return isset($stats['total_links']) ? intval($stats['total_links']) : 0;
    }

    /**
     * Get inbound links count for a post
     *
     * @param int $post_id Post ID
     * @return int Inbound links count
     */
    public function getInboundLinksCount($post_id)
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gik25_il_links WHERE target_post_id = %d",
            $post_id
        ));

        return intval($count);
    }

    /**
     * Get outbound links count for a post
     *
     * @param int $post_id Post ID
     * @return int Outbound links count
     */
    public function getOutboundLinksCount($post_id)
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}gik25_il_links WHERE source_post_id = %d",
            $post_id
        ));

        return intval($count);
    }
}

