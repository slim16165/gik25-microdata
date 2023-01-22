<?php
namespace include\class\Utility;

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
            $parsed_url = parse_url($textContainingDomain);
            //else keep the protocol from the source text (if present)

            $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
            $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
            $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
            $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
            $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
            $result = "$scheme$destDomain$path$query$fragment";
            //$result = preg_replace('%(https?://)?([-A-Z0-9.]+)%im', "\1$destDomain", $textContainingDomain);
        }

        return $result;
    }
}