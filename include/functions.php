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
