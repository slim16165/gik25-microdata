<?php
/**
 * Migration Page - UI for data migration
 *
 * @package gik25microdata\InternalLinks\Admin
 */

namespace gik25microdata\InternalLinks\Admin;

use gik25microdata\InternalLinks\Migration\MigrationManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Migration Page class
 */
class MigrationPage
{
    /**
     * Render migration page
     *
     * @return void
     */
    public static function render()
    {
        $manager = new MigrationManager();
        $detected = $manager->detectPlugins();

        echo '<div class="wrap gik25-il-admin">';
        echo '<h1>' . esc_html__('Migrate Data', 'gik25-microdata') . '</h1>';

        if (isset($_POST['run_migration']) && check_admin_referer('gik25_il_migrate')) {
            $plugins_to_migrate = isset($_POST['plugins']) ? $_POST['plugins'] : [];
            $results = $manager->runMigration($plugins_to_migrate);
            $validation = $manager->validateMigration($results);

            echo '<div class="notice notice-success"><p>Migration completed!</p></div>';
            echo '<pre>' . print_r($results, true) . '</pre>';
        }

        echo '<form method="post">';
        wp_nonce_field('gik25_il_migrate');

        if ($detected['daim']) {
            echo '<p><label><input type="checkbox" name="plugins[daim]" value="1"> Migrate from Interlinks Manager (DAIM)</label></p>';
        }

        if ($detected['wpil']) {
            echo '<p><label><input type="checkbox" name="plugins[wpil]" value="1"> Migrate from Link Whisper Premium (WPIL)</label></p>';
        }

        if (!$detected['daim'] && !$detected['wpil']) {
            echo '<p>' . esc_html__('No source plugins detected.', 'gik25-microdata') . '</p>';
        } else {
            echo '<p><button type="submit" name="run_migration" class="button button-primary">Run Migration</button></p>';
        }

        echo '</form>';
        echo '</div>';
    }
}

