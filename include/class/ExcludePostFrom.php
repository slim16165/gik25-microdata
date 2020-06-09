<?php


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
    if ( $query->is_archive() ) {
        $query->set('post__not_in', array(1737, 1718));
    }
}

function exclude_posts_from_everywhere($query)
{
    $ids = TagHelper::find_post_id_from_taxonomy("OT", 'post_tag');

    if ( $query->is_home() || $query->is_feed() || $query->is_archive() ) {
        $query->set('post__not_in', $ids);
    }
}

function exclude_posts_from_sitemap_by_post_ids($alreadyExcluded)
{
    $excludePostId = array_merge($alreadyExcluded, TagHelper::find_post_id_from_taxonomy("OT", 'post_tag'));
    return $excludePostId;
}

function wpseo_exclude_from_sitemap_by_term_ids($alreadyExcluded)
{
    //Da implementare
    $excludePostId = array_merge($alreadyExcluded, TagHelper::find_post_id_from_taxonomy("OT", 'post_tag'));
    return $excludePostId;
}
