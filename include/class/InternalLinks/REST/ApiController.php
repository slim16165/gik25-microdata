<?php
/**
 * REST API Controller for Internal Links
 *
 * @package gik25microdata\InternalLinks\REST
 */

namespace gik25microdata\InternalLinks\REST;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Controller class
 */
class ApiController
{
    /**
     * Namespace for REST API
     */
    const NAMESPACE = 'gik25-il/v1';

    /**
     * Initialize REST API
     *
     * @return void
     */
    public static function init()
    {
        $controller = new self();
        add_action('rest_api_init', [$controller, 'registerRoutes']);
    }

    /**
     * Register REST API routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        // Autolinks endpoints
        register_rest_route(self::NAMESPACE, '/autolinks', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getAutolinks'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'createAutolink'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/autolinks/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getAutolink'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
            [
                'methods' => 'PUT',
                'callback' => [$this, 'updateAutolink'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'deleteAutolink'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        // Suggestions endpoints
        register_rest_route(self::NAMESPACE, '/suggestions/(?P<post_id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getSuggestions'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/suggestions/generate', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'generateSuggestions'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        // Reports endpoints
        register_rest_route(self::NAMESPACE, '/reports/links', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getLinkReport'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/reports/juice', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getJuiceReport'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/reports/clicks', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getClickReport'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        // Monitoring endpoints
        register_rest_route(self::NAMESPACE, '/monitoring/health', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getHealth'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/monitoring/check-status', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'checkHttpStatus'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);
    }

    /**
     * Check permission
     *
     * @return bool
     */
    public function checkPermission()
    {
        return current_user_can('edit_posts');
    }

    /**
     * Get autolinks
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function getAutolinks($request)
    {
        global $wpdb;

        $autolinks = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}gik25_il_autolinks ORDER BY priority DESC",
            ARRAY_A
        );

        return new \WP_REST_Response($autolinks, 200);
    }

    /**
     * Get single autolink
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function getAutolink($request)
    {
        global $wpdb;

        $id = intval($request['id']);
        $autolink = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}gik25_il_autolinks WHERE id = %d",
            $id
        ), ARRAY_A);

        if (!$autolink) {
            return new \WP_Error('not_found', 'Autolink not found', ['status' => 404]);
        }

        return new \WP_REST_Response($autolink, 200);
    }

    /**
     * Create autolink
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function createAutolink($request)
    {
        // TODO: Implement autolink creation
        return new \WP_REST_Response(['message' => 'Not implemented'], 501);
    }

    /**
     * Update autolink
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function updateAutolink($request)
    {
        // TODO: Implement autolink update
        return new \WP_REST_Response(['message' => 'Not implemented'], 501);
    }

    /**
     * Delete autolink
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function deleteAutolink($request)
    {
        // TODO: Implement autolink deletion
        return new \WP_REST_Response(['message' => 'Not implemented'], 501);
    }

    /**
     * Get suggestions
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function getSuggestions($request)
    {
        $post_id = intval($request['post_id']);
        $manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
        $suggestions = $manager->getSuggestions($post_id);

        return new \WP_REST_Response($suggestions, 200);
    }

    /**
     * Generate suggestions
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function generateSuggestions($request)
    {
        // TODO: Implement suggestion generation
        return new \WP_REST_Response(['message' => 'Not implemented'], 501);
    }

    /**
     * Get link report
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function getLinkReport($request)
    {
        $filters = $request->get_query_params();
        $manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
        $report = $manager->generateReport('links', $filters);

        return new \WP_REST_Response($report, 200);
    }

    /**
     * Get juice report
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function getJuiceReport($request)
    {
        $filters = $request->get_query_params();
        $manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
        $report = $manager->generateReport('juice', $filters);

        return new \WP_REST_Response($report, 200);
    }

    /**
     * Get click report
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function getClickReport($request)
    {
        $filters = $request->get_query_params();
        $manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
        $report = $manager->generateReport('clicks', $filters);

        return new \WP_REST_Response($report, 200);
    }

    /**
     * Get health status
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function getHealth($request)
    {
        return new \WP_REST_Response([
            'status' => 'ok',
            'timestamp' => current_time('mysql'),
        ], 200);
    }

    /**
     * Check HTTP status
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function checkHttpStatus($request)
    {
        $url = isset($request['url']) ? esc_url_raw($request['url']) : '';
        if (empty($url)) {
            return new \WP_Error('invalid_url', 'URL is required', ['status' => 400]);
        }

        $manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
        $status = $manager->checkHttpStatus($url);

        return new \WP_REST_Response($status, 200);
    }
}

