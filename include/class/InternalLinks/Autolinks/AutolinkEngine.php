<?php
/**
 * Autolink Engine - Main engine for applying autolinks
 *
 * @package gik25microdata\InternalLinks\Autolinks
 */

namespace gik25microdata\InternalLinks\Autolinks;

use gik25microdata\InternalLinks\Core\LinkProcessor;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autolink Engine class
 */
class AutolinkEngine
{
    /**
     * Protected blocks array
     *
     * @var array
     */
    private $protected_blocks = [];

    /**
     * Protected block ID counter
     *
     * @var int
     */
    private $pb_id = 0;

    /**
     * Applied autolinks counter
     *
     * @var int
     */
    private $applied_count = 0;

    /**
     * Apply autolinks to content
     *
     * @param string $content Post content
     * @param int $post_id Post ID
     * @param array $rules Autolink rules (optional, will load if not provided)
     * @return string Processed content
     */
    public function applyAutolinks($content, $post_id, $rules = null)
    {
        // Check if autolinks enabled for this post
        if (!$this->isAutolinksEnabled($post_id)) {
            return $content;
        }

        // Load rules if not provided
        if ($rules === null) {
            $rules = $this->loadRules($post_id);
        }

        // Apply protected blocks
        $content = $this->applyProtectedBlocks($content);

        // Get limits
        $max_links_per_post = $this->getMaxLinksPerPost($post_id, $content);
        $same_url_limit = intval(get_option('gik25_il_same_url_limit', 1), 10);

        // Track applied URLs
        $applied_urls = [];

        // Process each rule
        $keyword_matcher = new KeywordMatcher();
        $context_matcher = new ContextMatcher();
        $processor = new LinkProcessor();

        foreach ($rules as $rule) {
            // Check compliance
            if (!$this->checkCompliance($rule, $post_id)) {
                continue;
            }

            // Check same URL limit
            if (isset($applied_urls[$rule->url]) && $applied_urls[$rule->url] >= $rule->same_url_limit) {
                continue;
            }

            // Check max links per post
            if ($this->applied_count >= $max_links_per_post) {
                break;
            }

            // Match keyword
            $options = [
                'use_stemming' => $rule->use_stemming,
                'case_insensitive' => $rule->case_insensitive,
                'language' => $rule->language,
            ];

            $matches = $keyword_matcher->matchKeyword($rule->keyword, $content, $options);

            // Filter by context
            $matches = $context_matcher->filterByContext($matches, $rule, $content);

            // Limit matches per keyword
            $matches = array_slice($matches, 0, $rule->max_links_per_post);

            // Apply links
            foreach ($matches as $match) {
                if ($this->applied_count >= $max_links_per_post) {
                    break 2;
                }

                if (isset($applied_urls[$rule->url]) && $applied_urls[$rule->url] >= $rule->same_url_limit) {
                    continue;
                }

                $content = $this->insertLink($content, $rule, $match);
                $this->applied_count++;
                $applied_urls[$rule->url] = isset($applied_urls[$rule->url]) ? $applied_urls[$rule->url] + 1 : 1;
            }
        }

        // Remove protected blocks
        $content = $this->removeProtectedBlocks($content);

        return $content;
    }

    /**
     * Load autolink rules from database
     *
     * @param int $post_id Post ID
     * @return array Array of AutolinkRule objects
     */
    public function loadRules($post_id = 0)
    {
        global $wpdb;

        $rules = [];
        $table_name = $wpdb->prefix . 'gik25_il_autolinks';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return $rules;
        }

