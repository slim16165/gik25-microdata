<?php
namespace gik25microdata\Utility;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

	class ServerHelper
	{
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