<?php
namespace gik25microdata\ListOfPosts\Renderer;

use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\ListOfPosts\HtmlTemplate;
use gik25microdata\ListOfPosts\WPPostsHelper;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renderer standard per link con thumbnail
 * Usa il template HTML standard con immagini opzionali
 */
class StandardLinkRenderer implements LinkRendererInterface
{
    private bool $removeIfSelf;
    private bool $withImage;
    private bool $linkSelf;
    
    public function __construct(bool $removeIfSelf = true, bool $withImage = true, bool $linkSelf = false)
    {
        $this->removeIfSelf = $removeIfSelf;
        $this->withImage = $withImage;
        $this->linkSelf = $linkSelf;
    }
    
    public function render(LinkBase $link, array $options = []): string
    {
        $removeIfSelf = $options['removeIfSelf'] ?? $this->removeIfSelf;
        $withImage = $options['withImage'] ?? $this->withImage;
        $linkSelf = $options['linkSelf'] ?? $this->linkSelf;
        
        $target_url = WPPostsHelper::ReplaceTargetUrlIfStaging($link->Url);
        
        list($target_post, $noLink, $debugMsg) = WPPostsHelper::GetPostData($target_url, $removeIfSelf);
        
        if ($debugMsg || !$target_post) {
            return '';
        }
        
        $commento = \gik25microdata\ListOfPosts\ListOfPostsHelper::ParseComment($link->Comment);
        
        if ($withImage) {
            return HtmlTemplate::GetTemplateWithThumbnail($target_url, $link->Title, $commento, $target_post, $noLink);
        } else {
            return HtmlTemplate::GetTemplateNoThumbnail($target_url, $link->Title, $commento, $noLink);
        }
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
        return in_array($option, ['removeIfSelf', 'withImage', 'linkSelf', 'nColumns']);
    }
}
