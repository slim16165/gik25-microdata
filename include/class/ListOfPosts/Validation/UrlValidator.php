<?php
namespace gik25microdata\ListOfPosts\Validation;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di validazione URL avanzato
 * Verifica esistenza, accessibilità e validità degli URL
 */
class UrlValidator
{
    /**
     * Valida un URL
     * 
     * @param string $url URL da validare
     * @param bool $checkExistence Verifica esistenza del post (default: true)
     * @return array Risultato validazione ['valid' => bool, 'errors' => [], 'warnings' => []]
     */
    public static function validate(string $url, bool $checkExistence = true): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'url' => $url,
        ];
        
        // Validazione formato base
        if (empty($url)) {
            $result['valid'] = false;
            $result['errors'][] = 'URL vuoto';
            return $result;
        }
        
        // Verifica formato URL valido
        if (!filter_var($url, FILTER_VALIDATE_URL) && !self::isWordPressUrl($url)) {
            $result['valid'] = false;
            $result['errors'][] = 'Formato URL non valido';
            return $result;
        }
        
        // Verifica se è un URL WordPress interno
        if (self::isWordPressUrl($url)) {
            if ($checkExistence) {
                $post_id = url_to_postid($url);
                
                if ($post_id === 0) {
                    $result['warnings'][] = 'Post non trovato per questo URL';
                } else {
                    $post = get_post($post_id);
                    if (!$post) {
                        $result['warnings'][] = 'Post non esiste';
                    } elseif ($post->post_status !== 'publish') {
                        $result['warnings'][] = 'Post non pubblicato (status: ' . $post->post_status . ')';
                    } else {
                        $result['post_id'] = $post_id;
                        $result['post_title'] = $post->post_title;
                    }
                }
            }
        } else {
            // URL esterno - verifica accessibilità (opzionale, può essere lento)
            $result['is_external'] = true;
            $result['warnings'][] = 'URL esterno - verifica accessibilità non eseguita automaticamente';
        }
        
        // Verifica protocollo
        if (!preg_match('/^https?:\/\//i', $url)) {
            $result['warnings'][] = 'URL senza protocollo (http/https)';
        }
        
        return $result;
    }
    
    /**
     * Verifica se un URL è interno a WordPress
     * 
     * @param string $url URL da verificare
     * @return bool True se è un URL WordPress
     */
    public static function isWordPressUrl(string $url): bool
    {
        $home_url = home_url();
        $parsed_home = parse_url($home_url);
        $parsed_url = parse_url($url);
        
        if (!$parsed_home || !$parsed_url) {
            return false;
        }
        
        // Confronta dominio
        $home_domain = ($parsed_home['host'] ?? '');
        $url_domain = ($parsed_url['host'] ?? '');
        
        return $home_domain === $url_domain || 
               strpos($url_domain, $home_domain) !== false ||
               strpos($home_domain, $url_domain) !== false;
    }
    
    /**
     * Verifica accessibilità di un URL esterno (HTTP request)
     * 
     * @param string $url URL da verificare
     * @param int $timeout Timeout in secondi
     * @return array Risultato ['accessible' => bool, 'status_code' => int, 'error' => string]
     */
    public static function checkExternalUrl(string $url, int $timeout = 5): array
    {
        $result = [
            'accessible' => false,
            'status_code' => 0,
            'error' => '',
        ];
        
        if (!self::isWordPressUrl($url)) {
            $response = wp_remote_head($url, [
                'timeout' => $timeout,
                'redirection' => 5,
                'sslverify' => true,
            ]);
            
            if (is_wp_error($response)) {
                $result['error'] = $response->get_error_message();
            } else {
                $result['status_code'] = wp_remote_retrieve_response_code($response);
                $result['accessible'] = $result['status_code'] >= 200 && $result['status_code'] < 400;
            }
        }
        
        return $result;
    }
    
    /**
     * Valida una collezione di URL
     * 
     * @param array $urls Array di URL
     * @param bool $checkExistence Verifica esistenza
     * @return array Risultati validazione per ogni URL
     */
    public static function validateBatch(array $urls, bool $checkExistence = true): array
    {
        $results = [];
        foreach ($urls as $url) {
            $results[$url] = self::validate($url, $checkExistence);
        }
        return $results;
    }
    
    /**
     * Trova URL non validi in una collezione
     * 
     * @param array $urls Array di URL
     * @return array URL non validi con relativi errori
     */
    public static function findInvalidUrls(array $urls): array
    {
        $invalid = [];
        foreach ($urls as $url) {
            $validation = self::validate($url);
            if (!$validation['valid'] || !empty($validation['errors'])) {
                $invalid[$url] = $validation;
            }
        }
        return $invalid;
    }
}
