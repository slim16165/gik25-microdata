<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}



function GetLinkWithImage(string $target_url, string $nome, string $commento = "", bool $removeIfSelf = false, bool $withImage = true)
{
    $target_url = ReplaceTargetUrlIfStaging($target_url);

    global $post, $MY_DEBUG; //il post corrente
    $current_post = $post;
    $result ="";

    if(!IsNullOrEmptyString($commento) && !MyString::Contains("$commento", "("))
        $commento = " ($commento)";

    $current_permalink = get_permalink( $current_post->ID );

    //Check if the current post is the same of the target_url
    $sameFile2 = strcmp($current_permalink, $target_url);
    $sameFile = $sameFile2 == 0;

////DEBUG
//    $val = <<<TAG
//<p>current_permalink: $current_permalink<br/>
//target_url: $target_url<br/>
//sameFile2: $sameFile2<br/>
//sameFile: $sameFile<br/>
//</p>
//TAG;
//    return $val;

    if($sameFile && $removeIfSelf)
    {
        if( $MY_DEBUG )
            return "sameFile && removeIfSelf";
        else
            return "";
    }

    $target_postid = url_to_postid($target_url);

    if ($target_postid == 0)
    {
        if( $MY_DEBUG)
            return "target_postid == 0";
        else
            return "";
    }

    $target_post = get_post($target_postid);

    if( $MY_DEBUG )
        $result.="259-";

    if ($target_post->post_status === "publish")
    {
        #region debug
        if( $MY_DEBUG)
            $result.="266-";
        #endregion

        if($withImage)
            $result.= GetTemplateWithThumbnail($target_url, $nome, $commento, $removeIfSelf, $target_post, $sameFile);
        else
            $result.= GetTemplateNoThumbnail($target_url, $nome, $commento, $removeIfSelf, $sameFile);
    }
    else
    {
        if( $MY_DEBUG)
            $result.="NON PUBBLICATO: $target_url";
        else
            $result.="<!-- NON PUBBLICATO -->";
    }

    return $result;
}


function GetTemplateNoThumbnail(string $target_url, string $nome, string $commento, bool $removeIfSelf, $sameFile): string
{
    if (!$sameFile) {
        if (IsNullOrEmptyString($commento))
            return "<li><a href=\"$target_url\">$nome</a></li>\n";
        else
            return "<li><a href=\"$target_url\">$nome</a> $commento</li>\n";
    }
    else if(!$removeIfSelf)
    {
        if (IsNullOrEmptyString($commento))
            return "<li>$nome (articolo corrente)</li>\n";
        else
            return "<li>$nome $commento (articolo corrente)</li>\n";
    }
    else
        return "";

}

function GetTemplateWithThumbnail(string $target_url, string $nome, string $commento, bool $removeIfSelf, $target_post, $sameFile): string
{
    $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');

    if ($sameFile) {
        $result = GetNoLinkTemplate("", $nome, $commento, $removeIfSelf);
    } else {
        $result = GetLinkTemplate($target_url, $nome, $commento, $featured_img_url);
    }
    return $result;
}

function GetNoLinkTemplate(string $target_url, string $nome, string $commento, string  $featured_img_url): string
{
    return <<<EOF
<li>
<div class="li-img">
	<img style="width=50px; height: 50px;" src="$featured_img_url" alt="$nome" />		
</div>
<div class="li-text">$nome ($commento)</div>
</li>\n
EOF;
}

function GetLinkTemplate($target_url, $nome, $commento, $featured_img_url): string
{
    return <<<EOF
<li>
<a href="$target_url">			
<div class="li-img">
	<img style="width=50px; height: 50px;" src="$featured_img_url" alt="$nome" />		
</div>
<div class="li-text">$nome </div>
</a>$commento</li>\n
EOF;
}

