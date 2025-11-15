<?php
/**
 * Link Validator - Validates links
 *
 * @package gik25microdata\InternalLinks\Utils
 */

namespace gik25microdata\InternalLinks\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Link Validator class
 */
class LinkValidator
{
    /**
     * Validate URL
     *
     * @param string $url URL
     * @return bool Is valid
     */
    public function validateUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        // Check if absolute URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }

        // Check if relative URL
        if (preg_match('/^\//', $url)) {
            return true;
        }

        return false;
    }

    /**
     * Check if URL is internal
     *
     * @param string $url URL
     * @return bool Is internal
     */
    public function isInternal($url)
    {
        $home_url = home_url();
        $parsed_url = parse_url($url);
        $parsed_home = parse_url($home_url);

        if (!$parsed_url || !$parsed_home) {
            return false;
        }

        // Relative URL is internal
        if (!isset($parsed_url['host'])) {
            return true;
        }

        // Check if same domain
        if (isset($parsed_url['host']) && isset($parsed_home['host'])) {
            return $parsed_url['host'] === $parsed_home['host'];
        }

        return false;
    }

    /**
     * Check if link is broken
     *
     * @param string $url URL
     * @return bool Is broken
     */
    public function isBroken($url)
    {
        global $wpdb;

        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT http_status FROM {$wpdb->prefix}gik25_il_http_status WHERE url = %s",
            $url
        ));

        if ($status === null) {
            return false; // Not checked yet
        }

        return !in_array(intval($status), [200, 301, 302], true);
    }
}

