<?php
/**
 * ChatGPT Integration - Stub for ChatGPT API calls and user interaction
 *
 * @package gik25microdata\InternalLinks\Integration
 */

namespace gik25microdata\InternalLinks\Integration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ChatGPT Integration class
 */
class ChatGPTIntegration
{
    /**
     * API endpoint
     */
    const API_ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    /**
     * Initialize ChatGPT integration
     *
     * @return void
     */
    public static function init()
    {
        $instance = new self();
        add_action('wp_ajax_gik25_il_chatgpt_query', [$instance, 'handleAjaxQuery']);
        add_action('wp_ajax_nopriv_gik25_il_chatgpt_query', [$instance, 'handleAjaxQuery']);
        add_action('rest_api_init', [$instance, 'registerRestRoutes']);
    }

    /**
     * Register REST API routes
     *
     * @return void
     */
    public function registerRestRoutes()
    {
        register_rest_route('gik25-il/v1', '/chatgpt/query', [
            'methods' => 'POST',
            'callback' => [$this, 'handleRestQuery'],
            'permission_callback' => [$this, 'checkPermission'],
        ]);
    }

    /**
     * Check permission
     *
     * @return bool
     */
    public function checkPermission()
    {
        // Allow logged-in users and optionally public (configurable)
        return is_user_logged_in() || get_option('gik25_il_chatgpt_public', false);
    }

    /**
     * Handle AJAX query
     *
     * @return void
     */
    public function handleAjaxQuery()
    {
        check_ajax_referer('gik25_il_chatgpt', 'nonce');

        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        $context = isset($_POST['context']) ? sanitize_textarea_field($_POST['context']) : '';

        if (empty($query)) {
            wp_send_json_error(['message' => 'Query is required']);
            return;
        }

        $response = $this->queryChatGPT($query, $context);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error($response['error']);
        }
    }

    /**
     * Handle REST API query
     *
     * @param \WP_REST_Request $request Request
     * @return \WP_REST_Response
     */
    public function handleRestQuery($request)
    {
        $query = $request->get_param('query');
        $context = $request->get_param('context');

        if (empty($query)) {
            return new \WP_Error('invalid_query', 'Query is required', ['status' => 400]);
        }

        $response = $this->queryChatGPT($query, $context);

        if ($response['success']) {
            return new \WP_REST_Response($response['data'], 200);
        } else {
            return new \WP_Error('chatgpt_error', $response['error']['message'], ['status' => 500]);
        }
    }

    /**
     * Query ChatGPT API
     *
     * @param string $query User query
     * @param string $context Additional context
     * @return array Response
     */
    public function queryChatGPT($query, $context = '')
    {
        $api_key = get_option('gik25_il_chatgpt_api_key', '');
        
        if (empty($api_key)) {
            return [
                'success' => false,
                'error' => ['message' => 'ChatGPT API key not configured'],
            ];
        }

        // Build messages
        $messages = [];
        
        // System message
        $system_message = 'You are a helpful assistant for a WordPress internal linking system. ';
        $system_message .= 'Help users understand and optimize their internal linking strategy.';
        if (!empty($context)) {
            $system_message .= "\n\nContext: " . $context;
        }
        
        $messages[] = [
            'role' => 'system',
            'content' => $system_message,
        ];
        
        // User message
        $messages[] = [
            'role' => 'user',
            'content' => $query,
        ];

        // Prepare request
        $body = [
            'model' => get_option('gik25_il_chatgpt_model', 'gpt-3.5-turbo'),
            'messages' => $messages,
            'max_tokens' => intval(get_option('gik25_il_chatgpt_max_tokens', 500)),
            'temperature' => floatval(get_option('gik25_il_chatgpt_temperature', 0.7)),
        ];

        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body' => json_encode($body),
            'timeout' => 30,
        ];

        $response = wp_remote_request(self::API_ENDPOINT, $args);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => ['message' => $response->get_error_message()],
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code !== 200) {
            return [
                'success' => false,
                'error' => [
                    'message' => isset($body['error']['message']) ? $body['error']['message'] : 'Unknown error',
                    'code' => $status_code,
                ],
            ];
        }

        $content = isset($body['choices'][0]['message']['content']) 
            ? $body['choices'][0]['message']['content'] 
            : '';

        return [
            'success' => true,
            'data' => [
                'response' => $content,
                'usage' => isset($body['usage']) ? $body['usage'] : null,
            ],
        ];
    }

    /**
     * Get link suggestions from ChatGPT
     *
     * @param int $post_id Post ID
     * @param string $content Post content
     * @return array Suggestions
     */
    public function getLinkSuggestions($post_id, $content)
    {
        $query = "Analyze this WordPress post content and suggest 5 relevant internal links. ";
        $query .= "For each suggestion, provide: target post title, anchor text, and reason. ";
        $query .= "Format as JSON array with keys: title, anchor, reason.\n\n";
        $query .= "Content:\n" . wp_strip_all_tags($content);

        $response = $this->queryChatGPT($query);

        if (!$response['success']) {
            return [];
        }

        // Try to parse JSON from response
        $json = json_decode($response['data']['response'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        // Fallback: return empty array
        return [];
    }
}

