<?php
namespace include\class\Shortcodes;

if (!defined('ABSPATH'))
{
    exit;
}
class Flexlist extends ShortcodeBase
{
    public function __construct()
    {
        $this->shortcode = 'md_flexlist';
        parent::__construct();
    }

    public function ShortcodeHandler($atts, $content = null)
    {
        $html = $content;
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query("//ul");
        foreach ($nodes as $node)
        {
            $node->setAttribute('style', 'display: flex; flex-wrap: wrap;');
        }

        $nodes = $xpath->query("//li");
        foreach ($nodes as $node)
        {
            $node->setAttribute('style', 'margin-right: 5px;');
        }

        return $dom->saveHTML();
    }

    public function styles()
    {
        // TODO: Implement styles() method.
    }

    public function admin_scripts()
    {
        // TODO: Implement admin_scripts() method.
    }

    public function register_plugin($plugin_array)
    {
        // TODO: Implement register_plugin() method.
    }

    public function register_button($buttons)
    {
        // TODO: Implement register_button() method.
    }
}

$flexlist = new Flexlist();
//TODO: the button for TinyMCE is missing