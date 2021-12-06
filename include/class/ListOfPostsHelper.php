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
    public static $listOfPostsStyle;


    function __construct($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle = '')
    {
        $this->removeIfSelf = $removeIfSelf;
        $this->withImage = $withImage;
        $this->linkSelf = $linkSelf;
        self::$listOfPostsStyle = $listOfPostsStyle;
    }

    // public function GetPostData(string &$target_url, &$isSameFile, &$ShouldReturnNow)
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

class ListOfPostsHelperChild extends ListOfPostsHelper
{

    function __construct($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle)
    {
        $this->removeIfSelf = $removeIfSelf;
        $this->linkSelf = $linkSelf;
        $this->withImage = $withImage;
        self::$listOfPostsStyle = $listOfPostsStyle;
    }

    public function GetPostsDataByTag(&$isSameFile, &$ShouldReturnNow, $tag = '')
    {
        //die('child GetPostData');
        // var_dump($isSameFile);
        // var_dump($ShouldReturnNow);

        //goto aaa;

        global $MY_DEBUG;
        $ShouldReturnNow = "";
        $target_posts = array();

        //Check if the current post is the same of the target_url
        //$isSameFile = self::IsSameFile($target_url);

        if ($isSameFile && $this->removeIfSelf)
        {
            if ($MY_DEBUG)
                $ShouldReturnNow = "sameFile && removeIfSelf";
            else
                $ShouldReturnNow = "";
        }
        //var_dump($tags);exit;
        if(!empty($tag)) {

            $target_postids = TagHelper::find_post_id_from_taxonomy($tag, 'post_tag');

            if(empty($target_postids)) {
                if ($MY_DEBUG)
                    $ShouldReturnNow = '<h5 style="color: red;">There are no posts tagged with \'' . $tag . '\'</h5>';
                else
                    $ShouldReturnNow = "";
            }

            foreach($target_postids as $target_postid) {
                $target_post = get_post($target_postid);
                if ($target_post->post_status !== "publish") {
                    $ShouldReturnNow .= "NON PUBBLICATO: " . get_permalink($target_post->ID);
                }
                $target_posts[] = $target_post;
            }
            //var_dump($target_post);exit;
            return $target_posts;

        } 
        else {
            return false;
        }

    }

    public function GetLinksWithImages(array $links_data)
    {
        $i = 0;
        $column_links_number = ceil( count($links_data) / self::$listOfPostsStyle );
        $links_div_open = '<div class="list-of-posts-layout-' . self::$listOfPostsStyle . '">';
        $links_div_close = '</div>';
        $links_ul_open = '<ul>';
        $links_ul_close = '</ul>';

        if(self::$listOfPostsStyle == 1) {
            //use one column layout
            $links_html = '';
    
            foreach($links_data as $k => $v) {
                if(isset($v['commento']))
                    $links_html .= $this->GetLinkWithImage($v['target_url'], $v['nome'], $v['commento']);
                else
                    $links_html .= $this->GetLinkWithImage($v['target_url'], $v['nome']);
            }
    
            $links_html = $links_div_open . $links_ul_open . $links_html . $links_ul_close . $links_div_close;
            return $links_html;
        }
        elseif(self::$listOfPostsStyle == 2) {
            //use two column layout
            $links_html = '';
            $links_html_col_1 = '';
            $links_html_col_2 = '';

            foreach($links_data as $k => $v) {
                if(isset($v['commento']))
                    $links_html .= $this->GetLinkWithImage($v['target_url'], $v['nome'], $v['commento']);
                else
                    $links_html .= $this->GetLinkWithImage($v['target_url'], $v['nome']);
    
                $i++;
    
                if($column_links_number == $i) {
                    //first col complete
                    $links_html_col_1 = $links_ul_open . $links_html . $links_ul_close;
                    $links_html = '';
                }
            }

            $links_html_col_2 = $links_ul_open . $links_html . $links_ul_close;
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
        if (!IsNullOrEmptyString($commento) && !MyString::Contains("$commento", "("))
            $commento = " ($commento)";

        //var_dump($this->withImage);exit;
        if ($this->withImage)
            $result .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
        else
            $result .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);

        return $result;
    }

    public static function GetTemplateWithThumbnail(string $target_url, string $anchorText, string $comment, $target_post, $noLink): string
    {

        $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');

        if(!$featured_img_url) {
            $featured_img_html = '<img style="width=50px; height: 50px;" src="' . plugins_url() . '/gik25-microdata/assets/images/placeholder-200x200.png" alt="' . $anchorText . '" />';
        }
        else {
            $featured_img_html = '<img style="width=50px; height: 50px;" src="' . $featured_img_url . '" alt="' . $anchorText . '" />';
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

    public function GetLinksWithImagesByTag($tag)
    {
        $i = 0;
        $links_html = '';
        //$column_links_number = ceil( count($links_data) / self::$listOfPostsStyle );
        $links_div_open = '<div class="list-of-posts-layout-' . self::$listOfPostsStyle . '">';
        $links_div_close = '</div>';
        $links_ul_open = '<ul>';
        $links_ul_close = '</ul>';

        //$result = "";

        $target_posts = self::GetPostsDataByTag($noLink, $ShouldReturnNow, $tag);

        $column_links_number = ceil( count($target_posts) / self::$listOfPostsStyle );

        if ($ShouldReturnNow)
            return $ShouldReturnNow;

        //var_dump($this->withImage);exit;

        if(self::$listOfPostsStyle == 1) {
            //use one column layout

            foreach($target_posts as $target_post) {
                $target_url = get_the_permalink($target_post->ID);
                $nome = $target_post->post_title;
                $commento = '';
                if ($this->withImage)
                    // $result .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
                    $links_html .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
                else
                    // $result .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);
                    $links_html .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);
            }
            
            $links_html = $links_div_open . $links_ul_open . $links_html . $links_ul_close . $links_div_close;

            return $links_html;
        }
        elseif(self::$listOfPostsStyle == 2) {
            //use two column layout
            $links_html_col_1 = '';
            $links_html_col_2 = '';

            foreach($target_posts as $target_post) {
                $target_url = get_the_permalink($target_post->ID);
                $nome = $target_post->post_title;
                $commento = '';
                if ($this->withImage)
                    // $result .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
                    $links_html .= self::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
                else
                    // $result .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);
                    $links_html .= self::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);

                $i++;
        
                if($column_links_number == $i) {
                    //first col complete
                    $links_html_col_1 = $links_ul_open . $links_html . $links_ul_close;
                    $links_html = '';
                }
            }

            $links_html_col_2 = $links_ul_open . $links_html . $links_ul_close;
            $links_html = $links_html_col_1 . $links_html_col_2;

            $links_html = $links_div_open . $links_html . $links_div_close;
    
            return $links_html;
        }

    }

}