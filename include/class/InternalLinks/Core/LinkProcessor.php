<?php
/**
 * Link Processor - Handles link processing in content
 *
 * @package gik25microdata\InternalLinks\Core
 */

namespace gik25microdata\InternalLinks\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Link Processor class
 */
class LinkProcessor
{
    /**
     * Process links in content
     *
     * @param string $content Post content
     * @param int $post_id Post ID
     * @return string Processed content
     */
    public function processLinks($content, $post_id)
    {
        // Extract existing links
        $links = $this->extractLinks($content);

        // Validate links
        foreach ($links as $link) {
            $this->validateLink($link);
        }

        // Apply autolinks
        $autolink_engine = new \gik25microdata\InternalLinks\Autolinks\AutolinkEngine();
        $content = $autolink_engine->applyAutolinks($content, $post_id);

        return $content;
    }

    /**
     * Extract links from content
     *
     * @param string $content Post content
     * @return array Array of link data
     */
    public function extractLinks($content)
    {
        $links = [];
        $pattern = '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $links[] = [
                    'url' => $this->sanitizeLink($match[1]),
                    'anchor' => strip_tags($match[2]),
                    'full_match' => $match[0],
                ];
            }
        }

        return $links;
    }

    /**
     * Validate a link
     *
     * @param array $link Link data
     * @return bool Is valid
     */
    public function validateLink($link)
    {
        if (empty($link['url'])) {
            return false;
        }

        // Check if URL is valid
        $url = filter_var($link['url'], FILTER_VALIDATE_URL);
        if ($url === false) {
            // Might be relative URL
            $url = $link['url'];
        }

        return !empty($url);
    }

    /**
     * Sanitize link URL
     *
     * @param string $url URL to sanitize
     * @return string Sanitized URL
     */
    public function sanitizeLink($url)
    {
        return esc_url_raw($url);
    }

    /**
     * Check if URL is internal
     *
     * @param string $url URL to check
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

        // Check if same domain
        if (isset($parsed_url['host']) && isset($parsed_home['host'])) {
            return $parsed_url['host'] === $parsed_home['host'];
        }

        // Relative URL is internal
        return !isset($parsed_url['host']);
    }
}