        $results = $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE enabled = 1 ORDER BY priority DESC",
            ARRAY_A
        );

        foreach ($results as $row) {
            $rules[] = AutolinkRule::fromArray($row);
        }

        // Apply random prioritization if enabled
        if (intval(get_option('gik25_il_random_prioritization', 0), 10) === 1) {
            shuffle($rules);
        }

        return $rules;
    }

    /**
     * Check if rule is compliant with post
     *
     * @param AutolinkRule $rule Rule
     * @param int $post_id Post ID
     * @return bool Is compliant
     */
    public function checkCompliance($rule, $post_id)
    {
        // Check self AIL prevention
        if (intval(get_option('gik25_il_ignore_self_ail', 1), 10) === 1) {
            $post_permalink = get_permalink($post_id);
            $home_url_length = abs(strlen(home_url()));
            if (substr($post_permalink, $home_url_length) === $rule->url) {
                return false;
            }
        }

        // Check post type
        $post_type = get_post_type($post_id);
        if (!empty($rule->post_types) && !in_array($post_type, $rule->post_types, true)) {
            return false;
        }

        // Check categories and tags (simplified - will be enhanced)
        if (!empty($rule->categories) || !empty($rule->tags)) {
            // TODO: Implement category/tag checking
        }

        // Check term group (simplified - will be enhanced)
        if ($rule->term_group_id > 0) {
            // TODO: Implement term group checking
        }

        return true;
    }

    /**
     * Insert link into content
     *
     * @param string $content Content
     * @param AutolinkRule $rule Rule
     * @param array $match Match data
     * @return string Content with link inserted
     */
    private function insertLink($content, $rule, $match)
    {
        $anchor = !empty($rule->anchor_text) ? $rule->anchor_text : $match['text'];
        $title = !empty($rule->title) ? ' title="' . esc_attr($rule->title) . '"' : '';
        $target = $rule->open_new_tab ? ' target="_blank" rel="noopener"' : '';
        $nofollow = $rule->use_nofollow ? ' rel="nofollow"' : '';

        $link = '<a href="' . esc_url($rule->url) . '"' . $title . $target . $nofollow . '>' . esc_html($anchor) . '</a>';

        // Replace match with link
        $before = substr($content, 0, $match['position']);
        $after = substr($content, $match['position'] + $match['length']);

        return $before . $link . $after;
    }

    /**
     * Check if autolinks enabled for post
     *
     * @param int $post_id Post ID
     * @return bool Enabled
     */
    private function isAutolinksEnabled($post_id)
    {
        $enable_ail = get_post_meta($post_id, '_gik25_il_enable_ail', true);
        if (strlen(trim($enable_ail)) === 0) {
            $enable_ail = get_option('gik25_il_default_enable_ail_on_post', 1);
        }
        return intval($enable_ail, 10) === 1;
    }

    /**
     * Get max links per post
     *
     * @param int $post_id Post ID
     * @param string $content Content
     * @return int Max links
     */
    private function getMaxLinksPerPost($post_id, $content)
    {
        $max = get_post_meta($post_id, '_gik25_il_max_autolinks', true);
        if (empty($max)) {
            $max = get_option('gik25_il_max_autolinks_per_post', 10);
        }
        return intval($max, 10);
    }

    /**
     * Apply protected blocks (simplified version)
     *
     * @param string $content Content
     * @return string Content with protected blocks
     */
    private function applyProtectedBlocks($content)
    {
        // Protect HTML tags
        $this->pb_id = 0;
        $this->protected_blocks = [];

        // Protect existing links
        $content = preg_replace_callback(
            '/<a\s+[^>]*>.*?<\/a>/is',
            [$this, 'protectBlock'],
            $content
        );

        // Protect HTML comments
        $content = preg_replace_callback(
            '/<!--.*?-->/s',
            [$this, 'protectBlock'],
            $content
        );

        return $content;
    }

    /**
     * Remove protected blocks
     *
     * @param string $content Content
     * @return string Content without protected blocks
     */
    private function removeProtectedBlocks($content)
    {
        foreach ($this->protected_blocks as $id => $block) {
            $content = str_replace("[pr]{$id}[/pr]", $block, $content);
        }
        return $content;
    }

    /**
     * Protect block callback
     *
     * @param array $matches Matches
     * @return string Protected placeholder
     */
    private function protectBlock($matches)
    {
        $this->pb_id++;
        $this->protected_blocks[$this->pb_id] = $matches[0];
        return "[pr]{$this->pb_id}[/pr]";
    }

    /**
     * Validate rule
     *
     * @param AutolinkRule $rule Rule
     * @return bool Is valid
     */
    public function validateRule($rule)
    {
        if (empty($rule->keyword)) {
            return false;
        }
        if (empty($rule->url)) {
            return false;
        }
        if (!filter_var($rule->url, FILTER_VALIDATE_URL) && !preg_match('/^\//', $rule->url)) {
            return false;
        }
        return true;
    }
}

