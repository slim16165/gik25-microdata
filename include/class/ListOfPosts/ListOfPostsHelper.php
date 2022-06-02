<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once '../../../vendor/autoload.php';

use ListOfPosts\LinkConfig;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Meta;

class ListOfPostsHelper
{

    private LinkConfig $linkConfig;

    function __construct($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle = '')
    {
        $this->linkConfig = new LinkConfig($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle);
    }

    /**
     * Check if the current post is the same of the target_url
     * @param string $target_url
     * @return bool
     */
    public static function IsTargetUrlSamePost(string $target_url): bool
    {
        global $post;
        $current_post = $post;
        $current_permalink = get_permalink($current_post->ID);
        $sameFile2 = strcmp($current_permalink, $target_url);
        $sameFile = $sameFile2 == 0;
        return $sameFile;
    }

    public static function ParseComment(string $commento): string
    {
        if (!IsNullOrEmptyString($commento) && !MyString::Contains("$commento", "("))
        {
            $commento = " ($commento)";
        }
        return $commento;
    }

    //Multicolumns
    public function GetLinksWithImagesPaginated(array $links_data) : string
    {
        $links_per_column_arr = Util::PaginateArray($links_data, $this->linkConfig->nColumns);

        $links_html = '';
        $cssDivClass = sprintf("list-of-posts-layout-%s", $this->linkConfig->nColumns);
        $links_html_per_col = array(); $col_ix = 0;
        foreach ($links_per_column_arr as $links_col_x)
        {
            $col_ix++;
            foreach ($links_col_x as $item)
            {
                $links_html_per_col[$col_ix].= self::GetLinkWithImage($item);
            }

            $links_html.= Html::div()->class($cssDivClass)->content($links_html_per_col[$col_ix])->close();
        }

        return $links_html;
    }

    public function GetLinkWithImage(string $target_url, string $nome, string $commento = "")
    {
        $result = "";

        $target_post = PostData::GetPostData($target_url, $noLink, $debugMsg, $this->linkConfig->removeIfSelf);

        if ($debugMsg)
            return $debugMsg;

        //In caso contrario il post è pubblicato
        $commento = self::ParseComment($commento);

        $result = $this->GetTemplate($target_url, $nome, $commento, $target_post, $noLink, $result);

        return $result;
    }

    public function GetTemplate(string $target_url, string $nome, string $commento, $target_post, $noLink, string $result): string
    {
        if ($this->linkConfig->withImage) //GetTemplateWithThumbnail per la classe child
            $result .= HtmlTemplate::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
        else
            $result .= HtmlTemplate::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);
        return $result;
    }
}