<?php
namespace gik25microdata\SiteSpecific;

use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Ul;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe base per funzionalità specifiche dei siti
 * Fornisce metodi helper comuni per tutti i file site_specific
 */
abstract class SiteSpecificBase
{
    /**
     * Crea un builder di link standard
     * 
     * @param bool $removeIfSelf Rimuovi link se punta alla pagina corrente
     * @param bool $withImage Includi immagine thumbnail
     * @param bool $linkSelf Crea link anche se punta alla pagina corrente
     * @param int $nColumns Numero di colonne (default: 1)
     * @return LinkBuilder
     */
    protected static function linkBuilder(
        bool $removeIfSelf = true,
        bool $withImage = true,
        bool $linkSelf = false,
        int $nColumns = 1
    ): LinkBuilder {
        return LinkBuilder::standard([
            'removeIfSelf' => $removeIfSelf,
            'withImage' => $withImage,
            'linkSelf' => $linkSelf,
            'nColumns' => $nColumns,
        ]);
    }
    
    /**
     * Crea un builder di link carousel
     * 
     * @return LinkBuilder
     */
    protected static function carouselBuilder(): LinkBuilder
    {
        return LinkBuilder::carousel();
    }
    
    /**
     * Crea un builder di link semplice (senza immagini)
     * 
     * @param bool $removeIfSelf Rimuovi link se punta alla pagina corrente
     * @return LinkBuilder
     */
    protected static function simpleBuilder(bool $removeIfSelf = true): LinkBuilder
    {
        return LinkBuilder::simple(['removeIfSelf' => $removeIfSelf]);
    }
    
    /**
     * Renderizza una lista di link con titolo
     * 
     * @param LinkBuilder $builder Builder configurato
     * @param array $links Array di link ['target_url' => ..., 'nome' => ..., 'commento' => ...]
     * @param string $title Titolo della lista
     * @param string $ulClass Classe CSS per la lista (default: 'thumbnail-list')
     * @return string HTML completo
     */
    protected static function renderList(
        LinkBuilder $builder,
        array $links,
        string $title = '',
        string $ulClass = 'thumbnail-list'
    ): string {
        $result = '';
        
        if (!empty($title)) {
            $result .= Html::h3($title)->render();
        }
        
        $result .= $builder->createLinksFromArray($links, [
            'ulClass' => $ulClass,
            'wrapInDiv' => true,
        ]);
        
        return $result;
    }
    
    /**
     * Renderizza una lista di link con Collection
     * 
     * @param LinkBuilder $builder Builder configurato
     * @param Collection $links Collezione di LinkBase
     * @param string $title Titolo della lista
     * @param string $ulClass Classe CSS per la lista
     * @return string HTML completo
     */
    protected static function renderListFromCollection(
        LinkBuilder $builder,
        Collection $links,
        string $title = '',
        string $ulClass = 'thumbnail-list'
    ): string {
        $result = '';
        
        if (!empty($title)) {
            $result .= Html::h3($title)->render();
        }
        
        $result .= $builder->createLinksFromCollection($links, [
            'ulClass' => $ulClass,
            'wrapInDiv' => true,
        ]);
        
        return $result;
    }
    
    /**
     * Renderizza una lista con sezioni (h4) e link
     * 
     * @param LinkBuilder $builder Builder configurato
     * @param array $sections Array di sezioni ['title' => '...', 'links' => [...]]
     * @param string $mainTitle Titolo principale
     * @param string $ulClass Classe CSS per le liste
     * @return string HTML completo
     */
    protected static function renderListWithSections(
        LinkBuilder $builder,
        array $sections,
        string $mainTitle = '',
        string $ulClass = 'nicelist'
    ): string {
        $result = '';
        
        if (!empty($mainTitle)) {
            $result .= Html::h3($mainTitle)->render();
        }
        
        foreach ($sections as $section) {
            $sectionTitle = $section['title'] ?? '';
            $sectionLinks = $section['links'] ?? [];
            
            if (!empty($sectionTitle)) {
                $result .= Html::h4($sectionTitle)->render();
            }
            
            if (!empty($sectionLinks)) {
                $result .= Html::ul()->class($ulClass)->open();
                $result .= $builder->createLinksFromArray($sectionLinks, [
                    'wrapInDiv' => false,
                ]);
                $result .= Ul::tag()->close();
            }
        }
        
        return $result;
    }
    
    /**
     * Helper per creare una Collection da array di link
     * 
     * @param array $links Array di link
     * @return Collection Collezione di LinkBase
     */
    protected static function createLinkCollection(array $links): Collection
    {
        $collection = new Collection();
        foreach ($links as $linkData) {
            $url = $linkData['target_url'] ?? $linkData['url'] ?? '';
            $title = $linkData['nome'] ?? $linkData['title'] ?? '';
            $comment = $linkData['commento'] ?? $linkData['comment'] ?? '';
            if (!empty($url) && !empty($title)) {
                $collection->add(new LinkBase($title, $url, $comment));
            }
        }
        return $collection;
    }
}
