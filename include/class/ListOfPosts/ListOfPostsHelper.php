<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Illuminate\Support\Collection;
use include\class\ListOfPosts\Types\LinkBase;
use JetBrains\PhpStorm\Pure;
use ListOfPosts\LinkConfig;
use Yiisoft\Html\Tag\Div;


class ListOfPostsHelper
{
    private LinkConfig $linkConfig;

    /**
     * @param bool $removeIfSelf
     * @param bool $withImage
     * @param bool $linkSelf
     * @param string $listOfPostsStyle
     */
    #[Pure] function __construct($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle = '')
    {
        $this->linkConfig = new LinkConfig($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle);
    }



    public static function ParseComment(string $commento): string
    {
        if (!MyString::IsNullOrEmptyString($commento) && !MyString::Contains("$commento", "("))
        {
            $commento = " ($commento)";
        }
        return $commento;
    }

    /**
     * @param array<array> $links_data
     * @return string
     */
    public function GetLinksWithImages(array $links_data): string
    {
        $collection = Util::ConvertArrayToCollectionOfLinks($links_data);

        if($this->linkConfig->nColumns > 1)
            return $this->GetLinksWithImagesMulticolumn($collection);
        else
            return $this->getLinksWithImagesCurrentColumn($collection);
    }


    /**
     * @param Collection<LinkBase> $links_data
     * @return string
     */
    public function GetLinksWithImagesMulticolumn(Collection $links_data) : string
    {
        //https://blog.jetbrains.com/phpstorm/2021/12/phpstorm-2021-3-release/#improved_support_for_doctrine_collections
        $links_per_column_arr = Util::PaginateArray($links_data, $this->linkConfig->nColumns);

        $links_html = '';
        $cssDivClass = sprintf("list-of-posts-layout-%s", $this->linkConfig->nColumns);
        //$links_html_per_col = array(); $col_ix = 0;
        foreach ($links_per_column_arr as $links_col_x)
        {
            //$currentColumn = $links_html_per_col[$col_ix];
            $currentColumn = $this->getLinksWithImagesCurrentColumn($links_col_x);

            //Genero l'html di tutte le colonne
            $links_html.= Div::tag()
                            ->class($cssDivClass)
                            ->content($currentColumn)
                            ->encode(false)
                            ->render();
            //$col_ix++;
        }

        return $links_html;
    }

    /**
     * @param string|null $target_url
     * @param string|null $nome
     * @param string|null $commento
     * @return string
     */
    public function GetLinkWithImage(?string $target_url, ?string $nome, ?string $commento = ""): string
    {
        if($target_url == null)
            return "";

        list($target_post, $noLink, $debugMsg) = WPPostsHelper::GetPostData($target_url, $this->linkConfig->removeIfSelf);

        //In caso contrario il post è pubblicato
        $commento = self::ParseComment($commento);

        if ($debugMsg)
        {
            $target_post = null;
        }

        if ($this->linkConfig->withImage) //GetTemplateWithThumbnail per la classe child
            $result = HtmlTemplate::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
        else
            $result = HtmlTemplate::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);

        return $result;
    }

    /**
     * @param Collection<LinkBase> $links_col_x
     * @return string
     */
    public function getLinksWithImagesCurrentColumn(Collection $links_col_x): string
    {
        $currentColumn = "";

        //html di una singola colonna
        /** @var LinkBase $item */
        foreach ($links_col_x as $item)
        {
            $currentColumn .= self::GetLinkWithImage($item->Title, $item->Url);
        }
        return $currentColumn;
    }


}