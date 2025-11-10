<?php
namespace gik25microdata\ListOfPosts\Notifications;

use gik25microdata\ListOfPosts\Validation\BrokenLinkChecker;
use gik25microdata\ListOfPosts\Validation\UrlValidator;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di notifiche per link rotti o post non pubblicati
 */
class LinkNotifier
{
    private const NOTIFICATION_OPTION = 'gik25_link_notifications';
    
    /**
     * Verifica e notifica link rotti
     * 
     * @param array $urls Array di URL da verificare
     * @param bool $send_email Invia email di notifica
     * @return array Risultati verifica
     */
    public static function checkAndNotify(array $urls, bool $send_email = false): array
    {
        $broken = BrokenLinkChecker::findBroken($urls);
        $notifications = [];
        
        foreach ($broken as $url => $result) {
            $notification = [
                'url' => $url,
                'error' => $result['error'],
                'status_code' => $result['status_code'],
                'timestamp' => current_time('mysql'),
                'notified' => false,
            ];
            
            // Salva notifica
            self::addNotification($notification);
            $notifications[] = $notification;
        }
        
        // Invia email se richiesto e ci sono link rotti
        if ($send_email && !empty($broken)) {
            self::sendEmailNotification($broken);
        }
        
        return $notifications;
    }
    
    /**
     * Aggiunge una notifica
     * 
     * @param array $notification Dati notifica
     */
    private static function addNotification(array $notification): void
    {
        $notifications = get_option(self::NOTIFICATION_OPTION, []);
        $notifications[] = $notification;
        
        // Mantieni solo le ultime 100 notifiche
        if (count($notifications) > 100) {
            $notifications = array_slice($notifications, -100);
        }
        
        update_option(self::NOTIFICATION_OPTION, $notifications, false);
    }
    
    /**
     * Ottiene le notifiche
     * 
     * @param bool $unread_only Solo non lette
     * @return array Array di notifiche
     */
    public static function getNotifications(bool $unread_only = false): array
    {
        $notifications = get_option(self::NOTIFICATION_OPTION, []);
        
        if ($unread_only) {
            $notifications = array_filter($notifications, function($n) {
                return empty($n['read']);
            });
        }
        
        return array_reverse($notifications); // Più recenti prima
    }
    
    /**
     * Segna una notifica come letta
     * 
     * @param int $index Indice della notifica
     * @return bool True se segnata
     */
    public static function markAsRead(int $index): bool
    {
        $notifications = get_option(self::NOTIFICATION_OPTION, []);
        
        if (isset($notifications[$index])) {
            $notifications[$index]['read'] = true;
            $notifications[$index]['read_at'] = current_time('mysql');
            update_option(self::NOTIFICATION_OPTION, $notifications, false);
            return true;
        }
        
        return false;
    }
    
    /**
     * Invia email di notifica
     * 
     * @param array $broken Link rotti
     */
    private static function sendEmailNotification(array $broken): void
    {
        $admin_email = get_option('admin_email');
        if (empty($admin_email)) {
            return;
        }
        
        $subject = sprintf('[%s] %d link rotti rilevati', get_bloginfo('name'), count($broken));
        
        $message = "Sono stati rilevati " . count($broken) . " link rotti:\n\n";
        
        foreach ($broken as $url => $result) {
            $message .= "- {$url}\n";
            $message .= "  Errore: {$result['error']}\n";
            if ($result['status_code'] > 0) {
                $message .= "  Status Code: {$result['status_code']}\n";
            }
            $message .= "\n";
        }
        
        $message .= "\nVerifica i link nel pannello di amministrazione WordPress.";
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Pulisce le notifiche vecchie
     * 
     * @param int $days Giorni di retention
     * @return int Numero di notifiche rimosse
     */
    public static function cleanup(int $days = 30): int
    {
        $notifications = get_option(self::NOTIFICATION_OPTION, []);
        $before = count($notifications);
        $cutoff = strtotime("-{$days} days");
        
        $notifications = array_filter($notifications, function($n) use ($cutoff) {
            return strtotime($n['timestamp']) >= $cutoff;
        });
        
        update_option(self::NOTIFICATION_OPTION, array_values($notifications), false);
        
        return $before - count($notifications);
    }
}
