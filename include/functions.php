<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

	spl_autoload_register(function($className) {
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

	function IsNullOrEmptyString($str){
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


        $errormessage="<pre>$errormessage<br/>
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

		if($EnableErrorLogging_Called == true)
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

	// function ReplaceTargetUrlIfStaging($target_url) : string
	// {
	// 	if (MyString::Contains(ServerHelper::getDomain(), "cloudwaysapps.com"))
	// 	{
	// 		$target_url = str_replace("www.chiecosa.it", "wordpress-217146-983380.cloudwaysapps.com", $target_url);
	// 		$target_url = str_replace("www.nonsolodiete.it", "wordpress-217146-992662.cloudwaysapps.com", $target_url);
	// 		$target_url = str_replace("www.superinformati.com", "wordpress-217146-1004348.cloudwaysapps.com", $target_url);
	// 		$target_url = str_replace("www.totaldesign.it", "wordpress-217146-1330173.cloudwaysapps.com", $target_url);
	// 		//wordpress-251650-1339580.cloudwaysapps.com
	// 		$target_url = str_replace("www.nonsolodiete.it", "wordpress-251650-1339580.cloudwaysapps.com", $target_url);
			
	// 	}
	// 	return $target_url;
	// }

	function ReplaceTargetUrlIfStaging($target_url)
	{
		if (MyString::Contains(ServerHelper::getDomain(), "cloudwaysapps.com"))
		{
			$target_url = str_replace("www.chiecosa.it", "wordpress-217146-983380.cloudwaysapps.com", $target_url);
			$target_url = str_replace("www.nonsolodiete.it", "wordpress-217146-992662.cloudwaysapps.com", $target_url);
			$target_url = str_replace("www.superinformati.com", "wordpress-217146-1004348.cloudwaysapps.com", $target_url);
			$target_url = str_replace("www.totaldesign.it", "wordpress-217146-1330173.cloudwaysapps.com", $target_url);
			//wordpress-251650-1339580.cloudwaysapps.com
			$target_url = str_replace("www.nonsolodiete.it", "wordpress-251650-1339580.cloudwaysapps.com", $target_url);
			
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