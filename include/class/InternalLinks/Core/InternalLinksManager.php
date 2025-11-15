<?php
/**
 * Internal Links Manager - Main Singleton Class
 *
 * @package gik25microdata\InternalLinks\Core
 */

namespace gik25microdata\InternalLinks\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main manager class for Internal Links system
 */
class InternalLinksManager
{
    /**
     * Singleton instance
     *
     * @var InternalLinksManager|null
     */
    private static $instance = null;

    /**
     * Whether the manager is initialized
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * Get singleton instance
     *
     * @return InternalLinksManager
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct()
    {
        // Prevent direct instantiation
    }

    /**
     * Initialize the manager
     *
     * @return void
     */
    public function init()
    {
        if ($this->initialized) {
            return;
        }

        // Check if feature is enabled
        if (!get_option('gik25_il_enabled', true)) {
            return;
        }

        // Ensure database tables exist
        if (!DatabaseSchema::tablesExist()) {
            DatabaseSchema::createTables();
        }

        // Register WordPress hooks
        $this->registerHooks();

        $this->initialized = true;
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    private function registerHooks()
    {
        // Content processing hook
        add_filter('the_content', [$this, 'processContent'], 10, 1);

        // Post save hook
        add_action('save_post', [$this, 'onPostSave'], 10, 2);

        // AJAX handlers
        add_action('wp_ajax_gik25_il_track_link_click', [$this, 'handleAjaxClick']);
        add_action('wp_ajax_nopriv_gik25_il_track_link_click', [$this, 'handleAjaxClick']);

        // Initialize REST API
        if (class_exists('\gik25microdata\InternalLinks\REST\ApiController')) {
            \gik25microdata\InternalLinks\REST\ApiController::init();
        }

        // Initialize Admin Menu (only in admin)
        if (is_admin() && class_exists('\gik25microdata\InternalLinks\Admin\AdminMenu')) {
            \gik25microdata\InternalLinks\Admin\AdminMenu::init();
        }

        // Initialize Editor Integration
        if (is_admin() && class_exists('\gik25microdata\InternalLinks\Integration\EditorIntegration')) {
            \gik25microdata\InternalLinks\Integration\EditorIntegration::init();
        }

        // Initialize ChatGPT Integration
        if (class_exists('\gik25microdata\InternalLinks\Integration\ChatGPTIntegration')) {
            \gik25microdata\InternalLinks\Integration\ChatGPTIntegration::init();
        }
    }

    /**
     * Process content and apply autolinks
     *
     * @param string $content Post content
     * @return string Processed content
     */
    public function processContent($content)
    {
        if (!is_singular() || is_attachment() || is_feed()) {
            return $content;
        }

        global $post;
        if (!$post || !isset($post->ID)) {
            return $content;
        }

        // Check if autolinks are enabled for this post
        $enable_ail = get_post_meta($post->ID, '_gik25_il_enable_ail', true);
        if (strlen(trim($enable_ail)) === 0) {
            $enable_ail = get_option('gik25_il_default_enable_ail_on_post', 1);
        }
        if (intval($enable_ail, 10) === 0) {
            return $content;
        }

        // Apply autolinks (will be implemented in AutolinkEngine)
        $processor = new LinkProcessor();
        $content = $processor->processLinks($content, $post->ID);

        return $content;
    }

    /**
     * Get suggestions for a post
     *
     * @param int $post_id Post ID
     * @param int $limit Number of suggestions
     * @return array Suggestions array
     */
    public function getSuggestions($post_id, $limit = 10)
    {
        $engine = new \gik25microdata\InternalLinks\Suggestions\SuggestionEngine();
        return $engine->generateSuggestions($post_id, ['limit' => $limit]);
    }

    /**
     * Calculate juice for a link
     *
     * @param int $post_id Post ID
     * @param int $link_position Link position in content
     * @return array Juice data
     */
    public function calculateJuice($post_id, $link_position)
    {
        $calculator = new \gik25microdata\InternalLinks\Reports\JuiceCalculator();
        return $calculator->calculateJuice($post_id, $link_position);
    }

    /**
     * Track click on a link
     *
     * @param int $link_id Link ID
     * @param array $data Additional data
     * @return bool Success
     */
    public function trackClick($link_id, $data = [])
    {
        $tracker = new \gik25microdata\InternalLinks\Reports\ClickTracker();
        return $tracker->trackClick($link_id, $data);
    }

    /**
     * Check HTTP status of a URL
     *
     * @param string $url URL to check
     * @return array Status data
     */
    public function checkHttpStatus($url)
    {
        $checker = new \gik25microdata\InternalLinks\Monitoring\HttpStatusChecker();
        return $checker->checkStatus($url);
    }

    /**
     * Generate report
     *
     * @param string $type Report type
     * @param array $filters Filters
     * @return array Report data
     */
    public function generateReport($type, $filters = [])
    {
        $generator = new \gik25microdata\InternalLinks\Reports\ReportGenerator();
        
        switch ($type) {
            case 'links':
                return $generator->generateLinkReport($filters);
            case 'juice':
                return $generator->generateJuiceReport($filters);
            case 'clicks':
                return $generator->generateClickReport($filters);
            default:
                return [];
        }
    }

    /**
     * Handle post save
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     * @return void
     */
    public function onPostSave($post_id, $post)
    {
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Update link statistics (will be implemented in LinkAnalyzer)
        $analyzer = new LinkAnalyzer();
        $analyzer->analyzeLinks($post_id);
    }

    /**
     * Handle AJAX click tracking
     *
     * @return void
     */
    public function handleAjaxClick()
    {
        check_ajax_referer('gik25_il_track_click', 'nonce');

        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if ($link_id > 0 && $post_id > 0) {
            $data = [
                'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '',
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
                'referrer' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
            ];

            $this->trackClick($link_id, $data);
        }

        wp_send_json_success();
    }
}

