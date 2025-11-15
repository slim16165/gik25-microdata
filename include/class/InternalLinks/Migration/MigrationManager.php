<?php
/**
 * Migration Manager - Manages data migration from DAIM and WPIL
 *
 * @package gik25microdata\InternalLinks\Migration
 */

namespace gik25microdata\InternalLinks\Migration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Migration Manager class
 */
class MigrationManager
{
    /**
     * Detect if plugins are installed
     *
     * @return array Detected plugins
     */
    public function detectPlugins()
    {
        $plugins = [
            'daim' => false,
            'wpil' => false,
        ];

        // Check for DAIM
        global $wpdb;
        $daim_table = $wpdb->prefix . 'daim_autolinks';
        if ($wpdb->get_var("SHOW TABLES LIKE '$daim_table'") === $daim_table) {
            $plugins['daim'] = true;
        }

        // Check for WPIL
        $wpil_table = $wpdb->prefix . 'wpil_keywords';
        if ($wpdb->get_var("SHOW TABLES LIKE '$wpil_table'") === $wpil_table) {
            $plugins['wpil'] = true;
        }

        return $plugins;
    }

    /**
     * Run migration
     *
     * @param array $plugins Plugins to migrate from
     * @return array Migration results
     */
    public function runMigration($plugins)
    {
        $results = [
            'daim' => [],
            'wpil' => [],
        ];

        if (isset($plugins['daim']) && $plugins['daim']) {
            $daim_migration = new DaimMigration();
            $results['daim'] = $daim_migration->migrateAll();
        }

        if (isset($plugins['wpil']) && $plugins['wpil']) {
            $wpil_migration = new WpilMigration();
            $results['wpil'] = $wpil_migration->migrateAll();
        }

        return $results;
    }

    /**
     * Validate migration
     *
     * @param array $results Migration results
     * @return array Validation results
     */
    public function validateMigration($results)
    {
        $validation = [
            'success' => true,
            'errors' => [],
            'warnings' => [],
        ];

        // Validate DAIM migration
        if (isset($results['daim'])) {
            // TODO: Add validation logic
        }

        // Validate WPIL migration
        if (isset($results['wpil'])) {
            // TODO: Add validation logic
        }

        return $validation;
    }
}

