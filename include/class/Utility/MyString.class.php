<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 21/10/2019
	 * Time: 15:03
	 */

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