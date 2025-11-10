<?php
namespace gik25microdata\ListOfPosts\Testing;

use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\ListOfPosts\Template\TemplateManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di A/B testing per template
 */
class ABTester
{
    private static array $tests = [];
    
    /**
     * Crea un test A/B
     * 
     * @param string $test_name Nome del test
     * @param string $template_a Template A
     * @param string $template_b Template B
     * @param int $split Split percentuale (50 = 50/50)
     * @return bool True se creato
     */
    public static function createTest(string $test_name, string $template_a, string $template_b, int $split = 50): bool
    {
        self::$tests[$test_name] = [
            'template_a' => $template_a,
            'template_b' => $template_b,
            'split' => $split,
            'stats' => [
                'a_views' => 0,
                'b_views' => 0,
                'a_clicks' => 0,
                'b_clicks' => 0,
            ],
        ];
        
        self::saveTests();
        return true;
    }
    
    /**
     * Ottiene il template da usare per un test
     * 
     * @param string $test_name Nome del test
     * @param string $user_id ID utente (per consistenza)
     * @return string Nome del template
     */
    public static function getTemplate(string $test_name, string $user_id = ''): string
    {
        if (!isset(self::$tests[$test_name])) {
            return 'standard';
        }
        
        $test = self::$tests[$test_name];
        $hash = md5($user_id . $test_name);
        $value = hexdec(substr($hash, 0, 8)) % 100;
        
        $template = $value < $test['split'] ? $test['template_a'] : $test['template_b'];
        
        // Registra view
        $variant = $value < $test['split'] ? 'a' : 'b';
        self::$tests[$test_name]['stats'][$variant . '_views']++;
        self::saveTests();
        
        return $template;
    }
    
    /**
     * Registra un click per un test
     * 
     * @param string $test_name Nome del test
     * @param string $variant Variante ('a' o 'b')
     */
    public static function recordClick(string $test_name, string $variant): void
    {
        if (!isset(self::$tests[$test_name])) {
            return;
        }
        
        if (in_array($variant, ['a', 'b'], true)) {
            self::$tests[$test_name]['stats'][$variant . '_clicks']++;
            self::saveTests();
        }
    }
    
    /**
     * Ottiene statistiche di un test
     * 
     * @param string $test_name Nome del test
     * @return array|null Statistiche o null
     */
    public static function getStats(string $test_name): ?array
    {
        if (!isset(self::$tests[$test_name])) {
            return null;
        }
        
        $stats = self::$tests[$test_name]['stats'];
        $stats['a_ctr'] = $stats['a_views'] > 0 ? ($stats['a_clicks'] / $stats['a_views']) * 100 : 0;
        $stats['b_ctr'] = $stats['b_views'] > 0 ? ($stats['b_clicks'] / $stats['b_views']) * 100 : 0;
        
        return $stats;
    }
    
    /**
     * Salva i test in opzione WordPress
     */
    private static function saveTests(): void
    {
        update_option('gik25_ab_tests', self::$tests, false);
    }
    
    /**
     * Carica i test da opzione WordPress
     */
    public static function loadTests(): void
    {
        $saved = get_option('gik25_ab_tests', []);
        if (is_array($saved)) {
            self::$tests = $saved;
        }
    }
}

// Carica i test all'avvio
add_action('init', [ABTester::class, 'loadTests'], 1);
