<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Illuminate\Support\Collection;
use include\class\ListOfPosts\Types\LinkBase;
use ListOfPosts\LinkConfig;
use Yiisoft\Html\Html;
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
        /** @var Collection<LinkBase> $collection */
        $collection = Util::ConvertArrayToCollectionOfLinks($links_data);

        return $this->GetLinksWithImagesPaginated($collection);
    }


    /**
     * @param Collection<LinkBase> $links_data
     * @return string
     */
    public function GetLinksWithImagesPaginated(Collection $links_data) : string
    {
        //https://blog.jetbrains.com/phpstorm/2021/12/phpstorm-2021-3-release/#improved_support_for_doctrine_collections
        $links_per_column_arr = Util::PaginateArray($links_data, $this->linkConfig->nColumns);

        $links_html = '';
        $cssDivClass = sprintf("list-of-posts-layout-%s", $this->linkConfig->nColumns);
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
                            ->class($cssDivClass)
                            ->content($currentColumn)
                            ->encode(false)
                            ->render();
            $col_ix++;
        }

        return $links_html;
    }

    /**
     * @param string|null $target_url
     * @param string|null $nome
     * @param string|null $commento
     * @return mixed|string
     */
    public function GetLinkWithImage(?string $target_url, ?string $nome, ?string $commento = ""): mixed
    {
        $result = "";
        if($target_url == null)
            return "";

        list($target_post, $noLink, $debugMsg) = WPPostsHelper::GetPostData($target_url, $this->linkConfig->removeIfSelf);

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



}