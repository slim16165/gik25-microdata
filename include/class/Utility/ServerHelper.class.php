<?php
namespace include\class\Utility;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 21/10/2019
	 * Time: 15:04
	 */

	class ServerHelper
	{
		public static function getUrl()
		{
			$pageURL = 'http';
			if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}

		public static function getDomain()
		{
			$domain = $_SERVER["SERVER_NAME"];
			return $domain;
		}

		public static function getSecondLevelDomainOnly()
		{
		    //i.e. superinformati.com → superinformati
			$domain = $_SERVER["SERVER_NAME"];

			if (preg_match('/\b([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}\b/im', $domain, $regs)) {
				$result = preg_replace('/(.+?)\.?/im', '\1', $regs[1]);
			} else {
				$result = $domain;
			}

			return $result;
		}
	}