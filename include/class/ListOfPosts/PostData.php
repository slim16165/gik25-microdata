<?php

class PostData
{
    public static function GetPostsDataByTag(&$debugMsg, $tag = '')
    {
        global $MY_DEBUG;
        $debugMsg = "";        

        if (empty($tag)) return false;

        $target_postids = TagHelper::find_post_id_from_taxonomy($tag, 'post_tag');
        
        #region Debug
        if (empty($target_postids))
        {
            $debugMsg = $MY_DEBUG ? '<h5 style="color: red;">There are no posts tagged with \'' . $tag . '\'</h5>' : "";
        }

        #endregion

        $target_posts = array();
        
        foreach ($target_postids as $targetpostId)
        {
            list($target_post, $debugMsg) = self::GetPostIfPublished($targetpostId);

            $target_posts[] = $target_post;
        }
        
        return $target_posts;
    }

    /**
     * Retrieves post data given a post ID or post object.
     *
     * @global WP_Post $post Global post object.
     *
     * @return WP_Post|array|null Type corresponding to $output on success or null on failure.
     *                            When $output is OBJECT, a `WP_Post` instance is returned.
     */
    public static function GetPostData(string &$target_url, &$isSameFile, &$debugMsg, bool $removeIfSelf)
    {
        //se siamo su staging modifica il target_url
        list($target_url, $debugMsg, $isSameFile) = self::HandleExtraInfo($target_url, $removeIfSelf);

        list($debugMsg, $target_post) = self::GetPostDataFromUrl($target_url);

        return $target_post;
    }

    public static function GetPostIfPublished($target_postid): array
    {
        $debugMsg = "";
        $target_post = get_post($target_postid);

        if ($target_post->post_status !== "publish")
            $debugMsg .= "NON PUBBLICATO: " . get_permalink($target_post->ID);

        return array($target_post, $debugMsg);
    }

    public static function GetPostDataFromUrl($target_url): array
    {
        global $MY_DEBUG;

        //Find the post id from the url
        $target_postid = url_to_postid($target_url);

        if ($target_postid == 0)
        {
            $debugMsg = $MY_DEBUG ? '<h5 style="color: red;">This post does not exist, or it is on other domain, or URL is wrong (target_postid == 0)</h5>' : "";
        }

        list($target_post, $debugMsg) = self::GetPostIfPublished($target_postid);

        return array($debugMsg, $target_post);
    }

    public static function HandleExtraInfo(string $target_url, bool $removeIfSelf): array
    {
        global $MY_DEBUG;
        $debugMsg = "";

        $target_url = ReplaceTargetUrlIfStaging($target_url);

        //Check if the current post is the same of the target_url
        $isSameFile = ListOfPostsHelper::IsSameFile($target_url);

        if ($isSameFile && $removeIfSelf)
        {
            $debugMsg = $MY_DEBUG ? "sameFile && removeIfSelf" : "";
        }
        return array($target_url, $debugMsg, $isSameFile);
    }
}