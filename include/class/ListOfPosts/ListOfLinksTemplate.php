<?php


class ListOfLinksTemplate
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

        if (!$featured_img_url)
        {
            $featured_img_html = /** @lang HTML */
                '<img width="50" height="50" style="width=50px; height: 50px;" src="' . plugins_url() . '/gik25-microdata/assets/images/placeholder-200x200.png" alt="' . $anchorText . '" />';
        } else
        {
            $featured_img_html = /** @lang HTML */
                '<img width="50" height="50" style="width=50px; height: 50px;" src="' . $featured_img_url . '" alt="' . $anchorText . '" />';
        }

        if ($noLink)
        {
            return <<<EOF
<li>
<div class="li-img">
    $featured_img_html
</div>
<div class="li-text">$anchorText $comment</div>
</li>\n
EOF;
        } else
        {
            return <<<EOF
<li>
<a href="$target_url">			
<div class="li-img">
    $featured_img_html		
</div>
<div class="li-text">$anchorText </div>
</a>$comment</li>\n
EOF;
        }

    }

    public static function GetTemplateWithThumbnail2(string $target_url, string $anchorText, string $comment, $target_post, $noLink): string
    {

        $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');

        if (!$featured_img_url)
        {
            $featured_img_html = /** @lang HTML */
                '<img style="width=50px; height: 50px;" src="' . plugins_url() . '/gik25-microdata/assets/images/placeholder-200x200.png" alt="' . $anchorText . '" />';
        } else
        {
            $featured_img_html = /** @lang HTML */
                "<img style=\"width=50px; height: 50px;\" src=\"{$featured_img_url}\" alt=\"{$anchorText}\" />";
        }

        if ($noLink)
        {
            return <<<EOF
<li>
<div class="li-img">
    $featured_img_html
</div>
<div class="li-text">$anchorText ($comment)</div>
</li>\n
EOF;
        } else
        {
            return <<<EOF
<li>
<a href="$target_url">			
<div class="li-img">
    $featured_img_html		
</div>
<div class="li-text">$anchorText </div>
                    </a>$comment
                </li>\n
EOF;
        }
    }
}