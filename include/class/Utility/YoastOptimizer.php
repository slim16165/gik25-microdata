<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class YoastOptimizer
{
    /*
     * Add a link to the Yoast SEO breadcrumbs
     * Credit: https://wordpress.stackexchange.com/users/8495/rjb
     * Last Tested: Nov 30 2018 using Yoast SEO 9.2 on WordPress 4.9.8
     *********
     * DIFFERENT POST TYPES
     * Post: Change 123456 to the post ID
     * Page: Change is_single to is_page and 123456 to the page ID
     * Custom Post Type: Change is_single to is_singular and 123456 to the 'post_type_slug'
        Example: is_singular( 'cpt_slug' )
     *********
     * MULTIPLE ITEMS
     * Multiple of the same type can use an array.
        Example: is_single( array( 123456, 234567, 345678 ) )
     * Multiple of different types can repeat the if statement
     */

    public function __construct()
    {
        if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) )
        {
            add_filter( 'wpseo_breadcrumb_links', array($this, 'yoast_seo_breadcrumb_append_link' ));
        }
    }

    public function yoast_seo_breadcrumb_append_link( $links )
    {
        if ( is_author() || is_page('contatti'))
        {
            $breadcrumb_item[] = [
                'url' => site_url( '/chi-siamo/' ),
                'text' => 'Chi siamo',
            ];

            array_splice( $links, 1, -2, $breadcrumb_item );
        }

        return $links;
    }
}


new YoastOptimizer();