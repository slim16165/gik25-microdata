<?php
namespace gik25microdata\ListOfPosts;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


use gik25microdata\Utility\ImageDetails;
use gik25microdata\Utility\ImageHelper;
use gik25microdata\Utility\MyString;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\Img;
use function plugins_url;

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
            $featured_img_html = self::getOptimalImageHtml($target_post, $anchorText);
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

    public static function getOptimalImageHtml($target_post, string $anchorText): string
    {
        $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');

        $featured_img = ImageDetails::createFromUrl($featured_img_url);

        if ($featured_img_url == null || !$featured_img->FileExists())
        {
            do_action( 'qm/debug', 'L\'URL dell\'immagine thumbnail è null o l\'immagine non esiste.' );
//            $featured_img = ImageDetails::createFromPath(__DIR__ . '/../../assets/images/placeholder-50x50.png');
            $featured_img = new ImageDetails("./../../wp-content/themes/bimber-child-theme/template-parts/ListHandler/assets/images/", "placeholder-50x50.png");
        }
        else
        {
            $featured_img = ImageHelper::getOrCreateCustomThumb($featured_img);
        }

        $html = Img::tag()
            ->width(50)
            ->height(50)
            ->attribute("style", "width=50px; height: 50px;")
            ->src($featured_img->getComputedUrl())
            ->alt($anchorText)
            ->render();

        return $html;
    }

}