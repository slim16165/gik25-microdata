<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
* Date: 23/09/2019
* Time: 14:36
*/

	function IsNullOrEmptyString($str){
		return (!isset($str) || trim($str) === '');
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

	function ReplaceTargetUrlIfStaging($target_url) : string
	{
		if (MyString::Contains(ServerHelper::getDomain(), "cloudwaysapps.com"))
		{
			$target_url = str_replace("www.chiecosa.it", "wordpress-217146-983380.cloudwaysapps.com", $target_url);
			$target_url = str_replace("www.nonsolodiete.it", "wordpress-217146-992662.cloudwaysapps.com", $target_url);
		}
		return $target_url;
	}

	function add_LogRocket()
	{
		if ( defined( 'DOING_AJAX' ))
		{
			return;
		}
		$user = wp_get_current_user();
		echo <<<TAG
<script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
<script>window.LogRocket && window.LogRocket.init('hdyhlv/si');
LogRocket.identify('{$user->user_login}', {
  name: '{$user->user_nicename}',
  email: '{$user->user_email}',
});
</script>
TAG;

	}

