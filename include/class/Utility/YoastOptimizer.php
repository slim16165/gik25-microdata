<?php
namespace include\class\Utility;

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
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) )
        {
            add_filter( 'wpseo_breadcrumb_links', array($this, 'yoast_seo_breadcrumb_append_link' ));
        }
    }

    public function yoast_seo_breadcrumb_append_link( $links )
    {
        global $post;
        if ( is_author() || is_page('contatti'))
        {
            $breadcrumb_item[] = [
                'url' => site_url( '/chi-siamo/' ),
                'text' => 'Chi siamo',
            ];

            array_splice( $links, 1, -2, $breadcrumb_item );
        }
//        else if (is_singular('post'))
//        {
//            //Taking the last element of the array
//            $x = $links[] = array_pop($links);
//            //array(3) { ["url"]=> string(42) "https://www.psicocultura.it/comunicazione/" ["text"]=> string(53) "Comunicazione: Definizione, Elementi, Assiomi e Stili" ["id"]=> int(11827) }
//            //$x["text"] =
//            //var_dump($post);
//            //exit();
//            //get_post_meta($post->ID, '_yoast_wpseo_focusk‌​w', true);
//
//            /** Method 2. Use Yoast's function. **/
//            $posttags = get_the_tags();
//            if ($posttags)
//            {
//                foreach( $posttags as $tag )
//                {
//                    echo WPSEO_Taxonomy_Meta::get_term_meta( $tag->term_id, 'post_tag', 'focuskw' );
//                }
//            }
//
//        }

        return $links;
    }
}


new YoastOptimizer();