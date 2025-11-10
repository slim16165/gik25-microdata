<?php
namespace gik25microdata\site_specific;

use gik25microdata\ListOfPosts\LinkBuilder;
use Yiisoft\Html\Html;

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('codicitributo', __NAMESPACE__ . '\\codicitributo_handler');

/**
 * Handler per lo shortcode codicitributo
 * Genera una lista di link ai codici tributo usando il nuovo sistema unificato
 */
function codicitributo_handler($atts, $content = null)
{
    $links = [
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codici-f24.htm', 'nome' => 'Codici e Modello F24'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-piu-usati.htm', 'nome' => 'Codici Tributo: i più utilizzati'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-6035.htm', 'nome' => 'Codice tributo 6035'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-6869.htm', 'nome' => 'Codice tributo 6869'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-2002.htm', 'nome' => 'Codice tributo 2002'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-109t.htm', 'nome' => 'Codice tributo 109t'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/il-codice-tributo-3812.htm', 'nome' => 'Codice tributo 3812'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-4034.htm', 'nome' => 'Codice tributo 4034'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-3850.htm', 'nome' => 'Codice tributo 3850'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-4001-a-cosa-si-riferisce-e-dove-trova-impiego.htm', 'nome' => 'Codice tributo 4001'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-2003.htm', 'nome' => 'Codice tributo 2003'],
        ['target_url' => 'https://www.prestitiinforma.it/leggi/codice-tributo-9001.htm', 'nome' => 'Codice tributo 9001'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-3813.htm', 'nome' => 'Codice tributo 3813'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-1668.htm', 'nome' => 'Codice tributo 1668'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-3844.htm', 'nome' => 'Codice tributo 3844'],
        ['target_url' => 'https://www.prestitiinforma.it/tributi/codice-tributo-1040.htm', 'nome' => 'Codice tributo 1040'],
    ];
    
    // Usa il nuovo sistema unificato per creare i link
    $builder = LinkBuilder::simple(['removeIfSelf' => true]);
    $linksHtml = $builder->createLinksFromArray($links, [
        'ulClass' => 'nicelist',
        'wrapInDiv' => false,
    ]);
    
    // Aggiungi <strong> ai link (mantenendo compatibilità con il formato originale)
    $linksHtml = str_replace('<a href', '<strong><a href', $linksHtml);
    $linksHtml = str_replace('</a>', '</a></strong>', $linksHtml);
    
    return Html::ul()->class('nicelist')->content($linksHtml)->encode(false)->render();
}

add_filter( 'xmlrpc_enabled', '__return_false' );