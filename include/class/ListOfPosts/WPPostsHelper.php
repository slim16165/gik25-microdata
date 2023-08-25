<?php
namespace gik25microdata\ListOfPosts;
use gik25microdata\Utility\HtmlHelper;
use gik25microdata\Utility\MyString;
use gik25microdata\Utility\ServerHelper;

class WPPostsHelper
{
    const MY_DEBUG = true;

    public static function GetPostData(string &$target_url, bool $removeIfSelf): array
    {
        //se siamo su staging modifica il target_url
        $target_url = self::ReplaceTargetUrlIfStaging($target_url);
        $target_post = null;
        $debugMsg = "";
        //Check if the current post is the same of the target_url
        $isSameFile = self::IsTargetUrlSamePost($target_url);

        if ($isSameFile && $removeIfSelf)
        {
            do_action( 'qm/debug', 'Stesso file e rimuovi link' );
        }

        //Find the post id from the url
        $target_postid = url_to_postid($target_url);

        if ($target_postid == 0)
        {
            $msg1 = "This post does not exist, or it is on other domain, or URL is wrong (target_postid == 0)";
            $msg = "<h5 style=\"color: red;\">$msg1</h5>";
            $debugMsg = WPPostsHelper::MY_DEBUG ? $msg : "";
            do_action( 'qm/warning', $msg1 );
        }
        else
        {
            $target_post = get_post($target_postid);

            if ($target_post->post_status !== "publish" )
            {
                $debugMsg = "NON PUBBLICATO: " . get_permalink($target_post->ID);
                do_action( 'qm/debug', $debugMsg );
            }
        }

        return [$target_post, $isSameFile, $debugMsg];
    }

    /**
     * Check if the current executing post is the same of the target_url
     * @param string $target_url
     * @return bool
     */
    public static function IsTargetUrlSamePost(string $target_url): bool
    {
        global $post;
        $current_post = $post;
        $current_permalink = get_permalink($current_post->ID);

        return strcmp($current_permalink, $target_url) == 0;
    }

    /**
     * The provided url should be an article of this WordPress installation. This method is used to test on staging environments
     */
    public static function ReplaceTargetUrlIfStaging(string $target_url): string
    {
        //$path = parse_url($target_url, PHP_URL_PATH);
        $domain = ServerHelper::getDomain();

        //return $domain .$path;

        $executingOnStaging = MyString::Contains($domain, "cloudwaysapps.com") || MyString::Contains($domain, ".local");
        $linkingToStaging = MyString::Contains($target_url, "cloudwaysapps.com") || MyString::Contains($target_url, ".local");

        if ($executingOnStaging & !$linkingToStaging)
        {
            //cioè solo se sono un server di staging ma l'url lincato non ne tiene conto, ho un problema
            $target_url = HtmlHelper::ReplaceDomain($target_url, $domain);
        }
        return $target_url;
    }
}