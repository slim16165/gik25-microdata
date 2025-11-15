<?php
/**
 * Database Schema for Internal Links System
 *
 * @package gik25microdata\InternalLinks\Core
 */

namespace gik25microdata\InternalLinks\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Schema Manager
 */
class DatabaseSchema
{
    /**
     * Database version
     */
    const DB_VERSION = '1.0.0';

    /**
     * Option name for database version
     */
    const DB_VERSION_OPTION = 'gik25_il_db_version';

    /**
     * Create all tables
     *
     * @return void
     */
    public static function createTables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table: autolinks
        $sql_autolinks = "CREATE TABLE {$wpdb->prefix}gik25_il_autolinks (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            keyword VARCHAR(255) NOT NULL,
            url VARCHAR(2083) NOT NULL,
            anchor_text VARCHAR(255),
            title VARCHAR(1024),
            string_before INT DEFAULT 1,
            string_after INT DEFAULT 1,
            keyword_before VARCHAR(255),
            keyword_after VARCHAR(255),
            case_insensitive TINYINT(1) DEFAULT 0,
            use_stemming TINYINT(1) DEFAULT 0,
            language VARCHAR(10) DEFAULT 'it',
            max_links_per_post INT DEFAULT 1,
            same_url_limit INT DEFAULT 1,
            priority INT DEFAULT 0,
            post_types TEXT,
            categories TEXT,
            tags TEXT,
            term_group_id BIGINT DEFAULT 0,
            category_id BIGINT DEFAULT 0,
            open_new_tab TINYINT(1) DEFAULT 0,
            use_nofollow TINYINT(1) DEFAULT 0,
            enabled TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_keyword (keyword),
            INDEX idx_url (url(255)),
            INDEX idx_priority (priority),
            INDEX idx_enabled (enabled),
            INDEX idx_category (category_id),
            INDEX idx_term_group (term_group_id)
        ) $charset_collate;";

        // Table: links
        $sql_links = "CREATE TABLE {$wpdb->prefix}gik25_il_links (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            source_post_id BIGINT NOT NULL,
            target_post_id BIGINT NOT NULL,
            target_url VARCHAR(2083) NOT NULL,
            anchor_text VARCHAR(255),
            link_type ENUM('manual', 'autolink', 'suggestion') DEFAULT 'manual',
            autolink_id BIGINT NULL,
            suggestion_id BIGINT NULL,
            position INT,
            sentence TEXT,
            context_before TEXT,
            context_after TEXT,
            juice_score DECIMAL(10,4) DEFAULT 0,
            juice_calculated_at DATETIME,
            click_count INT DEFAULT 0,
            last_click_at DATETIME,
            http_status INT,
            http_status_checked_at DATETIME,
            is_broken TINYINT(1) DEFAULT 0,
            is_ignored TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_source (source_post_id),
            INDEX idx_target (target_post_id),
            INDEX idx_url (target_url(255)),
            INDEX idx_type (link_type),
            INDEX idx_autolink (autolink_id),
            INDEX idx_broken (is_broken),
            INDEX idx_http_status (http_status),
            UNIQUE KEY unique_link (source_post_id, target_url(255), anchor_text(100))
        ) $charset_collate;";

        // Table: suggestions
        $sql_suggestions = "CREATE TABLE {$wpdb->prefix}gik25_il_suggestions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT NOT NULL,
            suggested_post_id BIGINT NOT NULL,
            phrase TEXT NOT NULL,
            anchor_text VARCHAR(255),
            sentence TEXT,
            context_before TEXT,
            context_after TEXT,
            similarity_score DECIMAL(5,4) DEFAULT 0,
            juice_score DECIMAL(10,4) DEFAULT 0,
            combined_score DECIMAL(5,4) DEFAULT 0,
            is_applied TINYINT(1) DEFAULT 0,
            applied_at DATETIME,
            is_ignored TINYINT(1) DEFAULT 0,
            generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME,
            INDEX idx_post (post_id),
            INDEX idx_suggested (suggested_post_id),
            INDEX idx_score (combined_score),
            INDEX idx_applied (is_applied),
            INDEX idx_expires (expires_at)
        ) $charset_collate;";

        // Table: clicks
        $sql_clicks = "CREATE TABLE {$wpdb->prefix}gik25_il_clicks (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            link_id BIGINT NOT NULL,
            post_id BIGINT NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            referrer VARCHAR(2083),
            device_type VARCHAR(50),
            browser VARCHAR(100),
            clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_link (link_id),
            INDEX idx_post (post_id),
            INDEX idx_date (clicked_at),
            INDEX idx_ip (ip_address)
        ) $charset_collate;";

        // Table: http_status
        $sql_http_status = "CREATE TABLE {$wpdb->prefix}gik25_il_http_status (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            url VARCHAR(2083) NOT NULL,
            post_id BIGINT,
            http_status INT,
            status_description TEXT,
            redirect_url VARCHAR(2083),
            checked_at DATETIME,
            check_duration INT,
            error_message TEXT,
            expires_at DATETIME,
            INDEX idx_url (url(255)),
            INDEX idx_status (http_status),
            INDEX idx_post (post_id),
            INDEX idx_expires (expires_at),
            UNIQUE KEY unique_url (url(255))
        ) $charset_collate;";

        // Table: juice
        $sql_juice = "CREATE TABLE {$wpdb->prefix}gik25_il_juice (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT NOT NULL,
            url VARCHAR(2083) NOT NULL,
            seo_power INT DEFAULT 100,
            juice_absolute DECIMAL(10,4) DEFAULT 0,
            juice_relative DECIMAL(10,4) DEFAULT 0,
            inbound_links INT DEFAULT 0,
            outbound_links INT DEFAULT 0,
            total_links INT DEFAULT 0,
            calculated_at DATETIME,
            calculation_version VARCHAR(20),
            INDEX idx_post (post_id),
            INDEX idx_url (url(255)),
            INDEX idx_juice (juice_absolute)
        ) $charset_collate;";

        // Table: categories
        $sql_categories = "CREATE TABLE {$wpdb->prefix}gik25_il_categories (
            category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        ) $charset_collate;";

        // Table: term_groups
        $sql_term_groups = "CREATE TABLE {$wpdb->prefix}gik25_il_term_groups (
            term_group_id BIGINT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            filters TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        ) $charset_collate;";

        // Table: archive
        $sql_archive = "CREATE TABLE {$wpdb->prefix}gik25_il_archive (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT NOT NULL,
            post_title TEXT,
            post_permalink TEXT,
            post_type VARCHAR(20),
            post_date DATETIME,
            manual_links INT DEFAULT 0,
            autolinks INT DEFAULT 0,
            inbound_links INT DEFAULT 0,
            outbound_links INT DEFAULT 0,
            content_length INT DEFAULT 0,
            recommended_links INT DEFAULT 0,
            click_count INT DEFAULT 0,
            optimization_score DECIMAL(5,2) DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_post (post_id),
            INDEX idx_type (post_type),
            INDEX idx_optimization (optimization_score)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql_autolinks);
        dbDelta($sql_links);
        dbDelta($sql_suggestions);
        dbDelta($sql_clicks);
        dbDelta($sql_http_status);
        dbDelta($sql_juice);
        dbDelta($sql_categories);
        dbDelta($sql_term_groups);
        dbDelta($sql_archive);

        update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
    }

    /**
     * Drop all tables
     *
     * @return void
     */
    public static function dropTables()
    {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'gik25_il_autolinks',
            $wpdb->prefix . 'gik25_il_links',
            $wpdb->prefix . 'gik25_il_suggestions',
            $wpdb->prefix . 'gik25_il_clicks',
            $wpdb->prefix . 'gik25_il_http_status',
            $wpdb->prefix . 'gik25_il_juice',
            $wpdb->prefix . 'gik25_il_categories',
            $wpdb->prefix . 'gik25_il_term_groups',
            $wpdb->prefix . 'gik25_il_archive',
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }

        delete_option(self::DB_VERSION_OPTION);
    }

    /**
     * Check if tables exist
     *
     * @return bool
     */
    public static function tablesExist()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'gik25_il_autolinks';
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) === $table_name;

        return $table_exists;
    }

    /**
     * Get database version
     *
     * @return string
     */
    public static function getDbVersion()
    {
        return get_option(self::DB_VERSION_OPTION, '0.0.0');
    }
}

