<?php
/**
 * HTTP Status Checker - Checks HTTP status of links
 *
 * @package gik25microdata\InternalLinks\Monitoring
 */

namespace gik25microdata\InternalLinks\Monitoring;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * HTTP Status Checker class
 */
class HttpStatusChecker
{
    /**
     * Check HTTP status of a URL
     *
     * @param string $url URL to check
     * @return array Status data
     */
    public function checkStatus($url)
    {
        global $wpdb;

        // Check cache first
        $cached = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}gik25_il_http_status 
             WHERE url = %s AND expires_at > NOW()",
            $url
        ), ARRAY_A);

        if ($cached) {
            return [
                'status' => intval($cached['http_status']),
                'checked_at' => $cached['checked_at'],
                'cached' => true,
            ];
        }

        // Perform check
        $start_time = microtime(true);
        $response = wp_remote_head($url, [
            'timeout' => 10,
            'redirection' => 5,
        ]);

        $duration = intval((microtime(true) - $start_time) * 1000);

        $http_status = 0;
        $status_description = '';
        $redirect_url = '';
        $error_message = '';

        if (is_wp_error($response)) {
            $http_status = 0;
            $error_message = $response->get_error_message();
        } else {
            $http_status = wp_remote_retrieve_response_code($response);
            $status_description = $this->getStatusDescription($http_status);
            $redirect_url = wp_remote_retrieve_header($response, 'location');
        }

        // Cache result (24 hours)
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $wpdb->replace(
            $wpdb->prefix . 'gik25_il_http_status',
            [
                'url' => $url,
                'http_status' => $http_status,
                'status_description' => $status_description,
                'redirect_url' => $redirect_url,
                'checked_at' => current_time('mysql'),
                'check_duration' => $duration,
                'error_message' => $error_message,
                'expires_at' => $expires_at,
            ],
            ['%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s']
        );

        return [
            'status' => $http_status,
            'status_description' => $status_description,
            'redirect_url' => $redirect_url,
            'checked_at' => current_time('mysql'),
            'check_duration' => $duration,
            'error_message' => $error_message,
            'cached' => false,
        ];
    }

    /**
     * Batch check HTTP status
     *
     * @param array $urls Array of URLs
     * @return array Results
     */
    public function batchCheck($urls)
    {
        $results = [];
        foreach ($urls as $url) {
            $results[$url] = $this->checkStatus($url);
        }
        return $results;
    }

    /**
     * Schedule check (cron job)
     *
     * @return void
     */
    public function scheduleCheck()
    {
        global $wpdb;

        // Get URLs to check (limit to 100 per run)
        $urls = $wpdb->get_col(
            "SELECT DISTINCT target_url 
             FROM {$wpdb->prefix}gik25_il_links 
             WHERE (http_status_checked_at IS NULL OR http_status_checked_at < DATE_SUB(NOW(), INTERVAL 7 DAY))
             LIMIT 100"
        );

        foreach ($urls as $url) {
            $this->checkStatus($url);
        }
    }

    /**
     * Get status description
     *
     * @param int $status_code HTTP status code
     * @return string Description
     */
    private function getStatusDescription($status_code)
    {
        $descriptions = [
            200 => 'OK',
            301 => 'Moved Permanently',
            302 => 'Found',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        ];

        return isset($descriptions[$status_code]) ? $descriptions[$status_code] : 'Unknown';
    }
}

