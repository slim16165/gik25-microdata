<?php
namespace gik25microdata\ListOfPosts\Search;

use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di ricerca e filtri avanzati per link
 */
class LinkSearcher
{
    /**
     * Cerca link in una collezione
     * 
     * @param Collection $links Collezione di link
     * @param string $query Query di ricerca
     * @param array $options Opzioni di ricerca
     * @return Collection Link filtrati
     */
    public static function search(Collection $links, string $query, array $options = []): Collection
    {
        if (empty($query)) {
            return $links;
        }
        
        $searchInTitle = $options['search_in_title'] ?? true;
        $searchInUrl = $options['search_in_url'] ?? true;
        $searchInComment = $options['search_in_comment'] ?? true;
        $caseSensitive = $options['case_sensitive'] ?? false;
        
        $query = $caseSensitive ? $query : mb_strtolower($query);
        
        return $links->filter(function($link) use ($query, $searchInTitle, $searchInUrl, $searchInComment, $caseSensitive) {
            if (!($link instanceof LinkBase)) {
                return false;
            }
            
            $title = $caseSensitive ? $link->Title : mb_strtolower($link->Title);
            $url = $caseSensitive ? $link->Url : mb_strtolower($link->Url);
            $comment = $caseSensitive ? $link->Comment : mb_strtolower($link->Comment);
            
            if ($searchInTitle && strpos($title, $query) !== false) {
                return true;
            }
            
            if ($searchInUrl && strpos($url, $query) !== false) {
                return true;
            }
            
            if ($searchInComment && strpos($comment, $query) !== false) {
                return true;
            }
            
            return false;
        });
    }
    
    /**
     * Filtra link per URL
     * 
     * @param Collection $links Collezione di link
     * @param string $pattern Pattern da cercare nell'URL
     * @return Collection Link filtrati
     */
    public static function filterByUrl(Collection $links, string $pattern): Collection
    {
        return $links->filter(function($link) use ($pattern) {
            return $link instanceof LinkBase && strpos($link->Url, $pattern) !== false;
        });
    }
    
    /**
     * Filtra link per dominio
     * 
     * @param Collection $links Collezione di link
     * @param string $domain Dominio da filtrare
     * @return Collection Link filtrati
     */
    public static function filterByDomain(Collection $links, string $domain): Collection
    {
        return $links->filter(function($link) use ($domain) {
            if (!($link instanceof LinkBase)) {
                return false;
            }
            
            $parsed = parse_url($link->Url);
            $linkDomain = $parsed['host'] ?? '';
            
            return $linkDomain === $domain || strpos($linkDomain, $domain) !== false;
        });
    }
    
    /**
     * Filtra link interni vs esterni
     * 
     * @param Collection $links Collezione di link
     * @param bool $internal True per interni, false per esterni
     * @return Collection Link filtrati
     */
    public static function filterByInternal(Collection $links, bool $internal = true): Collection
    {
        $homeUrl = home_url();
        $homeDomain = parse_url($homeUrl, PHP_URL_HOST);
        
        return $links->filter(function($link) use ($internal, $homeDomain) {
            if (!($link instanceof LinkBase)) {
                return false;
            }
            
            $parsed = parse_url($link->Url);
            $linkDomain = $parsed['host'] ?? '';
            
            $isInternal = $linkDomain === $homeDomain || strpos($linkDomain, $homeDomain) !== false;
            
            return $internal ? $isInternal : !$isInternal;
        });
    }
    
    /**
     * Ordina link
     * 
     * @param Collection $links Collezione di link
     * @param string $field Campo per ordinare ('title', 'url', 'comment')
     * @param string $direction Direzione ('asc', 'desc')
     * @return Collection Link ordinati
     */
    public static function sort(Collection $links, string $field = 'title', string $direction = 'asc'): Collection
    {
        return $links->sortBy(function($link) use ($field) {
            if (!($link instanceof LinkBase)) {
                return '';
            }
            
            switch ($field) {
                case 'url':
                    return $link->Url;
                case 'comment':
                    return $link->Comment;
                case 'title':
                default:
                    return $link->Title;
            }
        }, SORT_REGULAR, $direction === 'desc');
    }
    
    /**
     * Applica filtri multipli
     * 
     * @param Collection $links Collezione di link
     * @param array $filters Array di filtri ['type' => 'search|url|domain|internal', 'value' => ...]
     * @return Collection Link filtrati
     */
    public static function applyFilters(Collection $links, array $filters): Collection
    {
        $result = $links;
        
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? '';
            $value = $filter['value'] ?? '';
            
            switch ($type) {
                case 'search':
                    $result = self::search($result, $value, $filter['options'] ?? []);
                    break;
                case 'url':
                    $result = self::filterByUrl($result, $value);
                    break;
                case 'domain':
                    $result = self::filterByDomain($result, $value);
                    break;
                case 'internal':
                    $result = self::filterByInternal($result, (bool)$value);
                    break;
            }
        }
        
        return $result;
    }
}
