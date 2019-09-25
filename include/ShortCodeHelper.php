<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 17/09/2019
	 * Time: 11:37
	 */

	 class HtmlHelper
	{
		public static function CheckHtmlIsValid(string $html) : bool
		{
			$dom = new DOMDocument;
			$dom->loadHTML($html);
			if ($dom->validate())
				return true;
			else
				return false;
		}
	}

	class MyString
	{

		public static function IsNullOrEmptyString($str): bool
		{
			return (!isset($str) || trim($str) === '');
		}

		public static function Contains($haystack, $needle): bool
		{
			return strpos($haystack, $needle) !== false;
		}
	}


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
			$pageURL = $_SERVER["SERVER_NAME"];
			return $pageURL;
		}
	}