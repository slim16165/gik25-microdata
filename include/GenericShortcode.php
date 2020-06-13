<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once("functions.php");
require_once("Shortcodes/microdata_telefono.php");
require_once("Shortcodes/microdata_prezzo.php");
require_once("Shortcodes/shortcode_vari.php");
require_once("Shortcodes/tinymce.php");
require_once("class/Schema/QuestionSchema.class.php");
require_once("ListOfPostsHelper.php");
require_once("class/Utility/OttimizzazioneNewspaper.php");


EnableErrorLogging();

//Avoid link and pages for tags of just one link
TagHelper::add_filter_DisableTagWith1Post();
QuestionSchema::AddShortcode();

add_action('wp_enqueue_scripts', 'load_css_single_pages', 1001);
add_action('admin_head', 'add_LogRocket');

add_action('after_setup_theme', 'wnd_default_image_settings');
//add_filter( 'xmlrpc_enabled', '__return_false' );

//Change the default image settings in the Backend
function wnd_default_image_settings()
{
    update_option('image_default_align', 'left');
    update_option('image_default_link_type', 'none');
    update_option('image_default_size', 'full-size');
}


function load_css_single_pages()
{
    if (is_single()) {
        $plugin_url = plugin_dir_url(__FILE__);
        wp_enqueue_style('css_single_pages', trailingslashit($plugin_url) . 'assets/css/revious-microdata.css', array());

        // Register the style like this for a plugin:
        //wp_register_style('revious-quotes-styles', plugins_url('/revious_microdata.css', __FILE__), array(), '1.7.5', 'all');
        // For either a plugin or a theme, you can then enqueue the style:
        //wp_enqueue_style('revious-quotes-styles');
    }
    //else if(is_category() || is_tag())
}

#region Script & CSS loading

function add_LogRocket()
{
    if ( defined( 'DOING_AJAX' ))
    {
        return;
    }

    $domain = ServerHelper::getSecondLevelDomain();
    //$domain = getSecondLevelDomain();


    $user = wp_get_current_user();
    echo /** @lang javascript */
    <<<TAG
<script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
<script>window.LogRocket && window.LogRocket.init('hdyhlv/si', {mergeIframes: true});
LogRocket.identify('{$user->user_login}-$domain', {
  name: '{$user->user_nicename}-$domain',
  email: '{$user->user_email}',
  website: '$domain'
});
</script>
TAG;

}

//define('DISALLOW_FILE_EDIT',true);

function wpse_297026_update_user_activity() {
    update_user_meta( get_current_user_id(), '<last_activity>', time() );
}
add_action( 'init', 'wpse_297026_update_user_activity' );



//	add_action('wp_head', 'add_Teads');

//	add_filter('the_posts', 'conditionally_add_scripts_and_styles'); // the_posts gets triggered before wp_head
//	function conditionally_add_scripts_and_styles($posts){
//		if (empty($posts)) return $posts;style-post-UnusedCSS+UnCSS.css
//
//	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
//	foreach ($posts as $post) {
//		if (stripos($post-&gt;post_content, '[code]') !== false) {
//			$shortcode_found = true; // bingo!
//			break;
//		}
//	}
//
//	if ($shortcode_found) {
//		// enqueue here
//		wp_enqueue_style('my-style', '/style.css');
//		wp_enqueue_script('my-script', '/script.js');
//	}
//
//	return $posts;
//}

#endregion

//	add_action('pre_get_posts', 'exclude_posts_from_home');
//	add_action('pre_get_posts', 'exclude_posts_from_feed');
//	add_action('pre_get_posts', 'exclude_posts_from_archives');
//	add_action('pre_get_posts', 'exclude_posts_from_search');

if(!function_exists("exclude_posts_from_everywhere"))
    exit("la funzione non è definita");

add_action('pre_get_posts', 'exclude_posts_from_everywhere');
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'exclude_posts_from_sitemap_by_post_ids', 10000);