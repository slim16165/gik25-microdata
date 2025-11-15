<?php
/**
 * WPIL Migration - Migrates data from Link Whisper Premium
 *
 * @package gik25microdata\InternalLinks\Migration
 */

namespace gik25microdata\InternalLinks\Migration;

use gik25microdata\InternalLinks\Autolinks\AutolinkRule;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WPIL Migration class
 */
class WpilMigration
{
    /**
     * Migrate all WPIL data
     *
     * @return array Migration results
     */
    public function migrateAll()
    {
        $results = [
            'keywords' => $this->migrateKeywords(),
            'links' => $this->migrateLinks(),
            'clicks' => $this->migrateClicks(),
            'errors' => $this->migrateErrors(),
        ];

        return $results;
    }

    /**
     * Migrate keywords
     *
     * @return array Results
     */
    public function migrateKeywords()
    {
        global $wpdb;

        $wpil_table = $wpdb->prefix . 'wpil_keywords';
        $new_table = $wpdb->prefix . 'gik25_il_autolinks';

        if ($wpdb->get_var("SHOW TABLES LIKE '$wpil_table'") !== $wpil_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $keywords = $wpdb->get_results("SELECT * FROM {$wpil_table}", ARRAY_A);
        $migrated = 0;

        foreach ($keywords as $keyword) {
            $rule = new AutolinkRule();
            $rule->name = 'Migrated from WPIL';
            $rule->keyword = $keyword['keyword_text'] ?? '';
            $rule->url = $keyword['link'] ?? '';
            $rule->use_stemming = true; // WPIL uses stemming
            $rule->language = 'it'; // Default, can be enhanced
            $rule->max_links_per_post = intval($keyword['link_once'] ?? 0) ? 1 : 10;
            $rule->same_url_limit = intval($keyword['add_same_link'] ?? 0) ? 0 : 1;
            $rule->enabled = true;

            $data = $rule->toArray();
            unset($data['id']);

            $result = $wpdb->insert($new_table, $data);
            if ($result) {
                $migrated++;
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($keywords),
        ];
    }

    /**
     * Migrate links
     *
     * @return array Results
     */
    public function migrateLinks()
    {
        global $wpdb;

        $wpil_table = $wpdb->prefix . 'wpil_links';
        $new_table = $wpdb->prefix . 'gik25_il_links';

        if ($wpdb->get_var("SHOW TABLES LIKE '$wpil_table'") !== $wpil_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $links = $wpdb->get_results("SELECT * FROM {$wpil_table} WHERE internal = 1", ARRAY_A);
        $migrated = 0;

        foreach ($links as $link) {
            // Get target post ID from URL
            $target_post_id = url_to_postid($link['url'] ?? '');

            if ($target_post_id > 0) {
                $wpdb->insert($new_table, [
                    'source_post_id' => intval($link['post_id'] ?? 0),
                    'target_post_id' => $target_post_id,
                    'target_url' => $link['url'] ?? '',
                    'anchor_text' => $link['anchor'] ?? '',
                    'link_type' => 'manual', // WPIL doesn't distinguish autolinks clearly
                    'created_at' => current_time('mysql'),
                ]);

                if ($wpdb->last_error === '') {
                    $migrated++;
                }
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($links),
        ];
    }

    /**
     * Migrate clicks
     *
     * @return array Results
     */
    public function migrateClicks()
    {
        global $wpdb;

        $wpil_table = $wpdb->prefix . 'wpil_clicks';
        $new_table = $wpdb->prefix . 'gik25_il_clicks';

        if ($wpdb->get_var("SHOW TABLES LIKE '$wpil_table'") !== $wpil_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $clicks = $wpdb->get_results("SELECT * FROM {$wpil_table}", ARRAY_A);
        $migrated = 0;

        foreach ($clicks as $click) {
            // Try to find link_id
            $link_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}gik25_il_links WHERE target_url = %s AND source_post_id = %d LIMIT 1",
                $click['link_url'] ?? '',
                $click['post_id'] ?? 0
            ));

            if ($link_id) {
                $wpdb->insert($new_table, [
                    'link_id' => $link_id,
                    'post_id' => intval($click['post_id'] ?? 0),
                    'clicked_at' => $click['click_date'] ?? current_time('mysql'),
                ]);

                if ($wpdb->last_error === '') {
                    $migrated++;
                }
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($clicks),
        ];
    }

    /**
     * Migrate errors
     *
     * @return array Results
     */
    public function migrateErrors()
    {
        global $wpdb;

        $wpil_table = $wpdb->prefix . 'wpil_errors';
        $new_table = $wpdb->prefix . 'gik25_il_http_status';

        if ($wpdb->get_var("SHOW TABLES LIKE '$wpil_table'") !== $wpil_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $errors = $wpdb->get_results("SELECT * FROM {$wpil_table}", ARRAY_A);
        $migrated = 0;

        foreach ($errors as $error) {
            $wpdb->replace($new_table, [
                'url' => $error['url'] ?? '',
                'post_id' => intval($error['post_id'] ?? 0),
                'http_status' => 404, // WPIL errors are typically 404
                'status_description' => 'Not Found',
                'checked_at' => current_time('mysql'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            ]);

            if ($wpdb->last_error === '') {
                $migrated++;
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($errors),
        ];
    }
}

