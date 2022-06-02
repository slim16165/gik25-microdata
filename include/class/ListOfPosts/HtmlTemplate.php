<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once '../../../vendor/autoload.php';

use Yiisoft\Html\Html;

class HtmlTemplate
{
    public static function GetTemplateNoThumbnail(string $target_url, string $nome, string $commento, $noLink): string
    {
        if ($noLink)
        {
            if (IsNullOrEmptyString($commento))
                return "<li>$nome (articolo corrente)</li>\n";
            else
                return "<li>$nome $commento (articolo corrente)</li>\n";
        } else
        {
            if (IsNullOrEmptyString($commento))
                return "<li><a href=\"$target_url\">$nome</a></li>\n";
            else
                return "<li><a href=\"$target_url\">$nome</a> $commento</li>\n";
        }
    }

    public static function GetTemplateWithThumbnail(string $target_url, string $anchorText, string $comment, $target_post, $noLink): string
    {
        $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');

        $featured_img_html = self::GetFeaturedImage($featured_img_url, $anchorText);

        $innerHtml = <<<EOF
<div class="li-img">
    $featured_img_html
</div>
<div class="li-text">$anchorText </div>
<div class="li-text">$comment</div>
EOF;

        if ($noLink)
        {
            $tpl = Html::li($innerHtml);
        } else
        {
            $tpl = Html::li() .
                Html::a($innerHtml, $target_url) .
                Html::div($comment, ['class' => 'li-text']) . //il commento è fuori dal link
                Html::closeTag("li");
        }
        return $tpl;
    }

    public static function GetFeaturedImage($featured_img_url, string $anchorText): string
    {
        if (!$featured_img_url)
        {
            $featured_img_html = /** @lang HTML */
                '<img width="50" height="50" style="width=50px; height: 50px;" src="' . plugins_url() . '/gik25-microdata/assets/images/placeholder-200x200.png" alt="' . $anchorText . '" />';
        } else
        {
            $featured_img_html = /** @lang HTML */
                "<img width=\"50\" height=\"50\" style=\"width=50px; height: 50px;\" src=\"{$featured_img_url}\" alt=\"{$anchorText}\" />";
        }
        return $featured_img_html;
    }
}