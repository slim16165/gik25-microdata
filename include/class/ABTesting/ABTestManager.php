<?php
namespace gik25microdata\ABTesting;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A/B Testing Framework
 * 
 * Framework per test A/B con tracking conversioni
 */
class ABTestManager
{
    const TABLE_NAME = 'revious_ab_tests';
    
    /**
     * Crea test A/B
     */
    public static function createTest(string $name, array $variants, array $options = []): int
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        $data = [
            'name' => sanitize_text_field($name),
            'variants' => wp_json_encode($variants),
            'traffic_split' => $options['traffic_split'] ?? 50,
            'status' => $options['status'] ?? 'draft',
            'created_at' => current_time('mysql'),
        ];
        
        $wpdb->insert($table_name, $data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Assegna variante a utente
     */
    public static function assignVariant(int $test_id, string $user_id = null): string
    {
        $user_id = $user_id ?: self::getUserId();
        $test = self::getTest($test_id);
        
        if (!$test) {
            return 'control';
        }
        
        // Usa hash consistente per assegnazione
        $hash = md5($test_id . $user_id);
        $variant_index = hexdec(substr($hash, 0, 2)) % count(json_decode($test['variants'], true));
        $variants = json_decode($test['variants'], true);
        
        return $variants[$variant_index]['name'] ?? 'control';
    }
    
    /**
     * Traccia conversione
     */
    public static function trackConversion(int $test_id, string $variant, string $goal): void
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME . '_conversions';
        
        $wpdb->insert($table_name, [
            'test_id' => $test_id,
            'variant' => $variant,
            'goal' => $goal,
            'user_id' => self::getUserId(),
            'created_at' => current_time('mysql'),
        ]);
    }
    
    /**
     * Ottiene risultati test
     */
    public static function getResults(int $test_id): array
    {
        global $wpdb;
        
        $conversions_table = $wpdb->prefix . self::TABLE_NAME . '_conversions';
        $test = self::getTest($test_id);
        
        if (!$test) {
            return [];
        }
        
        $variants = json_decode($test['variants'], true);
        $results = [];
        
        foreach ($variants as $variant) {
            $conversions = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$conversions_table}
                WHERE test_id = %d AND variant = %s",
                $test_id,
                $variant['name']
            ));
            
            $visitors = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT user_id) FROM {$conversions_table}
                WHERE test_id = %d AND variant = %s",
                $test_id,
                $variant['name']
            ));
            
            $results[$variant['name']] = [
                'conversions' => (int)$conversions,
                'visitors' => (int)$visitors,
                'conversion_rate' => $visitors > 0 ? round(($conversions / $visitors) * 100, 2) : 0,
            ];
        }
        
        return $results;
    }
    
    /**
     * Ottiene test
     */
    private static function getTest(int $test_id): ?array
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $test_id
        ), ARRAY_A) ?: null;
    }
    
    /**
     * Ottiene user ID
     */
    private static function getUserId(): string
    {
        $user_id = get_current_user_id();
        if ($user_id) {
            return 'user_' . $user_id;
        }
        
        // Usa cookie per utenti non loggati
        if (!isset($_COOKIE['revious_visitor_id'])) {
            $visitor_id = uniqid('visitor_', true);
            setcookie('revious_visitor_id', $visitor_id, time() + (365 * 24 * 60 * 60), '/');
            return $visitor_id;
        }
        
        return $_COOKIE['revious_visitor_id'];
    }
}
