<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once("functions.php");
// require_once("Shortcodes/microdata_boxinformativo_perfectpullquote.php");//migrated from gik25-quotes.php
// require_once("Shortcodes/microdata_prezzo_telefono.php");

require_once("Shortcodes/microdata_boxinformativo.php");//migrated from gik25-quotes.php, converted to PHP class
require_once("Shortcodes/microdata_flexlist.php");
require_once("Shortcodes/microdata_flipbox.php");
require_once("Shortcodes/microdata_perfectpullquote.php");//migrated from gik25-quotes.php, converted to PHP class
require_once("Shortcodes/microdata_prezzo.php");
require_once("Shortcodes/microdata_quote.php");
require_once("Shortcodes/microdata_slidingbox.php");
require_once("Shortcodes/microdata_telefono.php");
require_once("Shortcodes/microdata_youtube.php");

//require_once("Shortcodes/microdata_wp_users.php");//temp for testing
// require_once("Shortcodes/shortcode_vari.php");

// require_once("Shortcodes/tinymce.php");
require_once("class/Schema/QuestionSchema.class.php");
require_once("ListOfPostsHelper.php");
require_once("class/Utility/OttimizzazioneNewspaper.php");


EnableErrorLogging();

//Avoid link and pages for tags of just one link
TagHelper::add_filter_DisableTagWith1Post();
QuestionSchema::AddShortcode();
OptimizationHelper::ConditionalLoadCssOnPosts();
OptimizationHelper::ConditionalLoadCssJsOnPostsWhichContainEnabledShortcodes();


add_action('admin_head', 'add_LogRocket');

// add_action('after_setup_theme', 'wnd_default_image_settings');
//add_filter( 'xmlrpc_enabled', '__return_false' );

//Change the default image settings in the Backend
function wnd_default_image_settings()
{
    // update_option('image_default_align', 'left');
    update_option('image_default_align', 'right');
    update_option('image_default_link_type', 'none');
    update_option('image_default_size', 'full-size');
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