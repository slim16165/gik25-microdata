<?php
namespace gik25microdata\LinkGenerator;

use Illuminate\Support\Collection;
use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\ListOfPosts\ListOfPostsHelper;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Ul;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builder per semplificare la creazione di collezioni di link
 */
class LinkCollectionBuilder
{
    private Collection $links;
    private bool $withImage;
    private bool $removeIfSelf;
    private int $columns;
    private string $ulClass;
    
    public function __construct()
    {
        $this->links = new Collection();
        $this->withImage = true;
        $this->removeIfSelf = true;
        $this->columns = 1;
        $this->ulClass = 'thumbnail-list';
    }
    
    /**
     * Aggiunge un link alla collezione
     * 
     * @param string $url URL di destinazione
     * @param string $title Titolo del link
     * @param string $comment Commento opzionale
     * @return self
     */
    public function addLink(string $url, string $title, string $comment = ''): self
    {
        $this->links->add(new LinkBase($url, $title, $comment));
        return $this;
    }
    
    /**
     * Aggiunge multipli link da un array
     * 
     * @param array $links Array di link con struttura ['target_url' => '', 'nome' => '', 'commento' => '']
     * @return self
     */
    public function addLinks(array $links): self
    {
        foreach ($links as $link) {
            $url = $link['target_url'] ?? $link['url'] ?? '';
            $title = $link['nome'] ?? $link['title'] ?? '';
            $comment = $link['commento'] ?? $link['comment'] ?? '';
            $this->addLink($url, $title, $comment);
        }
        return $this;
    }
    
    /**
     * Imposta se includere le immagini
     * 
     * @param bool $withImage
     * @return self
     */
    public function withImage(bool $withImage = true): self
    {
        $this->withImage = $withImage;
        return $this;
    }
    
    /**
     * Imposta se rimuovere i link che puntano al post corrente
     * 
     * @param bool $removeIfSelf
     * @return self
     */
    public function removeIfSelf(bool $removeIfSelf = true): self
    {
        $this->removeIfSelf = $removeIfSelf;
        return $this;
    }
    
    /**
     * Imposta il numero di colonne
     * 
     * @param int $columns
     * @return self
     */
    public function columns(int $columns): self
    {
        $this->columns = max(1, $columns);
        return $this;
    }
    
    /**
     * Imposta la classe CSS per la lista
     * 
     * @param string $ulClass
     * @return self
     */
    public function ulClass(string $ulClass): self
    {
        $this->ulClass = $ulClass;
        return $this;
    }
    
    /**
     * Genera l'HTML della lista di link
     * 
     * @return string HTML della lista
     */
    public function build(): string
    {
        if ($this->links->isEmpty()) {
            return '';
        }
        
        $helper = new ListOfPostsHelper(
            $this->removeIfSelf,
            $this->withImage,
            false, // linkSelf
            (string)$this->columns
        );
        
        if ($this->columns > 1) {
            return $helper->GetLinksWithImagesMulticolumn($this->links);
        }
        
        $result = Html::ul()->class($this->ulClass)->open();
        $result .= $helper->getLinksWithImagesCurrentColumn($this->links);
        $result .= Ul::tag()->close();
        
        return $result;
    }
    
    /**
     * Genera l'HTML con titolo e contenitore
     * 
     * @param string $title Titolo della sezione
     * @param string $containerClass Classe CSS per il contenitore
     * @return string HTML completo
     */
    public function buildWithTitle(string $title, string $containerClass = 'thumbnail-list'): string
    {
        $result = Html::h3($title);
        $result .= Html::div()->class($containerClass)->open();
        $result .= $this->build();
        $result .= Html::div()->close();
        
        return $result;
    }
    
    /**
     * Crea un nuovo builder
     * 
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }
}
