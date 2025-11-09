<?php
namespace gik25microdata\WPSettings;

use gik25microdata\Utility\TagHelper;

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

/**
 * Classe per escludere post da query WordPress
 * 
 * TODO: classe da completare.. ci sono cablature e casini vari
 */
class ExcludePostFrom
{

    function exclude_posts_from_home($query): void
    {
        if ($query->is_home() ) {
            $query->set('post__not_in', array(1737, 1718));
        }
    }

    function exclude_posts_from_feed($query): void
    {
        if ($query->is_feed() ) {
            $query->set('post__not_in', array(1737, 1718));
        }
    }

    function  exclude_posts_exclude_from_search($query): void
    {
        if ( $query->is_search() ) {
            $query->set('post__not_in', array(1737, 1718));
        }
    }

    function exclude_posts_from_archives($query): void
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
    function exclude_posts_from_everywhere($query): void
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

    function wpseo_exclude_from_sitemap_by_term_ids($alreadyExcluded): array
    {
        //Da implementare
        $excludePostId = array_merge($alreadyExcluded, TagHelper::find_post_id_from_taxonomy("OT", 'post_tag'));
        return $excludePostId;
    }


#region Script & CSS loading

//	add_action('pre_get_posts', 'exclude_posts_from_home');
//	add_action('pre_get_posts', 'exclude_posts_from_feed');
//	add_action('pre_get_posts', 'exclude_posts_from_archives');
//	add_action('pre_get_posts', 'exclude_posts_from_search');
//add_action( 'pre_get_posts', 'exclude_posts_from_everywhere');
//add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'exclude_posts_from_sitemap_by_post_ids', 10000);

//if(!function_exists("exclude_posts_from_everywhere"))
//    exit("la funzione non è definita");



}

