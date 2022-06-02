<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ListOfPostsHelper
{
    public $removeIfSelf;
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

    public function GetLinksWithImages(array $links_data): string
    {
        $links_html = '';

        foreach ($links_data as $k => $v)
        {
            $links_html .= $this->GetLinkWithImage1($v);
        }

        return $links_html;
    }

    //TODO probabilmente si può eliminare o rimpiazzare con una explode dell'array
    public function GetLinkWithImage1(array $v): string
    {
        if (isset($v['commento']))
            $links_html = $this->GetLinkWithImage($v['target_url'], $v['nome'], $v['commento']);
        else
            $links_html = $this->GetLinkWithImage($v['target_url'], $v['nome']);

        return $links_html;
    }

    public function GetLinkWithImage(string $target_url, string $nome, string $commento = "")
    {
        $result = "";

        $target_post = PostData::GetPostData($target_url, $noLink, $ShouldReturnNow, $this);

        if ($ShouldReturnNow)
            return $ShouldReturnNow;

        //In caso contrario il post è pubblicato
        if (!IsNullOrEmptyString($commento) && !MyString::Contains("$commento", "("))
        {
            $commento = " ($commento)";
        }

        if ($this->withImage) //GetTemplateWithThumbnail2 per la classe child
            $result .= ListOfLinksTemplate::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
        else
            $result .= ListOfLinksTemplate::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);

        return $result;
    }
}