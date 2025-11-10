<?php
namespace gik25microdata\ListOfPosts\Renderer;

use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\ListOfPosts\WPPostsHelper;
use gik25microdata\Widgets\ColorWidget;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renderer per link in formato carousel
 * Usa il template ColorWidget per visualizzazione carousel
 */
class CarouselLinkRenderer implements LinkRendererInterface
{
    public function render(LinkBase $link, array $options = []): string
    {
        $target_url = WPPostsHelper::ReplaceTargetUrlIfStaging($link->Url);
        return ColorWidget::GetLinkWithImageCarousel($target_url, $link->Title);
    }
    
    public function renderCollection(Collection $links, array $options = []): string
    {
        $result = '';
        foreach ($links as $link) {
            $result .= $this->render($link, $options);
        }
        return $result;
    }
    
    public function supports(string $option): bool
    {
        return in_array($option, ['carousel']);
    }
}
