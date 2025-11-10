<?php
namespace gik25microdata\Webhooks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Webhook System
 * 
 * Sistema webhook per integrazioni esterne
 */
class WebhookManager
{
    const TABLE_NAME = 'revious_webhooks';
    
    /**
     * Inizializza webhook manager
     */
    public static function init(): void
    {
        self::createTable();
        add_action('save_post', [self::class, 'triggerPostWebhooks'], 10, 2);
        add_action('wp_ajax_revious_test_webhook', [self::class, 'testWebhook']);
    }
    
    /**
     * Crea tabella webhooks
     */
    private static function createTable(): void
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            url varchar(500) NOT NULL,
            events text NOT NULL,
            secret varchar(100) DEFAULT NULL,
            active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Trigger webhooks per post
     */
    public static function triggerPostWebhooks(int $post_id, \WP_Post $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        $webhooks = self::getActiveWebhooks('post.*');
        
        foreach ($webhooks as $webhook) {
            self::sendWebhook($webhook, 'post.updated', [
                'post_id' => $post_id,
                'post' => [
                    'title' => $post->post_title,
                    'status' => $post->post_status,
                    'url' => get_permalink($post_id),
                ],
            ]);
        }
    }
    
    /**
     * Invia webhook
     */
    public static function sendWebhook(array $webhook, string $event, array $payload): bool
    {
        $events = json_decode($webhook['events'], true) ?: [];
        
        if (!in_array($event, $events) && !in_array('*', $events)) {
            return false;
        }
        
        $payload['event'] = $event;
        $payload['timestamp'] = current_time('mysql');
        $payload['signature'] = self::generateSignature($webhook, $payload);
        
        $response = wp_remote_post($webhook['url'], [
            'body' => wp_json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Webhook-Signature' => $payload['signature'],
            ],
            'timeout' => 10,
        ]);
        
        return !is_wp_error($response);
    }
    
    /**
     * Genera signature
     */
    private static function generateSignature(array $webhook, array $payload): string
    {
        $secret = $webhook['secret'] ?? '';
        return hash_hmac('sha256', wp_json_encode($payload), $secret);
    }
    
    /**
     * Ottiene webhooks attivi
     */
    private static function getActiveWebhooks(string $event = '*'): array
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE active = 1",
            ARRAY_A
        ) ?: [];
    }
    
    /**
     * Test webhook
     */
    public static function testWebhook(): void
    {
        check_ajax_referer('revious_webhook', 'nonce');
        
        $webhook_id = intval($_POST['webhook_id'] ?? 0);
        
        if (!$webhook_id) {
            wp_send_json_error(['message' => 'Invalid webhook ID']);
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $webhook = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $webhook_id
        ), ARRAY_A);
        
        if (!$webhook) {
            wp_send_json_error(['message' => 'Webhook not found']);
            return;
        }
        
        $result = self::sendWebhook($webhook, 'test', [
            'message' => 'Test webhook from Revious Microdata',
        ]);
        
        wp_send_json_success(['sent' => $result]);
    }
}
