<?php
namespace gik25microdata\ListOfPosts;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use gik25microdata\ListOfPosts\Types\LinkBaseExt;
use gik25microdata\Utility\MyString;
use Illuminate\Support\Collection;
use gik25microdata\ListOfPosts\Types\LinkBase;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\Ul;


class ListOfPostsRenderHelper //Diventerà LinkListPresenter
{
    private LinkConfig $linkConfig;

    /**
     * @param bool $removeIfSelf
     * @param bool $withImage
     * @param bool $linkSelf
     * @param string $listOfPostsStyle
     */
    function __construct(bool $removeIfSelf, bool $withImage, bool $linkSelf, string $listOfPostsStyle = '')
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
     * @param Collection $linksData
     * @return string
     */
    public function GetLinksWithImages(Collection $linksData): string
    {
        if($this->linkConfig->nColumns > 1)
            return $this->GetLinksWithImagesMulticolumn($linksData);
        else
            return $this->getLinksWithImagesCurrentColumn($linksData);
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
                            ->AddClass($cssDivClass)
                            ->content($currentColumn)
                            ->encode(false)
                            ->render();
            //$col_ix++;
        }

        return $links_html;
    }

    public function GetLinkWithImage(?LinkBaseExt $link): string
    {
        if($link == null || $link->Url == null)
            return "";

        $commento = self::ParseComment($link->Comment);

        if ($link->error)
        {
            //$link->post = null;
            return "";
        }

        if ($this->linkConfig->withImage) //GetTemplateWithThumbnail per la classe child
            $result = HtmlTemplate::GetTemplateWithThumbnail($link->Url, $link->Title, $commento, $link->post, $link->isSamepage);
        else
            $result = HtmlTemplate::GetTemplateNoThumbnail($link->Url, $link->Title, $commento, $link->isSamepage);

        return $result;
    }

    /**
     * @param Collection<LinkBase> $links_col_x
     * @return string
     */
    public function getLinksWithImagesCurrentColumn(Collection $links_col_x): string
    {
        $currentColumn = "";

        $links_col_x = WPPostsHelper::ReplaceTargetUrlIfStagingBulk($links_col_x);

        $urls = $links_col_x->map(function (LinkBase $link) : string { return $link->Url; })->toArray();


        // Ottiene un array di post con URL come chiave
        $url_to_post = WPPostsHelper::GetBulkPostDataCached($urls);
//        $url_to_post[$target_url] = [
//            'id' => $post_id,
//            'permalink' => get_permalink($post),
//            'post' => $post
//            'error' => ''
//        ];

        $current_permalink = WPPostsHelper::GetCurrentPostPermalink();
        // Crea un array di dati dei post con URL come chiave
        /** @var Collection<LinkBaseExt> $collectionExt */
        $collectionExt = $this->preparePostDataForThisPost($url_to_post, $links_col_x, $current_permalink);

        //html di una singola colonna
        //nota: non posso usare la collection, ha dentro gli url pre "ReplaceTargetUrlIfStagingBulk"
        /** @var LinkBaseExt $singlePostData */
        foreach ($collectionExt as $singlePostData)
        {
            if (isset($singlePostData->error) && !MyString::IsNullOrEmptyString($singlePostData->error)) {
                // Gestisci l'errore qui
                do_action('qm/warning', $singlePostData->error);
            } else {
                $currentColumn .= self::GetLinkWithImage($singlePostData);
            }
        }

        return $currentColumn;
    }

    public function RenderLinksAsHtml(Collection $linksData, string $title, string $ulClass): string {
        $result = Html::h3($title);
        $result .= Html::div()->addClass($ulClass)->open();
        $result .= Html::ul()->addClass($ulClass)->open();
        $result .= $this->GetLinksWithImages($linksData);
        $result .= Html::ul()->close();
        $result .= Html::div()->close();

        return $result;
    }

    public function RenderLinksAsHtml2(Collection $linksData, string $title, string $ulClass): string {
        $result = Html::h4($title);
        $result .= Html::ul()->addClass("$ulClass")->open();
        $result .= $this->getLinksWithImagesCurrentColumn($linksData);
        $result .= Ul::tag()->close();

        return $result;
    }

    /**
     * @param array $url_to_post
     * @param Collection<LinkBase> $links_col_x
     * @param string $current_permalink
     * @return Collection<LinkBaseExt>
     */
    public function preparePostDataForThisPost(array $url_to_post, Collection $links_col_x, string $current_permalink): Collection
    {
        $postsData = collect();

        foreach ($url_to_post as $target_url => $data)
        {
            $isSamepage = WPPostsHelper::IsTargetUrlSamePost($target_url, $current_permalink);
            $debugMsg = WPPostsHelper::getDebugMsg($this->linkConfig->removeIfSelf, $isSamepage, $data['post'], $target_url);

            // Trova l'oggetto LinkBase corrispondente nell'originale $links_col_x
            $originalLink = $links_col_x->firstWhere('Url', $target_url);

            // Crea un nuovo oggetto LinkBaseExt
            $link = new LinkBaseExt(
                $originalLink->Title,
                $originalLink->Url,
                $originalLink->Comment,
                $data['id'],
                $data['permalink'],
                $data['post'],
                $debugMsg,
                $isSamepage
            );

            $postsData->push($link);
        }

        return $postsData;
    }

}