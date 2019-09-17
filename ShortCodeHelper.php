<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 17/09/2019
	 * Time: 11:37
	 */

	class HtmlHelper
	{
		public function CheckHtmlIsValid(string $html) : bool
		{
			$dom = new DOMDocument;
			$dom->loadHTML($html);
			if ($dom->validate())
				return true;
			else
				return false;
		}
	}