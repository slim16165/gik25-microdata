<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 21/10/2019
	 * Time: 15:02
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