<?php
namespace gik25microdata\SiteSpecific;

use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper functions globali per facilitare l'uso del sistema di link
 * Queste funzioni possono essere usate direttamente nei file site_specific
 */
class Helper
{
    /**
     * Crea un link semplice (backward compatibility con linkIfNotSelf)
     * 
     * @param string $url URL del link
     * @param string $nome Nome/titolo del link
     * @param bool $removeIfSelf Rimuovi se punta alla pagina corrente
     * @return string HTML del link
     */
    public static function linkIfNotSelf(string $url, string $nome, bool $removeIfSelf = true): string
    {
        $builder = LinkBuilder::simple(['removeIfSelf' => $removeIfSelf]);
        return $builder->createLink($url, $nome);
    }
    
    /**
     * Crea un link con immagine (backward compatibility)
     * 
     * @param string $url URL del link
     * @param string $nome Nome/titolo del link
     * @param bool $removeIfSelf Rimuovi se punta alla pagina corrente
     * @return string HTML del link
     */
    public static function linkWithImage(string $url, string $nome, bool $removeIfSelf = true): string
    {
        $builder = LinkBuilder::standard([
            'removeIfSelf' => $removeIfSelf,
            'withImage' => true,
        ]);
        return $builder->createLink($url, $nome);
    }
    
    /**
     * Crea un link carousel (backward compatibility con ColorWidget)
     * 
     * @param string $url URL del link
     * @param string $nome Nome/titolo del link
     * @return string HTML del link
     */
    public static function linkCarousel(string $url, string $nome): string
    {
        $builder = LinkBuilder::carousel();
        return $builder->createLink($url, $nome);
    }
    
    /**
     * Crea una lista di link da array
     * 
     * @param array $links Array di link
     * @param array $options Opzioni per il builder
     * @return string HTML della lista
     */
    public static function renderLinks(array $links, array $options = []): string
    {
        $style = $options['style'] ?? 'standard';
        unset($options['style']);
        
        $builder = new LinkBuilder($style, $options);
        return $builder->createLinksFromArray($links, $options);
    }
}
