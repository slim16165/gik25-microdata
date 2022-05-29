<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ListOfPostsHelper
{
    protected $removeIfSelf;
    // private $withImage;
    public $withImage;
    protected $linkSelf;
    public static $nColumns;


    function __construct($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle = '')
    {
        $this->removeIfSelf = $removeIfSelf;
        $this->withImage = $withImage;
        $this->linkSelf = $linkSelf;
        self::$nColumns = $listOfPostsStyle;
    }

    public function GetPostData(string &$target_url, &$isSameFile, &$ShouldReturnNow)
    {
        $target_url = ReplaceTargetUrlIfStaging($target_url);

        global $MY_DEBUG;
        $ShouldReturnNow = "";

        //Check if the current post is the same of the target_url
        $isSameFile = self::IsSameFile($target_url);

        if ($isSameFile && $this->removeIfSelf)
        {
            if ($MY_DEBUG)
                $ShouldReturnNow = "sameFile && removeIfSelf";
            else
                $ShouldReturnNow = "";
        }

        $target_postid = url_to_postid($target_url);

        if ($target_postid == 0) {
            if ($MY_DEBUG)
                $ShouldReturnNow = '<h5 style="color: red;">This post does not exist, or it is on other domain, or URL is wrong (target_postid == 0)</h5>';
            else
                $ShouldReturnNow = "";
        }

        $target_post = get_post($target_postid);

        if ($target_post->post_status !== "publish") {
            $ShouldReturnNow .= "NON PUBBLICATO: $target_url";
        }

        return $target_post;
    }

    /**
     * Check if the current post is the same of the target_url
     * @param string $target_url
     * @return bool
     */
    public static function IsSameFile(string $target_url): bool
    {
        global $post;
        $current_post = $post;
        $current_permalink = get_permalink($current_post->ID);
        $sameFile2 = strcmp($current_permalink, $target_url);
        $sameFile = $sameFile2 == 0;
        return $sameFile;
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

    public function GetLinksWithImages(array $links_data)
    {

        $links_html = '';

        foreach ($links_data as $k => $v) {
            if (isset($v['commento']))
                $links_html .= $this->GetLinkWithImage($v['target_url'], $v['nome'], $v['commento']);
            else
                $links_html .= $this->GetLinkWithImage($v['target_url'], $v['nome']);
        }

        return $links_html;

    }

    public static function GetTemplateNoThumbnail(string $target_url, string $nome, string $commento, $noLink): string
    {
        if ($noLink) {

            if (IsNullOrEmptyString($commento))
                return "<li>$nome (articolo corrente)</li>\n";
            else
                return "<li>$nome $commento (articolo corrente)</li>\n";
        } else {
            if (IsNullOrEmptyString($commento))
                return "<li><a href=\"$target_url\">$nome</a></li>\n";
            else
                return "<li><a href=\"$target_url\">$nome</a> $commento</li>\n";
        }
    }

    public static function GetTemplateWithThumbnail(string $target_url, string $anchorText, string $comment, $target_post, $noLink): string
    {
        $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');

        if(!$featured_img_url) {
            $featured_img_html =  /** @lang HTML */
                '<img width="50" height="50" style="width=50px; height: 50px;" src="' . plugins_url() . '/gik25-microdata/assets/images/placeholder-200x200.png" alt="' . $anchorText . '" />';
        }
        else {
            $featured_img_html = /** @lang HTML */
                '<img width="50" height="50" style="width=50px; height: 50px;" src="' . $featured_img_url . '" alt="' . $anchorText . '" />';
        }

        if ($noLink) {
            return <<<EOF
<li>
<div class="li-img">
    $featured_img_html
</div>
<div class="li-text">$anchorText $comment</div>
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
</a>$comment</li>\n
EOF;
        }

    }

}