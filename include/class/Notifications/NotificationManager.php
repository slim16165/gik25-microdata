<?php
namespace gik25microdata\Notifications;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Notification System
 * 
 * Sistema notifiche multi-canale (email, dashboard, webhook)
 */
class NotificationManager
{
    /**
     * Invia notifica
     */
    public static function send(string $type, string $message, array $options = []): bool
    {
        $channels = $options['channels'] ?? ['dashboard'];
        
        $result = true;
        
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    $result = $result && self::sendEmail($type, $message, $options);
                    break;
                case 'dashboard':
                    $result = $result && self::addDashboardNotification($type, $message, $options);
                    break;
                case 'webhook':
                    $result = $result && self::sendWebhook($type, $message, $options);
                    break;
            }
        }
        
        return $result;
    }
    
    /**
     * Invia email
     */
    private static function sendEmail(string $type, string $message, array $options): bool
    {
        $to = $options['email'] ?? get_option('admin_email');
        $subject = $options['subject'] ?? 'Notifica Revious Microdata';
        
        return wp_mail($to, $subject, $message);
    }
    
    /**
     * Aggiunge notifica dashboard
     */
    private static function addDashboardNotification(string $type, string $message, array $options): bool
    {
        $notifications = get_option('revious_notifications', []);
        
        $notifications[] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => current_time('mysql'),
            'read' => false,
        ];
        
        // Mantieni solo ultime 100
        $notifications = array_slice($notifications, -100);
        
        return update_option('revious_notifications', $notifications);
    }
    
    /**
     * Invia webhook
     */
    private static function sendWebhook(string $type, string $message, array $options): bool
    {
        $webhook_url = $options['webhook_url'] ?? get_option('revious_webhook_url');
        
        if (empty($webhook_url)) {
            return false;
        }
        
        $payload = [
            'type' => $type,
            'message' => $message,
            'timestamp' => current_time('mysql'),
            'site' => get_bloginfo('name'),
        ];
        
        $response = wp_remote_post($webhook_url, [
            'body' => wp_json_encode($payload),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 5,
        ]);
        
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }
    
    /**
     * Ottiene notifiche non lette
     */
    public static function getUnreadNotifications(): array
    {
        $notifications = get_option('revious_notifications', []);
        
        return array_filter($notifications, function($notif) {
            return !($notif['read'] ?? false);
        });
    }
    
    /**
     * Marca notifica come letta
     */
    public static function markAsRead(int $index): bool
    {
        $notifications = get_option('revious_notifications', []);
        
        if (isset($notifications[$index])) {
            $notifications[$index]['read'] = true;
            return update_option('revious_notifications', $notifications);
        }
        
        return false;
    }
}
