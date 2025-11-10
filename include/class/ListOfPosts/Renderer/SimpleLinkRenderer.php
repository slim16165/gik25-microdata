<?php
namespace gik25microdata\ListOfPosts\Renderer;

use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\ListOfPosts\WPPostsHelper;
use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renderer semplice per link senza immagini
 * Usato per liste semplici di link testuali
 */
class SimpleLinkRenderer implements LinkRendererInterface
{
    private bool $removeIfSelf;
    
    public function __construct(bool $removeIfSelf = true)
    {
        $this->removeIfSelf = $removeIfSelf;
    }
    
    public function render(LinkBase $link, array $options = []): string
    {
        $removeIfSelf = $options['removeIfSelf'] ?? $this->removeIfSelf;
        $target_url = WPPostsHelper::ReplaceTargetUrlIfStaging($link->Url);
        
        global $current_post;
        $current_permalink = get_permalink($current_post->ID ?? 0);
        
        if ($removeIfSelf && $current_permalink === $target_url) {
            return '';
        }
        
        $target_postid = url_to_postid($target_url);
        if ($target_postid == 0) {
            return '';
        }
        
        $target_post = get_post($target_postid);
        if ($target_post && $target_post->post_status === 'publish') {
            $safe_url = esc_url($target_url);
            $safe_nome = esc_html($link->Title);
            return "<a href=\"{$safe_url}\">{$safe_nome}</a>";
        }
        
        return '';
    }
    
    public function renderCollection(Collection $links, array $options = []): string
    {
        $result = '';
        foreach ($links as $link) {
            $rendered = $this->render($link, $options);
            if (!empty($rendered)) {
                // Se il risultato non contiene già <li>, aggiungilo
                if (strpos($rendered, '<li') === false) {
                    $result .= '<li>' . $rendered . '</li>' . "\n";
                } else {
                    $result .= $rendered . "\n";
                }
            }
        }
        return $result;
    }
    
    public function supports(string $option): bool
    {
        return in_array($option, ['removeIfSelf']);
    }
}
