<?php
if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

class HtmlHelper
{
    public static function CheckHtmlIsValid(string $html): bool
    {
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        if ($dom->validate())
            return true;
        else
            return false;
    }

    public static function ReplaceDomain(string $textContainingDomain, string $destDomain): string
    {
        if (preg_match('%(https?://)%im', $destDomain))
        {
            //if the destination domain contains the protocol then replace it too
            $result = preg_replace('%(https?://)?([-A-Z0-9.]+)%im', $destDomain, $textContainingDomain);
        }
        else
        {
            //else keep the protocol from the source text (if present)
            $result = preg_replace('%(https?://)?([-A-Z0-9.]+)%im', "\1$destDomain", $textContainingDomain);
        }

        return $result;
    }
}