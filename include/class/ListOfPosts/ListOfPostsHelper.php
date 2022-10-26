<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Illuminate\Support\Collection;
use include\class\ListOfPosts\Types\LinkBase;
use ListOfPosts\LinkConfig;
use Yiisoft\Html\Html;
<<<<<<< HEAD
=======
use Yiisoft\Html\Tag\Div;
>>>>>>> 1bbc4b5 (OOP, Composer of lists of posts)
use function PHPUnit\Framework\throwException;


class ListOfPostsHelper
{
    private LinkConfig $linkConfig;

    function __construct($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle = '')
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

    public function GetLinksWithImages(array $links_data): string
    {
<<<<<<< HEAD
        /** @var Collection<LinkBase> $collection */
        $collection = Util::ConvertArrayToCollectionOfLinks($links_data);

        return $this->GetLinksWithImagesPaginated($collection);
=======
        $collection = Util::ConvertArrayToCollectionOfLinks($links_data);

        if($this->linkConfig->nColumns > 1)
            return $this->GetLinksWithImagesMulticolumn($collection);
        else
            return $this->getLinksWithImagesCurrentColumn($collection);
>>>>>>> 1bbc4b5 (OOP, Composer of lists of posts)
    }


    /**
     * @param Collection<LinkBase> $links_data
     * @return string
     */
<<<<<<< HEAD
    public function GetLinksWithImagesPaginated(Collection $links_data) : string
=======
    public function GetLinksWithImagesMulticolumn(Collection $links_data) : string
>>>>>>> 1bbc4b5 (OOP, Composer of lists of posts)
    {
        //https://blog.jetbrains.com/phpstorm/2021/12/phpstorm-2021-3-release/#improved_support_for_doctrine_collections
        $links_per_column_arr = Util::PaginateArray($links_data, $this->linkConfig->nColumns);

        $links_html = '';
        $cssDivClass = sprintf("list-of-posts-layout-%s", $this->linkConfig->nColumns);
<<<<<<< HEAD
        $links_html_per_col = array(); $col_ix = 0;
        foreach ($links_per_column_arr as $links_col_x)
        {
            $currentColumn = $links_html_per_col[$col_ix];
            $currentColumn = "";

            //html di una singola colonna
            /** @var LinkBase $item */
            foreach ($links_col_x as $item)
            {
                $currentColumn .= self::GetLinkWithImage($item->Title, $item->Url);
            }

            //Genero l'html di tutte le colonne
            $links_html.= \Yiisoft\Html\Tag\Div::tag()
=======
        //$links_html_per_col = array(); $col_ix = 0;
        foreach ($links_per_column_arr as $links_col_x)
        {
            //$currentColumn = $links_html_per_col[$col_ix];
            $currentColumn = $this->getLinksWithImagesCurrentColumn($links_col_x);

            //Genero l'html di tutte le colonne
            $links_html.= Div::tag()
>>>>>>> 1bbc4b5 (OOP, Composer of lists of posts)
                            ->class($cssDivClass)
                            ->content($currentColumn)
                            ->encode(false)
                            ->render();
<<<<<<< HEAD
            $col_ix++;
=======
            //$col_ix++;
>>>>>>> 1bbc4b5 (OOP, Composer of lists of posts)
        }

        return $links_html;
    }

    /**
     * @param string|null $target_url
     * @param string|null $nome
     * @param string|null $commento
<<<<<<< HEAD
     * @return mixed|string
     */
    public function GetLinkWithImage(?string $target_url, ?string $nome, ?string $commento = ""): mixed
    {
        $result = "";
=======
     * @return string
     */
    public function GetLinkWithImage(?string $target_url, ?string $nome, ?string $commento = ""): string
    {
>>>>>>> 1bbc4b5 (OOP, Composer of lists of posts)
        if($target_url == null)
            return "";

        list($target_post, $noLink, $debugMsg) = WPPostsHelper::GetPostData($target_url, $this->linkConfig->removeIfSelf);

<<<<<<< HEAD
        if ($debugMsg)
        {
            return $this->GetTemplate($target_url, $nome, $commento, null, $noLink);
        }

        //In caso contrario il post è pubblicato
        $commento = self::ParseComment($commento);

        $result = $this->GetTemplate($target_url, $nome, $commento, $target_post, $noLink);

        return $result;
    }

    public function GetTemplate(string $target_url, string $nome, string $commento, $target_post, $noLink): string
    {
        $result = "";
        if ($this->linkConfig->withImage) //GetTemplateWithThumbnail per la classe child
            $result .= HtmlTemplate::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
        else
            $result .= HtmlTemplate::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);
        return $result;
    }

=======
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
>>>>>>> 1bbc4b5 (OOP, Composer of lists of posts)


}