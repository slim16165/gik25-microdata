<?php
/**
 * Juice Calculator - Calculates link juice
 *
 * @package gik25microdata\InternalLinks\Reports
 */

namespace gik25microdata\InternalLinks\Reports;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Juice Calculator class
 */
class JuiceCalculator
{
    /**
     * Calculate juice for a link
     *
     * @param int $post_id Post ID
     * @param int $link_position Link position in content
     * @return array Juice data
     */
    public function calculateJuice($post_id, $link_position)
    {
        // Get SEO power
        $seo_power = get_post_meta($post_id, '_gik25_il_seo_power', true);
        if (strlen(trim($seo_power)) === 0) {
            $seo_power = intval(get_option('gik25_il_default_seo_power', 100), 10);
        }

        // Get total links in post
        $total_links = $this->getNumberOfLinks($post_id);

        // Calculate juice per link
        $juice_per_link = $total_links > 0 ? $seo_power / $total_links : $seo_power;

        // Calculate position index
        $links_before = $this->countLinksBeforePosition($post_id, $link_position);

        // Apply position penalty
        $penalty_percentage = intval(get_option('gik25_il_penalty_per_position', 10), 10);
        $penalty = ($juice_per_link / 100 * $penalty_percentage) * $links_before;

        // Final juice
        $final_juice = max(0, $juice_per_link - $penalty);

        // Calculate relative juice
        $max_juice = $this->getMaxJuice();
        $relative_juice = $max_juice > 0 ? ($final_juice / $max_juice) * 100 : 0;

        return [
            'absolute' => $final_juice,
            'relative' => $relative_juice,
        ];
    }

    /**
     * Calculate relative juice
     *
     * @param float $absolute_juice Absolute juice
     * @return float Relative juice
     */
    public function calculateRelativeJuice($absolute_juice)
    {
        $max_juice = $this->getMaxJuice();
        return $max_juice > 0 ? ($absolute_juice / $max_juice) * 100 : 0;
    }

    /**
     * Get max juice in site
     *
     * @return float Max juice
     */
    public function getMaxJuice()
    {
        global $wpdb;

        $max = $wpdb->get_var(
            "SELECT MAX(juice_absolute) FROM {$wpdb->prefix}gik25_il_juice"
        );

        return $max ? floatval($max) : 100.0;
    }

    /**
     * Get number of links in post
     *
     * @param int $post_id Post ID
     * @return int Number of links
     */
    private function getNumberOfLinks($post_id)
    {
        $post = get_post($post_id);
        if (!$post) {
            return 0;
        }

        $content = $post->post_content;
        
        // Remove HTML comments
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        
        // Remove script tags
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);

        // Count links
        $count = preg_match_all(
            '/<a\s+[^>]*href\s*=\s*([\'"]?)([^\'">\s]+)\1[^>]*>.*?<\/a>/is',
            $content,
            $matches
        );

        return $count ? intval($count) : 0;
    }

    /**
     * Count links before position
     *
     * @param int $post_id Post ID
     * @param int $link_position Link position
     * @return int Number of links before
     */
    private function countLinksBeforePosition($post_id, $link_position)
    {
        $post = get_post($post_id);
        if (!$post) {
            return 0;
        }

        $content = substr($post->post_content, 0, $link_position);
        
        // Remove HTML comments
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        
        // Remove script tags
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);

        // Count links
        $count = preg_match_all(
            '/<a\s+[^>]*href\s*=\s*([\'"]?)([^\'">\s]+)\1[^>]*>.*?<\/a>/is',
            $content,
            $matches
        );

        return $count ? intval($count) : 0;
    }
}

