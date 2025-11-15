<?php
/**
 * Click Tracker - Tracks clicks on links
 *
 * @package gik25microdata\InternalLinks\Reports
 */

namespace gik25microdata\InternalLinks\Reports;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Click Tracker class
 */
class ClickTracker
{
    /**
     * Track click on a link
     *
     * @param int $link_id Link ID
     * @param array $data Additional data
     * @return bool Success
     */
    public function trackClick($link_id, $data = [])
    {
        if (!get_option('gik25_il_click_tracking_enabled', true)) {
            return false;
        }

        global $wpdb;

        $post_id = isset($data['post_id']) ? intval($data['post_id']) : 0;
        $ip_address = isset($data['ip_address']) ? sanitize_text_field($data['ip_address']) : '';
        $user_agent = isset($data['user_agent']) ? sanitize_text_field($data['user_agent']) : '';
        $referrer = isset($data['referrer']) ? esc_url_raw($data['referrer']) : '';

        // Get device type and browser from user agent
        $device_type = $this->detectDeviceType($user_agent);
        $browser = $this->detectBrowser($user_agent);

        $result = $wpdb->insert(
            $wpdb->prefix . 'gik25_il_clicks',
            [
                'link_id' => $link_id,
                'post_id' => $post_id,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'referrer' => $referrer,
                'device_type' => $device_type,
                'browser' => $browser,
                'clicked_at' => current_time('mysql'),
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        // Update link click count
        if ($result) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}gik25_il_links 
                 SET click_count = click_count + 1, 
                     last_click_at = NOW() 
                 WHERE id = %d",
                $link_id
            ));
        }

        return $result !== false;
    }

    /**
     * Get click statistics
     *
     * @param int $link_id Link ID (optional)
     * @param int $post_id Post ID (optional)
     * @return array Statistics
     */
    public function getClickStats($link_id = 0, $post_id = 0)
    {
        global $wpdb;

        $where = ['1=1'];
        $params = [];

        if ($link_id > 0) {
            $where[] = "link_id = %d";
            $params[] = $link_id;
        }

        if ($post_id > 0) {
            $where[] = "post_id = %d";
            $params[] = $post_id;
        }

        $where_clause = implode(' AND ', $where);

        $query = "SELECT 
                    COUNT(*) as total_clicks,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(DISTINCT DATE(clicked_at)) as unique_days
                  FROM {$wpdb->prefix}gik25_il_clicks
                  WHERE {$where_clause}";

        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_row($query, ARRAY_A);
    }

    /**
     * Handle AJAX click
     *
     * @return void
     */
    public function handleAjaxClick()
    {
        check_ajax_referer('gik25_il_track_click', 'nonce');

        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if ($link_id > 0 && $post_id > 0) {
            $data = [
                'post_id' => $post_id,
                'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '',
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
                'referrer' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
            ];

            $this->trackClick($link_id, $data);
        }

        wp_send_json_success();
    }

    /**
     * Detect device type from user agent
     *
     * @param string $user_agent User agent
     * @return string Device type
     */
    private function detectDeviceType($user_agent)
    {
        if (preg_match('/mobile|android|iphone|ipad/i', $user_agent)) {
            return 'mobile';
        }
        if (preg_match('/tablet|ipad/i', $user_agent)) {
            return 'tablet';
        }
        return 'desktop';
    }

    /**
     * Detect browser from user agent
     *
     * @param string $user_agent User agent
     * @return string Browser
     */
    private function detectBrowser($user_agent)
    {
        if (preg_match('/chrome/i', $user_agent)) {
            return 'Chrome';
        }
        if (preg_match('/firefox/i', $user_agent)) {
            return 'Firefox';
        }
        if (preg_match('/safari/i', $user_agent)) {
            return 'Safari';
        }
        if (preg_match('/edge/i', $user_agent)) {
            return 'Edge';
        }
        return 'Unknown';
    }
}

