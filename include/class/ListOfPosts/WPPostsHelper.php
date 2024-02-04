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

        if ($removeIfSelf && $isSameFile)
        {
            do_action( 'qm/debug', 'Stesso file e rimuovi link' );
        }

        //Find the post id from the url
        /** @var int $target_postid */
        $target_postid = self::url_to_postid_cached($target_url, function($url) {return url_to_postid($url);});

        if ($target_postid == 0)
        {
            $msg1 = "This post does not exist, or it is on other domain, or URL is wrong (target_postid == 0)";
            $msg = "<h5 style=\"color: red;\">$msg1</h5>";
            $debugMsg = WPPostsHelper::MY_DEBUG ? $msg : "";
            do_action( 'qm/warning', $msg1 );
        }
        else
        {
            $target_post = self::get_post_cached($target_postid);

            if ($target_post->post_status !== "publish" )
            {
                $debugMsg = "NON PUBBLICATO: " . get_permalink($target_post->ID);
                do_action( 'qm/debug', $debugMsg );
            }
        }

        return [$target_post, $isSameFile, $debugMsg];
    }

    private static function url_to_postid_cached($url, callable $query_function)
    {
        // Identificatore univoco per la cache basato sull'URL
        $cache_key = 'unique_prefix_' . md5($url);
        $cache_group = 'gik25_microdata';

        // Prova a recuperare il risultato dalla cache
        $result = wp_cache_get($cache_key, $cache_group);

        if ($result === false) {
            // Se non presente in cache, esegui la query al database
            $result = $query_function($url);

            // Salva il risultato in cache per utilizzi futuri
            wp_cache_set($cache_key, $result, $cache_group, 604800); // 604800 = 1 settimana
        }

        return $result;
    }

    /**
     * Retrieves post data given a post ID or post object.
     *
     * See sanitize_post() for optional $filter values. Also, the parameter
     * `$post`, must be given as a variable, since it is passed by reference.
     *
     * @since 1.5.1
     *
     * @global WP_Post $post Global post object.
     *
     * @param int|WP_Post|null $post   Optional. Post ID or post object. `null`, `false`, `0` and other PHP falsey values
     *                                 return the current global post inside the loop. A numerically valid post ID that
     *                                 points to a non-existent post returns `null`. Defaults to global $post.
     * @param string           $output Optional. The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
     *                                 correspond to a WP_Post object, an associative array, or a numeric array,
     *                                 respectively. Default OBJECT.
     * @param string           $filter Optional. Type of filter to apply. Accepts 'raw', 'edit', 'db',
     *                                 or 'display'. Default 'raw'.
     * @return WP_Post|array|null Type corresponding to $output on success or null on failure.
     *                            When $output is OBJECT, a `WP_Post` instance is returned.
     */
    private static function get_post_cached($post_id)
    {
        // Identificatore univoco per la cache basato sull'URL
        $cache_key = 'post_id_' . $post_id;
        $cache_group = 'gik25_microdata';

        // Prova a recuperare il risultato dalla cache
        $result = wp_cache_get($cache_key, $cache_group);

        if ($result === false) {
            // Se non presente in cache, esegui la query al database
            $result = get_post($post_id);

            // Salva il risultato in cache per utilizzi futuri
            wp_cache_set($cache_key, $result, $cache_group, 604800); // 604800 = 1 settimana
        }

        return $result;
    }


    /**
     * Check if the current executing post is the same of the target_url
     * @param string $target_url
     * @return bool
     */
    public static function IsTargetUrlSamePost(string $target_url): bool
    {
        global $post;

        if (is_object($post)) {
            $current_post = $post;
            $current_permalink = get_permalink($current_post->ID);
            return strcmp($current_permalink, $target_url) == 0;
        }

        // Gestisci il caso in cui $post non sia un oggetto
        return false;
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