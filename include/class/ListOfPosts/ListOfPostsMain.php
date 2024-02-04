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

        return $this->renderHelper->RenderLinksAsHtml($this->links, $title, $ulClass);
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