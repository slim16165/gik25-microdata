<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ListOfPostsHelperChild extends ListOfPostsHelper
{
    function __construct($removeIfSelf, $withImage, $linkSelf, $nColumns)
    {
        $this->removeIfSelf = $removeIfSelf;
        $this->linkSelf = $linkSelf;
        $this->withImage = $withImage;
        self::$nColumns = $nColumns;
    }

    public function GetPostsDataByTag(&$isSameFile, &$ShouldReturnNow, $tag = '')
    {
        global $MY_DEBUG;
        $ShouldReturnNow = "";
        $target_posts = array();

        //Check if the current post is the same of the target_url

        if ($isSameFile && $this->removeIfSelf) {
            if ($MY_DEBUG)
                $ShouldReturnNow = "sameFile && removeIfSelf";
            else
                $ShouldReturnNow = "";
        }
        //var_dump($tags);exit;
        if (!empty($tag)) {

            $target_postids = TagHelper::find_post_id_from_taxonomy($tag, 'post_tag');

            if (empty($target_postids)) {
                if ($MY_DEBUG)
                    $ShouldReturnNow = '<h5 style="color: red;">There are no posts tagged with \'' . $tag . '\'</h5>';
                else
                    $ShouldReturnNow = "";
            }

            foreach ($target_postids as $target_postid) {
                $target_post = get_post($target_postid);
                if ($target_post->post_status !== "publish") {
                    $ShouldReturnNow .= "NON PUBBLICATO: " . get_permalink($target_post->ID);
                }
                $target_posts[] = $target_post;
            }
            //var_dump($target_post);exit;
            return $target_posts;

        } else {
            return false;
        }
    }

    public function GetLinksWithImages(array $links_data)
    {
        $i = 0;
        $links_number = count($links_data);
        $n_links_per_column = ceil($links_number / self::$nColumns);
        $links_div_open = '<div class="list-of-posts-layout-' . self::$nColumns . '">';
        $links_div_close = '</div>';
        if (self::$nColumns == 1) {
            //use one column layout
            $links_html = '';

            foreach ($links_data as $k => $v) {
                $links_html .= $this->muk($v, $links_html);
            }

            $links_html = $links_div_open . '<ul>' . $links_html . '</ul>' . $links_div_close;

            return $links_html;
        } elseif (self::$nColumns == 2) {
            //use two column layout
            $links_html = '';
            $links_html_col_1 = '';

            foreach ($links_data as $k => $v) {
                $links_html .= $this->muk($v);

                $i++;

                if ($n_links_per_column == $i) {
                    //first col complete
                    $links_html_col_1 = '<ul>' . $links_html . '</ul>';
                    $links_html = '';
                }
            }

            $links_html_col_2 = '<ul>' . $links_html . '</ul>';
            $links_html = $links_html_col_1 . $links_html_col_2;

            $links_html = $links_div_open . $links_html . $links_div_close;

            return $links_html;
        }
    }

    public function GetLinksWithImagesByTag($tag)
    {
        $i = 0;
        $links_html = '';
        $links_div_open = '<div class="list-of-posts-layout-' . self::$nColumns . '">';
        $links_div_close = '</div>';

        $target_posts = self::GetPostsDataByTag($noLink, $ShouldReturnNow, $tag);

        $n_links_per_column = ceil(count($target_posts) / self::$nColumns);

        if ($ShouldReturnNow)
            return $ShouldReturnNow;

        //var_dump($this->withImage);exit;

        if (self::$nColumns == 1) {
            //use one column layout

            foreach ($target_posts as $target_post) {
                $target_url = get_the_permalink($target_post->ID);
                $nome = $target_post->post_title;
                $commento = '';
                if ($this->withImage)
                    $links_html .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
                else
                    $links_html .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);
            }

            $links_html = $links_div_open . '<ul>' . $links_html . '</ul>' . $links_div_close;

            return $links_html;
        } elseif (self::$nColumns == 2) {
            //use two column layout
            $links_html_col_1 = '';

            foreach ($target_posts as $target_post) {
                $target_url = get_the_permalink($target_post->ID);
                $nome = $target_post->post_title;
                $commento = '';
                if ($this->withImage)
                    $links_html .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
                else
                    $links_html .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);

                $i++;

                if ($n_links_per_column == $i) {
                    //first col complete
                    $links_html_col_1 = '<ul>' . $links_html . '</ul>';
                    $links_html = '';
                }
            }

            $links_html_col_2 = '<ul>' . $links_html . '</ul>';
            $links_html = $links_html_col_1 . $links_html_col_2;

            $links_html = $links_div_open . $links_html . $links_div_close;

            return $links_html;
        }
    }

    public function GetLinkWithImage(string $target_url, string $nome, string $commento = "")
    {
        $result = "";

        $target_post = self::GetPostData($target_url, $noLink, $ShouldReturnNow);

        if ($ShouldReturnNow)
            return $ShouldReturnNow;

        //In caso contrario il post è pubblicato
        if (!IsNullOrEmptyString($commento) && !MyString::Contains("$commento", "(")) {
            $commento = " ($commento)";
        }
        if ($this->withImage)
            $result .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
        else
            $result .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);

        return $result;
    }

    public static function GetTemplateWithThumbnail(string $target_url, string $anchorText, string $comment, $target_post, $noLink): string
    {

        $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');

        if (!$featured_img_url) {
            $featured_img_html = /** @lang HTML */
                '<img style="width=50px; height: 50px;" src="' . plugins_url() . '/gik25-microdata/assets/images/placeholder-200x200.png" alt="' . $anchorText . '" />';
        } else {
            $featured_img_html = /** @lang HTML */
                "<img style=\"width=50px; height: 50px;\" src=\"{$featured_img_url}\" alt=\"{$anchorText}\" />";
        }

        if ($noLink) {
            return <<<EOF
<li>
<div class="li-img">
    $featured_img_html
</div>
<div class="li-text">$anchorText ($comment)</div>
</li>\n
EOF;
        } else {
            return <<<EOF
<li>
<a href="$target_url">			
<div class="li-img">
    $featured_img_html		
</div>
<div class="li-text">$anchorText </div>
                    </a>$comment
                </li>\n
EOF;
        }
    }

    public function muk($v): string
    {
        if (isset($v['commento']))
            $links_html = $this->GetLinkWithImage($v['target_url'], $v['nome'], $v['commento']);
        else
            $links_html = $this->GetLinkWithImage($v['target_url'], $v['nome']);

        return $links_html;
    }
}