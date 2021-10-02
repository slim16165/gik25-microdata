<?php

//TODO: classe da completare.. ci sono cablature e casini vari

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

class ExcludePostFrom
{

}

function exclude_posts_from_home($query)
{
    if ($query->is_home() ) {
        $query->set('post__not_in', array(1737, 1718));
    }
}


function exclude_posts_from_feed($query)
{
    if ($query->is_feed() ) {
        $query->set('post__not_in', array(1737, 1718));
    }
}

function  exclude_posts_exclude_from_search($query)
{
    if ( $query->is_search() ) {
        $query->set('post__not_in', array(1737, 1718));
    }
}

function exclude_posts_from_archives($query)
{
    if ($query->is_archive())
    {
        $query->set('post__not_in', array(1737, 1718));
    }
}


/**
 * @param $query
 * Excludes posts with tag OT from sitemap, archives, homepage in the website
 */
function exclude_posts_from_everywhere($query)
{
    $ids = TagHelper::find_post_id_from_taxonomy("OT", 'post_tag');

    if ($query->is_home() || $query->is_feed() || $query->is_archive())
    {
        $query->set('post__not_in', $ids);
    }
}

/**
 * Adds to the list of posts to be excluded the posts with tag OT from sitemap, archives, homepage in the website
 */
function exclude_posts_from_sitemap_by_post_ids($alreadyExcluded): array
{
    $post_id_from_taxonomy = TagHelper::find_post_id_from_taxonomy("OT", 'post_tag');
    $excludePostId = array_merge($alreadyExcluded, $post_id_from_taxonomy);
    return $excludePostId;
}

function wpseo_exclude_from_sitemap_by_term_ids($alreadyExcluded)
{
    //Da implementare
    $excludePostId = array_merge($alreadyExcluded, TagHelper::find_post_id_from_taxonomy("OT", 'post_tag'));
    return $excludePostId;
}
