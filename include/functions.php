<?php

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

spl_autoload_register(function ($className)
{
//		include_once $_SERVER['DOCUMENT_ROOT'] . "/class/$className.class.php";
    require_once "class/Utility/HtmlHelper.class.php";
//		require_once "class/LowLevelShortcode.class.php";
    require_once "class/Utility/MyString.class.php";
//		require_once("packets/highlight/Highlight/Highlighter.php");
    require_once "class/Utility/ServerHelper.class.php";
    require_once "class/Schema/QuestionSchema.class.php";
    require_once "class/TagHelper.php";
    require_once "class/ColorWidget.php";
    require_once "class/Utility/OptimizationHelper.php";
});
require_once "class/ExcludePostFrom.php";

function IsNullOrEmptyString($str)
{
    return (!isset($str) || trim($str) === '');
}

function CheckJsonError(string $json): string
{
    $json_last_error = json_last_error();

    switch ($json_last_error)
    {
        //Nessun errore
        case JSON_ERROR_NONE:
            return "";
            break;

        //varie casistiche di errori
        case JSON_ERROR_DEPTH:
            $errormessage = "Maximum stack depth exceeded";
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $errormessage = "Underflow or the modes mismatch";
            break;
        case JSON_ERROR_CTRL_CHAR:
            $errormessage = "Unexpected control character found";
            break;
        case JSON_ERROR_SYNTAX:
            $errormessage = "Syntax error, malformed JSON";
            break;
        case JSON_ERROR_UTF8:
            $errormessage = "Malformed UTF-8 characters, possibly incorrectly encoded";
            break;
        default:
            $errormessage = "Unknown error";
            break;
    }

//		https://github.com/scrivo/highlight.php


    $errormessage = "<pre>$errormessage<br/>
		[$json]
		<a href='https://codebeautify.org/jsonviewer?input=[$json]'>Validator</a>
		</pre>";
    // Instantiate the Highlighter.
    //$hl = new \Highlight\Highlighter();

//		try {
//			// Highlight some code.
//			$highlighted = $hl->highlight('json', $code);
//
//			echo "<pre><code class=\"hljs {$highlighted->language}\">";
//			echo $highlighted->value;
//			echo "</code></pre>";
//		}
//		catch (DomainException $e) {
//			// This is thrown if the specified language does not exist
//
//			echo "<pre><code>";
//			echo $code;
//			echo "</code></pre>";
//		}

    return $errormessage;
}


function EnableErrorLogging()
{
    global $EnableErrorLogging_Called, $MY_DEBUG;

    if ($EnableErrorLogging_Called == true)
    {
        return;
    }

    static $EnableErrorLogging_Called = true;

    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('error_reporting', E_ALL);
    //define('MY_DEBUG', true);
    //define('WP_DEBUG_DISPLAY', true);

    $MY_DEBUG = true;

}

function timer()
{
    $starttime = microtime(true);
    /* do stuff here */

    $endtime = microtime(true);
    $timediff = $endtime - $starttime;

    var_dump($timediff); //in seconds
}


/**
 * The provided url should be an article of this wordpress installation. This method is used to test on staging environments
 */
function ReplaceTargetUrlIfStaging($target_url)
{
    if (MyString::Contains(ServerHelper::getDomain(), "cloudwaysapps.com") && MyString::Contains($target_url, "cloudwaysapps.com"))
    {
        //don't replace, cause it's already staging url
    }
    elseif (MyString::Contains(ServerHelper::getDomain(), "cloudwaysapps.com"))
    {
        $target_url = HtmlHelper::ReplaceDomain($target_url, ServerHelper::getDomain());
    }
    return $target_url;
}

function get_progress_bar()
{
    $progress_bar_html = <<<ABC
		<div class="md-progress-bar-container">
			<div class="md-progress-bar" id="md-progress-bar"></div>
		</div>
ABC;

    return $progress_bar_html;

}


function mdpb_scripts_styles()
{
    wp_register_style('mdpb-styles', plugins_url('/gik25-microdata/assets/css/mdpb.css'), array(), '', 'all');
    wp_enqueue_style('mdpb-styles');
    wp_register_style('revious-microdata', plugins_url('/gik25-microdata/assets/css/revious-microdata.css'), array(), '', 'all');
    wp_enqueue_style('revious-microdata');
    wp_register_script('mdpb-script', plugins_url('/gik25-microdata/assets/js/mdpb.js'), array('jquery'));
    wp_enqueue_script('mdpb-script');
}

add_action('wp_enqueue_scripts', 'mdpb_scripts_styles');

define('PLUGIN_NAME_PREFIX', 'md_');

// add_filter('parse_query', 'wh_hideOthersRolePost');
add_filter('parse_query', 'md_hide_others_roles_posts');

// function wh_hideOthersRolePost($query) {
function md_hide_others_roles_posts($query) {
    global $pagenow;
    global $current_user;

    // $editor_1_id = 2;
    // $editor_2_id = 3;
    $editor_1_id = 199;
    $editor_2_id = 200;

    // $my_custom_post_type = 'companies'; // <-- replace it with your post_type slug
    // $my_custom_role = ['members', 'recruiter']; // <-- replace it with your role slug
    $my_custom_post_type = 'post'; // <-- replace it with your post_type slug
    //$my_custom_role = ['editor']; // <-- replace it with your role slug
    // $my_custom_role = ['administrator', 'editor']; 
    $my_custom_role = ['editor']; 

    //if user is not logged in or the logged in user is admin then dont do anything
    if (!is_user_logged_in() && !is_admin())
        return;

    $user_roles = $current_user->roles;
    //var_dump($current_user->roles);exit;
    $user_role = array_shift($user_roles);

    if(!in_array($user_role, $my_custom_role))
        return;

    $current_user_id = get_current_user_id();
    //var_dump($current_user_id);exit;

    if($current_user_id == $editor_1_id) {
        $user_excluded = $editor_2_id;
    }
    elseif ($current_user_id == $editor_2_id) {
        $user_excluded = $editor_1_id;
    }
    else {
        return;
    }
    

    $users_excluded = array($user_excluded);

    $user_args = [
        //'role' => $user_role,
        'fields ' => 'ID',
        'exclude' => $users_excluded
    ];

    //getting all the user_id with the specific role.
    $users = get_users($user_args);
    //print_r($users);

    if (!count($users)) {
        return;
    }
    $author__in = []; // <- variable to store all User ID with specific role
    foreach ($users as $user) {
        $author__in[] = $user->ID;
    }

    // if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == $my_custom_post_type){
    //     //retriving post from specific authors which has the above mentioned role.
    //     $query->query_vars['author__in'] = $author__in;
    // }

    if (is_admin() && $pagenow == 'edit.php') {
        //retriving post from specific authors which has the above mentioned role.
        $query->query_vars['author__in'] = $author__in;
    }

}
