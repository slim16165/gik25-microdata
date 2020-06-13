<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

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