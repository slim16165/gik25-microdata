<?php
if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

class ListOfPostsHelperChild extends ListOfPostsHelper
{
    function __construct($removeIfSelf, $withImage, $linkSelf, $nColumns)
    {
        parent::__construct($removeIfSelf, $withImage, $linkSelf, $nColumns);
    }

    public function GetLinksWithImagesByTag($tag)
    {
        $target_posts = PostData::GetPostsDataByTag($debugMsg, $tag);
        if ($debugMsg) return $debugMsg;

        /*$target_url = get_the_permalink($target_post->ID);
        $nome = $target_post->post_title;
        $commento = '';*/

        return parent::GetLinksWithImages($target_posts);
    }

    //Multicolumns
    public function GetLinksWithImages(array $links_data) : string
    {
        $i = 0;
        $links_number = count($links_data);
        $n_links_per_column = ceil($links_number / self::$nColumns);
        $links_div_open = '<div class="list-of-posts-layout-' . self::$nColumns . '">';
        $links_div_close = '</div>';
        $links_html = '';
        $links_html_col_1 = '';


        foreach ($links_data as $k => $v)
        {
            if (self::$nColumns == 1)
            {
                //one column layout
                $links_html .= $this->GetLinkWithImage1($v, $links_html);
            } elseif (self::$nColumns == 2)
            {
                //two column layout
                $links_html .= $this->GetLinkWithImage1($v);

                $i++;

                if ($n_links_per_column == $i)
                {
                    //first col complete
                    $links_html_col_1 = "<ul>{$links_html}</ul>";
                    $links_html = '';
                }
            }
        }
        if (self::$nColumns == 1)
        {
            $links_html = "{$links_div_open}<ul>{$links_html}</ul>{$links_div_close}";

        } elseif (self::$nColumns == 2)
        {
            $links_html_col_2 = "<ul>{$links_html}</ul>";
            $links_html = $links_html_col_1 . $links_html_col_2;

            $links_html = $links_div_open . $links_html . $links_div_close;
        }
        return $links_html;
    }


}