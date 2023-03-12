<?php
namespace gik25microdata\ListOfPosts;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\Img;

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

        $innerHtml =
            Div::tag()->content($featured_img_html)->encode(false)->addClass('li-img')->render().
            Div::tag()->content($anchorText)->encode(false)->addClass('li-text')->render();

        if ($noLink)
        {
            $tpl = Html::li()
                ->addContent($innerHtml)->encode(false)
                ->addContent(Div::tag()->content($comment)->encode(false)->addClass('li-text'));
        }
        else
        {
            $tpl = Html::li()
                    ->addContent( Html::a($innerHtml, $target_url)->encode(false) )
                    ->addContent( Html::div($comment, ['class' => 'li-text']) ); //il commento è fuori dal link) .

        }
        return $tpl->render();
    }

    public static function GetFeaturedImage($featured_img_url, string $anchorText): string
    {
        if ($featured_img_url == null)
            $featured_img_url = plugins_url() . '/gik25-microdata/assets/images/placeholder-200x200.png';

        $featured_img_html = Img::tag()
                ->width(50)
                ->height(50)
                ->attribute("style", "width=50px; height: 50px;")
                ->src($featured_img_url)
                ->alt($anchorText)
                ->render();

        return $featured_img_html;
    }
}