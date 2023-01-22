<?php
namespace include\class\Shortcodes;

class ShortcodeBase
{
    public function PostContainsShortCode($shortcode): bool
    {
        global $post;
        if (strpos($post->post_content, $shortcode) !== false)
            return true;
        else return false;
    }
}