<?php

class PostData
{
    public static function GetPostsDataByTag(&$isSameFile, &$ShouldReturnNow, $tag = '', ListOfPostsHelperChild $instance)
    {
        global $MY_DEBUG;
        $ShouldReturnNow = "";
        $target_posts = array();

        //Check if the current post is the same of the target_url

        if ($isSameFile && $instance->removeIfSelf)
        {
            if ($MY_DEBUG)
                $ShouldReturnNow = "sameFile && removeIfSelf";
            else
                $ShouldReturnNow = "";
        }
        //var_dump($tags);exit;
        if (!empty($tag))
        {

            $target_postids = TagHelper::find_post_id_from_taxonomy($tag, 'post_tag');

            if (empty($target_postids))
            {
                if ($MY_DEBUG)
                    $ShouldReturnNow = '<h5 style="color: red;">There are no posts tagged with \'' . $tag . '\'</h5>';
                else
                    $ShouldReturnNow = "";
            }

            foreach ($target_postids as $target_postid)
            {
                $target_post = get_post($target_postid);
                if ($instance->post_status !== "publish")
                {
                    $ShouldReturnNow .= "NON PUBBLICATO: " . get_permalink($instance->ID);
                }
                $target_posts[] = $target_post;
            }
            //var_dump($target_post);exit;
            return $target_posts;

        } else
        {
            return false;
        }
    }

    public static function GetPostData(string &$target_url, &$isSameFile, &$ShouldReturnNow, ListOfPostsHelper $instance)
    {
        $target_url = ReplaceTargetUrlIfStaging($target_url);

        global $MY_DEBUG;
        $ShouldReturnNow = "";

        //Check if the current post is the same of the target_url
        $isSameFile = ListOfPostsHelper::IsSameFile($target_url);

        if ($isSameFile && $instance->removeIfSelf)
        {
            if ($MY_DEBUG)
                $ShouldReturnNow = "sameFile && removeIfSelf";
            else
                $ShouldReturnNow = "";
        }

        $target_postid = url_to_postid($target_url);

        if ($target_postid == 0)
        {
            if ($MY_DEBUG)
                $ShouldReturnNow = '<h5 style="color: red;">This post does not exist, or it is on other domain, or URL is wrong (target_postid == 0)</h5>';
            else
                $ShouldReturnNow = "";
        }

        $target_post = get_post($target_postid);

        if ($instance->post_status !== "publish")
        {
            $ShouldReturnNow .= "NON PUBBLICATO: $target_url";
        }

        return $target_post;
    }
}