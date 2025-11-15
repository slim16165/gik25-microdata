<?php
/**
 * Admin Menu - WordPress admin menu for Internal Links
 *
 * @package gik25microdata\InternalLinks\Admin
 */

namespace gik25microdata\InternalLinks\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Menu class
 */
class AdminMenu
{
    /**
     * Initialize admin menu
     *
     * @return void
     */
    public static function init()
    {
        $instance = new self();
        add_action('admin_menu', [$instance, 'addMenu']);
        add_action('admin_enqueue_scripts', [$instance, 'enqueueAssets']);
    }

    /**
     * Add admin menu
     *
     * @return void
     */
    public function addMenu()
    {
        $capability = 'edit_posts';

        // Main menu
        add_menu_page(
            __('Internal Links', 'gik25-microdata'),
            __('Internal Links', 'gik25-microdata'),
            $capability,
            'gik25-internal-links',
            [$this, 'renderDashboard'],
            'dashicons-admin-links',
            30
        );

        // Dashboard
        add_submenu_page(
            'gik25-internal-links',
            __('Dashboard', 'gik25-microdata'),
            __('Dashboard', 'gik25-microdata'),
            $capability,
            'gik25-internal-links',
            [$this, 'renderDashboard']
        );

        // Links Report
        add_submenu_page(
            'gik25-internal-links',
            __('Links Report', 'gik25-microdata'),
            __('Links', 'gik25-microdata'),
            $capability,
            'gik25-il-links',
            [$this, 'renderLinksReport']
        );

        // Autolinks
        add_submenu_page(
            'gik25-internal-links',
            __('Autolinks', 'gik25-microdata'),
            __('Autolinks', 'gik25-microdata'),
            $capability,
            'gik25-il-autolinks',
            [$this, 'renderAutolinks']
        );

        // Suggestions
        add_submenu_page(
            'gik25-internal-links',
            __('Suggestions', 'gik25-microdata'),
            __('Suggestions', 'gik25-microdata'),
            $capability,
            'gik25-il-suggestions',
            [$this, 'renderSuggestions']
        );

        // Juice
        add_submenu_page(
            'gik25-internal-links',
            __('Juice Report', 'gik25-microdata'),
            __('Juice', 'gik25-microdata'),
            $capability,
            'gik25-il-juice',
            [$this, 'renderJuice']
        );

        // Clicks
        add_submenu_page(
            'gik25-internal-links',
            __('Click Report', 'gik25-microdata'),
            __('Clicks', 'gik25-microdata'),
            $capability,
            'gik25-il-clicks',
            [$this, 'renderClicks']
        );

        // Status
        add_submenu_page(
            'gik25-internal-links',
            __('HTTP Status', 'gik25-microdata'),
            __('Status', 'gik25-microdata'),
            $capability,
            'gik25-il-status',
            [$this, 'renderStatus']
        );

        // Migration
        add_submenu_page(
            'gik25-internal-links',
            __('Migration', 'gik25-microdata'),
            __('Migration', 'gik25-microdata'),
            'manage_options',
            'gik25-il-migration',
            ['\gik25microdata\InternalLinks\Admin\MigrationPage', 'render']
        );

        // Settings
        add_submenu_page(
            'gik25-internal-links',
            __('Settings', 'gik25-microdata'),
            __('Settings', 'gik25-microdata'),
            'manage_options',
            'gik25-il-settings',
            [$this, 'renderSettings']
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current page hook
     * @return void
     */
    public function enqueueAssets($hook)
    {
        if (strpos($hook, 'gik25-il') === false && strpos($hook, 'gik25-internal-links') === false) {
            return;
        }

        wp_enqueue_style(
            'gik25-il-admin',
            plugins_url('../../../../assets/internal-links/css/admin.css', __FILE__),
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'gik25-il-admin',
            plugins_url('../../../../assets/internal-links/js/admin.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('gik25-il-admin', 'gik25_il_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gik25_il_track_click'),
        ]);
    }

    /**
     * Render dashboard page
     *
     * @return void
     */
    public function renderDashboard()
    {
        DashboardPage::render();
    }

    /**
     * Render links report page
     *
     * @return void
     */
    public function renderLinksReport()
    {
        echo '<div class="wrap gik25-il-admin">';
        echo '<h1>' . esc_html__('Links Report', 'gik25-microdata') . '</h1>';
        echo '<p>' . esc_html__('Links report coming soon...', 'gik25-microdata') . '</p>';
        echo '</div>';
    }

    /**
     * Render autolinks page
     *
     * @return void
     */
    public function renderAutolinks()
    {
        echo '<div class="wrap gik25-il-admin">';
        echo '<h1>' . esc_html__('Autolinks', 'gik25-microdata') . '</h1>';
        echo '<p>' . esc_html__('Autolinks management coming soon...', 'gik25-microdata') . '</p>';
        echo '</div>';
    }

    /**
     * Render suggestions page
     *
     * @return void
     */
    public function renderSuggestions()
    {
        echo '<div class="wrap gik25-il-admin">';
        echo '<h1>' . esc_html__('Suggestions', 'gik25-microdata') . '</h1>';
        echo '<p>' . esc_html__('Suggestions coming soon...', 'gik25-microdata') . '</p>';
        echo '</div>';
    }

    /**
     * Render juice page
     *
     * @return void
     */
    public function renderJuice()
    {
        echo '<div class="wrap gik25-il-admin">';
        echo '<h1>' . esc_html__('Juice Report', 'gik25-microdata') . '</h1>';
        echo '<p>' . esc_html__('Juice report coming soon...', 'gik25-microdata') . '</p>';
        echo '</div>';
    }

    /**
     * Render clicks page
     *
     * @return void
     */
    public function renderClicks()
    {
        echo '<div class="wrap gik25-il-admin">';
        echo '<h1>' . esc_html__('Click Report', 'gik25-microdata') . '</h1>';
        echo '<p>' . esc_html__('Click report coming soon...', 'gik25-microdata') . '</p>';
        echo '</div>';
    }

    /**
     * Render status page
     *
     * @return void
     */
    public function renderStatus()
    {
        echo '<div class="wrap gik25-il-admin">';
        echo '<h1>' . esc_html__('HTTP Status', 'gik25-microdata') . '</h1>';
        echo '<p>' . esc_html__('HTTP status report coming soon...', 'gik25-microdata') . '</p>';
        echo '</div>';
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function renderSettings()
    {
        echo '<div class="wrap gik25-il-admin">';
        echo '<h1>' . esc_html__('Internal Links Settings', 'gik25-microdata') . '</h1>';
        echo '<p>' . esc_html__('Settings page coming soon...', 'gik25-microdata') . '</p>';
        echo '</div>';
    }
}

