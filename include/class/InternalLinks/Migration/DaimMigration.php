<?php
/**
 * DAIM Migration - Migrates data from Interlinks Manager
 *
 * @package gik25microdata\InternalLinks\Migration
 */

namespace gik25microdata\InternalLinks\Migration;

use gik25microdata\InternalLinks\Autolinks\AutolinkRule;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DAIM Migration class
 */
class DaimMigration
{
    /**
     * Migrate all DAIM data
     *
     * @return array Migration results
     */
    public function migrateAll()
    {
        $results = [
            'autolinks' => $this->migrateAutolinks(),
            'juice' => $this->migrateJuice(),
            'hits' => $this->migrateHits(),
            'http_status' => $this->migrateHttpStatus(),
            'archive' => $this->migrateArchive(),
        ];

        return $results;
    }

    /**
     * Migrate autolinks
     *
     * @return array Results
     */
    public function migrateAutolinks()
    {
        global $wpdb;

        $daim_table = $wpdb->prefix . 'daim_autolinks';
        $new_table = $wpdb->prefix . 'gik25_il_autolinks';

        // Check if source table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$daim_table'") !== $daim_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $autolinks = $wpdb->get_results("SELECT * FROM {$daim_table}", ARRAY_A);
        $migrated = 0;

        foreach ($autolinks as $autolink) {
            $rule = new AutolinkRule();
            $rule->name = $autolink['name'] ?? '';
            $rule->keyword = $autolink['keyword'] ?? '';
            $rule->url = $autolink['url'] ?? '';
            $rule->title = $autolink['title'] ?? '';
            $rule->string_before = intval($autolink['string_before'] ?? 1);
            $rule->string_after = intval($autolink['string_after'] ?? 1);
            $rule->keyword_before = $autolink['keyword_before'] ?? '';
            $rule->keyword_after = $autolink['keyword_after'] ?? '';
            $rule->case_insensitive = (bool) ($autolink['case_insensitive_search'] ?? 0);
            $rule->max_links_per_post = intval($autolink['max_number_autolinks'] ?? 1);
            $rule->priority = intval($autolink['priority'] ?? 0);
            $rule->post_types = !empty($autolink['activate_post_types']) ? maybe_unserialize($autolink['activate_post_types']) : [];
            $rule->categories = !empty($autolink['categories']) ? maybe_unserialize($autolink['categories']) : [];
            $rule->tags = !empty($autolink['tags']) ? maybe_unserialize($autolink['tags']) : [];
            $rule->term_group_id = intval($autolink['term_group_id'] ?? 0);
            $rule->category_id = intval($autolink['category_id'] ?? 0);
            $rule->open_new_tab = (bool) ($autolink['open_new_tab'] ?? 0);
            $rule->use_nofollow = (bool) ($autolink['use_nofollow'] ?? 0);
            $rule->enabled = true;

            $data = $rule->toArray();
            unset($data['id']); // Don't include ID for insert

            $result = $wpdb->insert($new_table, $data);
            if ($result) {
                $migrated++;
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($autolinks),
        ];
    }

    /**
     * Migrate juice data
     *
     * @return array Results
     */
    public function migrateJuice()
    {
        global $wpdb;

        $daim_table = $wpdb->prefix . 'daim_juice';
        $new_table = $wpdb->prefix . 'gik25_il_juice';

        if ($wpdb->get_var("SHOW TABLES LIKE '$daim_table'") !== $daim_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $juice_data = $wpdb->get_results("SELECT * FROM {$daim_table}", ARRAY_A);
        $migrated = 0;

        foreach ($juice_data as $juice) {
            // Get post ID from URL
            $post_id = url_to_postid($juice['url']);

            if ($post_id > 0) {
                $wpdb->insert($new_table, [
                    'post_id' => $post_id,
                    'url' => $juice['url'],
                    'juice_absolute' => floatval($juice['juice'] ?? 0),
                    'juice_relative' => floatval($juice['juice_relative'] ?? 0),
                    'inbound_links' => intval($juice['iil'] ?? 0),
                    'calculated_at' => current_time('mysql'),
                ]);

                if ($wpdb->last_error === '') {
                    $migrated++;
                }
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($juice_data),
        ];
    }

    /**
     * Migrate hits
     *
     * @return array Results
     */
    public function migrateHits()
    {
        global $wpdb;

        $daim_table = $wpdb->prefix . 'daim_hits';
        $new_table = $wpdb->prefix . 'gik25_il_clicks';

        if ($wpdb->get_var("SHOW TABLES LIKE '$daim_table'") !== $daim_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $hits = $wpdb->get_results("SELECT * FROM {$daim_table}", ARRAY_A);
        $migrated = 0;

        foreach ($hits as $hit) {
            // Try to find link_id from target_url
            $link_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}gik25_il_links WHERE target_url = %s AND source_post_id = %d LIMIT 1",
                $hit['target_url'],
                $hit['source_post_id']
            ));

            if ($link_id) {
                $wpdb->insert($new_table, [
                    'link_id' => $link_id,
                    'post_id' => intval($hit['source_post_id']),
                    'clicked_at' => $hit['date'] ?? current_time('mysql'),
                ]);

                if ($wpdb->last_error === '') {
                    $migrated++;
                }
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($hits),
        ];
    }

    /**
     * Migrate HTTP status
     *
     * @return array Results
     */
    public function migrateHttpStatus()
    {
        global $wpdb;

        $daim_table = $wpdb->prefix . 'daim_http_status';
        $new_table = $wpdb->prefix . 'gik25_il_http_status';

        if ($wpdb->get_var("SHOW TABLES LIKE '$daim_table'") !== $daim_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $status_data = $wpdb->get_results("SELECT * FROM {$daim_table}", ARRAY_A);
        $migrated = 0;

        foreach ($status_data as $status) {
            $wpdb->replace($new_table, [
                'url' => $status['url'],
                'post_id' => intval($status['post_id'] ?? 0),
                'http_status' => intval($status['code'] ?? 0),
                'status_description' => $status['code_description'] ?? '',
                'checked_at' => $status['last_check_date'] ?? current_time('mysql'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            ]);

            if ($wpdb->last_error === '') {
                $migrated++;
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($status_data),
        ];
    }

    /**
     * Migrate archive
     *
     * @return array Results
     */
    public function migrateArchive()
    {
        global $wpdb;

        $daim_table = $wpdb->prefix . 'daim_archive';
        $new_table = $wpdb->prefix . 'gik25_il_archive';

        if ($wpdb->get_var("SHOW TABLES LIKE '$daim_table'") !== $daim_table) {
            return ['success' => false, 'message' => 'Source table not found'];
        }

        $archive_data = $wpdb->get_results("SELECT * FROM {$daim_table}", ARRAY_A);
        $migrated = 0;

        foreach ($archive_data as $archive) {
            $wpdb->replace($new_table, [
                'post_id' => intval($archive['post_id']),
                'post_title' => $archive['post_title'] ?? '',
                'post_permalink' => $archive['post_permalink'] ?? '',
                'post_type' => $archive['post_type'] ?? 'post',
                'post_date' => $archive['post_date'] ?? null,
                'manual_links' => intval($archive['manual_interlinks'] ?? 0),
                'autolinks' => intval($archive['auto_interlinks'] ?? 0),
                'inbound_links' => intval($archive['iil'] ?? 0),
                'content_length' => intval($archive['content_length'] ?? 0),
                'recommended_links' => intval($archive['recommended_interlinks'] ?? 0),
                'click_count' => intval($archive['num_il_clicks'] ?? 0),
                'optimization_score' => floatval($archive['optimization'] ?? 0),
            ]);

            if ($wpdb->last_error === '') {
                $migrated++;
            }
        }

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($archive_data),
        ];
    }
}

