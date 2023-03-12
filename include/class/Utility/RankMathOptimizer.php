<?php
namespace gik25microdata\Utility;

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

class RankMathOptimizer
{
    public function __construct()
    {
//        if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) )
//        {
//            add_filter( 'wpseo_breadcrumb_links', array($this, 'yoast_seo_breadcrumb_append_link' ));
//        }

        /**
         * Allows filtering of the robots meta data.
         *
         * @param array $robots The meta robots directives.
         */
        add_filter('rank_math/frontend/robots', function ($robots) {
            global $post;
            // filter for only a specific category
            if (is_tag())
            {
                $robots['index'] = "noindex";
                $robots['follow'] = "nofollow";
                return $robots;
            }
            return $robots;
        });

    }
}

//new RankMathOptimizer();
