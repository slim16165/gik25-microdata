<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Yiisoft\Html\Html;
use Yiisoft\Html\NoEncode;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;

class HtmlTemplate
{
    public static function GetTemplateNoThumbnail(string $target_url, string $nome, string $commento, $noLink): string
    {
        if ($noLink)
        {
            if (MyString::IsNullOrEmptyString($commento))
                return "<li>$nome (articolo corrente)</li>\n";
            else
                return "<li>$nome $commento (articolo corrente)</li>\n";
        } else
        {
            if (MyString::IsNullOrEmptyString($commento))
                return "<li><a href=\"$target_url\">$nome</a></li>\n";
            else
                return "<li><a href=\"$target_url\">$nome</a> $commento</li>\n";
        }
    }

    public static function GetTemplateWithThumbnail(string $target_url, string $anchorText, string $comment, $target_post, $noLink): string
    {
        $featured_img_html = "";
        if($target_post != null)
        {
            $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
            $featured_img_html = self::GetFeaturedImage($featured_img_url, $anchorText);
        }

        $innerHtml = NoEncode::string(<<<EOF
<div class="li-img">
    $featured_img_html
</div>
<div class="li-text">$anchorText </div>
<div class="li-text">$comment</div>
EOF);

        $innerHtml =
            Div::tag()->content($featured_img_html)->encode(false)->addClass('li-img')->render().
            Div::tag()->content($anchorText)->encode(false)->addClass('li-text')->render().
            Div::tag()->content($comment)->encode(false)->addClass('li-text')->render();

        if ($noLink)
        {
            $tpl = Html::li($innerHtml)->encode(false)->render();
        } else
        {
            $tpl = Html::li(
                Html::a($innerHtml, $target_url) .
                       Html::div($comment, ['class' => 'li-text'])); //il commento è fuori dal link) .

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
                "<img width=\"50\" height=\"50\" style=\"width=50px; height: 50px;\" src=\"$featured_img_url\" alt=\"$anchorText\" />";
        }
        return $featured_img_html;
    }
}