<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
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