<?php
namespace gik25microdata\ListOfPosts\Renderer;

use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\ListOfPosts\Validation\UrlValidator;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renderer per link esterni con icona e nofollow
 */
class ExternalLinkRenderer implements LinkRendererInterface
{
    private bool $showIcon;
    private bool $nofollow;
    private bool $sponsored;
    
    public function __construct(bool $showIcon = true, bool $nofollow = true, bool $sponsored = false)
    {
        $this->showIcon = $showIcon;
        $this->nofollow = $nofollow;
        $this->sponsored = $sponsored;
    }
    
    public function render(LinkBase $link, array $options = []): string
    {
        $showIcon = $options['show_icon'] ?? $this->showIcon;
        $nofollow = $options['nofollow'] ?? $this->nofollow;
        $sponsored = $options['sponsored'] ?? $this->sponsored;
        
        $isExternal = !UrlValidator::isWordPressUrl($link->Url);
        
        if (!$isExternal) {
            // Per link interni, usa il renderer standard
            $standard = new StandardLinkRenderer();
            return $standard->render($link, $options);
        }
        
        $rel = [];
        if ($nofollow) {
            $rel[] = 'nofollow';
        }
        if ($sponsored) {
            $rel[] = 'sponsored';
        }
        $rel[] = 'external';
        
        $relAttr = !empty($rel) ? ' rel="' . esc_attr(implode(' ', $rel)) . '"' : '';
        $targetAttr = ' target="_blank"';
        $iconHtml = $showIcon ? ' <span class="external-link-icon" aria-hidden="true">↗</span>' : '';
        
        $html = '<a href="' . esc_url($link->Url) . '"' . $relAttr . $targetAttr . '>';
        $html .= esc_html($link->Title);
        $html .= $iconHtml;
        $html .= '</a>';
        
        return '<li>' . $html . '</li>';
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
        return in_array($option, ['show_icon', 'nofollow', 'sponsored', 'external']);
    }
}
