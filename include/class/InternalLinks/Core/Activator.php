<?php
/**
 * Plugin Activator for Internal Links System
 *
 * @package gik25microdata\InternalLinks\Core
 */

namespace gik25microdata\InternalLinks\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Activator class
 */
class Activator
{
    /**
     * Activate the plugin
     *
     * @return void
     */
    public static function activate()
    {
        // Create database tables
        DatabaseSchema::createTables();

        // Set default options
        self::setDefaultOptions();

        // Schedule cron events if needed
        self::scheduleCronEvents();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Deactivate the plugin
     *
     * @return void
     */
    public static function deactivate()
    {
        // Clear scheduled cron events
        self::clearCronEvents();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Uninstall the plugin
     *
     * @return void
     */
    public static function uninstall()
    {
        // Optionally drop tables (commented out for safety)
        // DatabaseSchema::dropTables();

        // Delete options
        delete_option('gik25_il_db_version');
        delete_option('gik25_il_settings');
    }

    /**
     * Set default options
     *
     * @return void
     */
    private static function setDefaultOptions()
    {
        $default_options = [
            'gik25_il_enabled' => true,
            'gik25_il_default_seo_power' => 100,
            'gik25_il_penalty_per_position' => 10,
            'gik25_il_default_enable_ail_on_post' => 1,
            'gik25_il_same_url_limit' => 1,
            'gik25_il_random_prioritization' => 0,
            'gik25_il_ignore_self_ail' => 1,
            'gik25_il_click_tracking_enabled' => true,
            'gik25_il_http_status_check_enabled' => true,
            'gik25_il_http_status_check_interval' => 'daily',
        ];

        foreach ($default_options as $option_name => $default_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $default_value);
            }
        }
    }

    /**
     * Schedule cron events
     *
     * @return void
     */
    private static function scheduleCronEvents()
    {
        // Schedule HTTP status check
        if (!wp_next_scheduled('gik25_il_check_http_status')) {
            wp_schedule_event(time(), 'daily', 'gik25_il_check_http_status');
        }
        
        // Register cron handler
        add_action('gik25_il_check_http_status', function() {
            if (class_exists('\gik25microdata\InternalLinks\Monitoring\HttpStatusChecker')) {
                $checker = new \gik25microdata\InternalLinks\Monitoring\HttpStatusChecker();
                $checker->scheduleCheck();
            }
        });

        // Schedule juice calculation
        if (!wp_next_scheduled('gik25_il_calculate_juice')) {
            wp_schedule_event(time(), 'daily', 'gik25_il_calculate_juice');
        }
    }

    /**
     * Clear cron events
     *
     * @return void
     */
    private static function clearCronEvents()
    {
        wp_clear_scheduled_hook('gik25_il_check_http_status');
        wp_clear_scheduled_hook('gik25_il_calculate_juice');
    }
}

