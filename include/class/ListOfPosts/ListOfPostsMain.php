<?php

namespace gik25microdata\ListOfPosts;

use gik25microdata\ListOfBlocks\BlockBase;
use gik25microdata\ListOfBlocks\ListOfBlocks;
use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;

class ListOfPostsMain
{
    private string $category;
    private Collection $links;
    public ListOfPostsRenderHelper $renderHelper;

    public function __construct($links, $category)
    {
        $this->links = $links;
        $this->category = $category;
    }

    public function SaveLinks()
    {
        /** @var LinkBase $link */
        foreach ($this->links as $link) {
            $link->Category = $this->category;
            $link->SaveToDb();
        }
    }

    /**
     * Inizializza l'helper di rendering.
     *
     * @param bool $removeIfSelf
     * @param bool $withImage
     * @param bool $linkSelf
     * @param string $listOfPostsStyle
     */
    public function InitRenderHelper(bool $removeIfSelf, bool $withImage, bool $linkSelf, string $listOfPostsStyle = '') {
        $this->renderHelper = new ListOfPostsRenderHelper($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle);
    }

    public function RenderLinksAsHtml($title, $ulClass): string {
        if (!$this->renderHelper) {
            exit("Devi inizializzare Render Helper");
        }

        $current_permalink = WPPostsHelper::GetCurrentPostPermalink();

        // Genera una chiave univoca per il transient basata su variabili specifiche
        $transient_key = 'render_links_' . md5($current_permalink . serialize($this->renderHelper) . serialize($this->links) . $title . $ulClass);

        // Prova a ottenere i dati dal transient
        $renderLinksAsHtml = get_transient($transient_key);

        // Se i dati non sono nel transient, ottienili e memorizzali nel transient
        if ($renderLinksAsHtml === false)
        {
            $renderLinksAsHtml = $this->renderHelper->RenderLinksAsHtml($this->links, $title, $ulClass);
            set_transient($transient_key, $renderLinksAsHtml, WEEK_IN_SECONDS);
        }

        return $renderLinksAsHtml;
    }


    public function RenderLinksAsHtml2($title, $ulClass): string {
        if (!$this->renderHelper) {
            exit("Devi inizializzare Render Helper");
        }

        return $this->renderHelper->RenderLinksAsHtml($this->links, $title, $ulClass);
    }


    public function ConvertToListOfBlocks(string $shortcode, string $listDescription): ListOfBlocks
    {
        $blocks = [];

        /** @var LinkBase $link */
        foreach ($this->links as $link) {
            $block = new BlockBase('link', $link->Id);
            $blocks[] = $block->toArray();
        }

        return new ListOfBlocks($shortcode, $listDescription, $this->links);
    }
}