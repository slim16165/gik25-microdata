<?php
namespace gik25microdata\site_specific;

use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\ListOfPosts\ListOfPostsHelper;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Funzioni helper per backward compatibility
 * Queste funzioni mantengono la compatibilità con il codice esistente
 * mentre usano internamente il nuovo sistema unificato
 */

/**
 * Crea un link se non punta alla pagina corrente (backward compatibility)
 * 
 * @param string $target_url URL del link
 * @param string $nome Nome/titolo del link
 * @param bool $removeIfSelf Rimuovi se punta alla pagina corrente
 * @return string HTML del link
 */
function linkIfNotSelf(string $target_url, string $nome, bool $removeIfSelf = true): string
{
    $builder = LinkBuilder::standard([
        'removeIfSelf' => $removeIfSelf,
        'withImage' => true,
    ]);
    return $builder->createLink($target_url, $nome);
}

/**
 * Crea un link semplice senza immagine (backward compatibility)
 * 
 * @param string $url URL del link
 * @param string $nome Nome/titolo del link
 * @return string HTML del link
 */
function linkIfNotSelf2(string $url, string $nome): string
{
    $builder = LinkBuilder::simple(['removeIfSelf' => true]);
    $rendered = $builder->createLink($url, $nome);
    
    // Mantiene il formato originale: solo link senza <li>
    if (strpos($rendered, '<li>') !== false) {
        $rendered = str_replace(['<li>', '</li>'], '', $rendered);
    }
    
    return $rendered;
}

/**
 * Helper per sostituire URL in staging (backward compatibility)
 * 
 * @param string $url URL da processare
 * @return string URL processato
 */
function ReplaceTargetUrlIfStaging(string $url): string
{
    return \gik25microdata\ListOfPosts\WPPostsHelper::ReplaceTargetUrlIfStaging($url);
}

// Assicura che le funzioni siano disponibili nel namespace globale per backward compatibility
if (!function_exists('linkIfNotSelf')) {
    function linkIfNotSelf(string $target_url, string $nome, bool $removeIfSelf = true): string
    {
        return \gik25microdata\site_specific\linkIfNotSelf($target_url, $nome, $removeIfSelf);
    }
}

if (!function_exists('linkIfNotSelf2')) {
    function linkIfNotSelf2(string $url, string $nome): string
    {
        return \gik25microdata\site_specific\linkIfNotSelf2($url, $nome);
    }
}
