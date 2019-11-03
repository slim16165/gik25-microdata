<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
* Date: 23/09/2019
* Time: 14:36
*/


	spl_autoload_register(function($className) {
//		include_once $_SERVER['DOCUMENT_ROOT'] . "/class/$className.class.php";
		require_once "class/HtmlHelper.class.php";
		require_once "class/LowLevelShortcode.class.php";
		require_once "class/MyString.class.php";
		require_once "class/ServerHelper.class.php";
		require_once "class/shortcode-wpautop-control.php";
		require_once "class/ShortCodeHelper.class.php";
		require_once "class/Schema/QuestionSchema.class.php";
	});

	function IsNullOrEmptyString($str){
		return (!isset($str) || trim($str) === '');
	}

    function CheckJsonError(string $json): string
{
    switch (json_last_error())
    {
        case JSON_ERROR_NONE:
            return "";
        case JSON_ERROR_DEPTH:
            return " - Maximum stack depth exceeded\n$json";
            break;
        case JSON_ERROR_STATE_MISMATCH:
			return " - Underflow or the modes mismatch\n$json";
            break;
        case JSON_ERROR_CTRL_CHAR:
			return " - Unexpected control character found\n$json";
            break;
        case JSON_ERROR_SYNTAX:
			return " - Syntax error, malformed JSON\n$json";
            break;
        case JSON_ERROR_UTF8:
			return " - Malformed UTF-8 characters, possibly incorrectly encoded\n$json";
            break;
        default:
			return " - Unknown error\n$json";
            break;
    }
}


	function EnableErrorLogging()
	{
		global $EnableErrorLogging_Called, $MY_DEBUG;

		if($EnableErrorLogging_Called == true)
		{
			return;
		}

		static $EnableErrorLogging_Called = true;

		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
		ini_set('error_reporting', E_ALL);
		define('MY_DEBUG', true);
		define('WP_DEBUG_DISPLAY', true);

		$MY_DEBUG = true;

	}

	function timer()
	{
		$starttime = microtime(true);
		/* do stuff here */

		$endtime = microtime(true);
		$timediff = $endtime - $starttime;

		var_dump($timediff); //in seconds
		exit;

	}

	function ReplaceTargetUrlIfStaging($target_url) : string
	{
		if (MyString::Contains(ServerHelper::getDomain(), "cloudwaysapps.com"))
		{
			$target_url = str_replace("www.chiecosa.it", "wordpress-217146-983380.cloudwaysapps.com", $target_url);
			$target_url = str_replace("www.nonsolodiete.it", "wordpress-217146-992662.cloudwaysapps.com", $target_url);
			$target_url = str_replace("www.superinformati.com", "wordpress-217146-1004348.cloudwaysapps.com", $target_url);
		}
		return $target_url;
	}

	function getSecondLevelDomain()
    {
        $domain = $_SERVER["SERVER_NAME"];

        if (preg_match('/\b([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}\b/im', $domain, $regs)) {
            $result = preg_replace('/(.+?)\.?/im', '\1', $regs[1]);
        } else {
            $result = $domain;
        }

        return $result;
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
		echo <<<TAG
<script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
<script>window.LogRocket && window.LogRocket.init('hdyhlv/si');
LogRocket.identify('{$user->user_login}-$domain', {
  name: '{$user->user_nicename}-$domain',
  email: '{$user->user_email}',
  website: '$domain'
});
</script>
TAG;

	}

	function add_Teads()
	{
		if (is_single())
		{ ?>
			<script type="text/javascript" class="teads" async="true" src="//a.teads.tv/page/109549/tag"></script>

			<?php
		}
	}

	add_action('wp_head', 'add_Teads');



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
		$ids = find_post_id_from_taxonomy();

		if ( $query->is_home() || $query->is_feed() || $query->is_archive() ) {
			$query->set('post__not_in', $ids);
		}
	}



	function find_post_id_from_taxonomy()
	{
		global $wpdb;

		$sql = <<<TAG
SELECT wp_posts.ID
FROM wp_posts
INNER JOIN wp_term_relationships
  ON wp_term_relationships.object_id = wp_posts.ID
INNER JOIN wp_term_taxonomy
  ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
    AND wp_term_taxonomy.taxonomy = 'post_tag'
INNER JOIN wp_terms
  ON wp_terms.term_id = wp_term_taxonomy.term_id
    AND wp_terms.name = 'OT'
WHERE wp_posts.post_type = 'post'
  AND wp_posts.post_status = 'publish'
  AND wp_posts.post_parent = 0
TAG;

		$result = $wpdb->get_results( $sql);

		#region Imparare PHP

		//[ array_column() ] Return the values from a single column in the input array
		//Easy
		//		$ids = [];
		//		foreach ($values as $value)     {
		//			$ids[] = $value->ID    ;
		//		}

		#endregion

		$fn = function ($value) {
			return $value->ID;
		};
		$ids = array_map($fn, $result);

		return $ids;
	}


	function exclude_posts_from_sitemap_by_post_ids($alreadyExcluded)
	{
		$excludePostId = array_merge($alreadyExcluded, find_post_id_from_taxonomy());
		return $excludePostId;
	}




//	add_action('pre_get_posts', 'exclude_posts_from_home');
//	add_action('pre_get_posts', 'exclude_posts_from_feed');
//	add_action('pre_get_posts', 'exclude_posts_from_archives');
//	add_action('pre_get_posts', 'exclude_posts_from_search');
	add_action('pre_get_posts', 'exclude_posts_from_everywhere');
	add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'exclude_posts_from_sitemap_by_post_ids', 10000);