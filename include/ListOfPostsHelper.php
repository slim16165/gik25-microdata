<?php


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ListOfPostsHelper
{
    private bool $linkSelf;
    private bool $removeIfSelf;
    private bool $withImage;


    function __construct($removeIfSelf, $withImage, $linkSelf)
    {
        $this->removeIfSelf = $removeIfSelf;
        $this->linkSelf = $linkSelf;
        $this->withImage = $withImage;
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
                $ShouldReturnNow = "target_postid == 0";
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
        if (!IsNullOrEmptyString($commento) && !MyString::Contains("$commento", "("))
            $commento = " ($commento)";

        if ($this->withImage)
            $result .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
        else
            $result .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);

        return $result;
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

        if ($noLink) {
            return <<<EOF
<li>
<div class="li-img">
	<img style="width=50px; height: 50px;" src="$featured_img_url" alt="$anchorText" />		
</div>
<div class="li-text">$anchorText ($comment)</div>
</li>\n
EOF;
        } else {
            return <<<EOF
<li>
<a href="$target_url">			
<div class="li-img">
	<img style="width=50px; height: 50px;" src="$featured_img_url" alt="$anchorText" />		
</div>
<div class="li-text">$anchorText </div>
</a>$comment</li>\n
EOF;
        }
    }

}