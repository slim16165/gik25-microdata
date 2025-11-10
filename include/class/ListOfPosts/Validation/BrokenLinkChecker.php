<?php
namespace gik25microdata\ListOfPosts\Validation;

use gik25microdata\ListOfPosts\Logging\LinkLogger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validazione automatica link rotti
 */
class BrokenLinkChecker
{
    /**
     * Verifica se un link è rotto
     * 
     * @param string $url URL da verificare
     * @return array Risultato ['broken' => bool, 'status_code' => int, 'error' => string]
     */
    public static function check(string $url): array
    {
        $result = [
            'broken' => false,
            'status_code' => 0,
            'error' => '',
        ];
        
        // Verifica se è un URL WordPress interno
        if (UrlValidator::isWordPressUrl($url)) {
            $post_id = url_to_postid($url);
            
            if ($post_id === 0) {
                $result['broken'] = true;
                $result['error'] = 'Post non trovato';
                LinkLogger::warning('Link rotto rilevato', ['url' => $url, 'reason' => 'post_not_found']);
                return $result;
            }
            
            $post = get_post($post_id);
            if (!$post || $post->post_status !== 'publish') {
                $result['broken'] = true;
                $result['error'] = 'Post non pubblicato';
                LinkLogger::warning('Link rotto rilevato', ['url' => $url, 'reason' => 'post_not_published']);
                return $result;
            }
            
            $result['status_code'] = 200;
            return $result;
        }
        
        // Verifica URL esterno
        $response = wp_remote_head($url, [
            'timeout' => 5,
            'redirection' => 5,
            'sslverify' => true,
        ]);
        
        if (is_wp_error($response)) {
            $result['broken'] = true;
            $result['error'] = $response->get_error_message();
            LinkLogger::error('Link esterno rotto', ['url' => $url, 'error' => $result['error']]);
            return $result;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $result['status_code'] = $status_code;
        $result['broken'] = $status_code < 200 || $status_code >= 400;
        
        if ($result['broken']) {
            LinkLogger::warning('Link esterno con status code non valido', [
                'url' => $url,
                'status_code' => $status_code,
            ]);
        }
        
        return $result;
    }
    
    /**
     * Verifica una collezione di URL
     * 
     * @param array $urls Array di URL
     * @return array Risultati per ogni URL
     */
    public static function checkBatch(array $urls): array
    {
        $results = [];
        
        foreach ($urls as $url) {
            $results[$url] = self::check($url);
            
            // Pausa per non sovraccaricare il server
            usleep(100000); // 0.1 secondi
        }
        
        return $results;
    }
    
    /**
     * Trova link rotti in una collezione
     * 
     * @param array $urls Array di URL
     * @return array URL rotti con relativi errori
     */
    public static function findBroken(array $urls): array
    {
        $broken = [];
        $results = self::checkBatch($urls);
        
        foreach ($results as $url => $result) {
            if ($result['broken']) {
                $broken[$url] = $result;
            }
        }
        
        return $broken;
    }
}
