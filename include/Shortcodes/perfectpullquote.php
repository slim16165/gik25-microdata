<?php
if(!defined('ABSPATH')) {
    exit;
}
class Perfectpullquote {
    
    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'perfectpullquote', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'perfectpullquote_styles'));
        add_filter('mce_external_plugins', array($this, 'perfectpullquote_add_buttons'));
        add_filter('mce_buttons', array($this, 'perfectpullquote_register_buttons'));
    }

    public function shortcode($atts, $content = null) {
        $a = shortcode_atts(array(
            'align' => 'left', // (Required) Align pullquote to the left, right, or full (for width:100%). Default left.
            'bordertop' => 'false', // (Optional) Change border location to the top, then fallback to align location on mobile.
            'cite' => null, // (Optional) Add the name/source of the quote.
            'link' => null, // (Optional) Add a link to the cited source, must be http or https link.
            'color' => null, // (Optional) Provide the HEX value of the border-color. Default #EEEEEE
            'class' => null, // (Optional) Add additional classes to the div.pullquote object.
            'size' => null // (Optional) Define the font size of the text in pixels.
    
        ), $atts);
    
        // Pullquote alignment (left, right, or full)
    
    
        $alignment = '';
        switch ($a['align']) {
            case 'full':
                $alignment = ' pullquote-align-full';
                break;
            case 'right':
                $alignment = ' pullquote-align-right';
                break;
            default:
                $alignment = ' pullquote-align-left';
                break;
        }
    
        //Check for border location options.
        $border = '';
        switch ($a['bordertop']) {
            case 'true':
                $border = " pullquote-border-placement-top";
                break;
            default:
                if ($a['align'] == 'left') {
                    $border = " pullquote-border-placement-right";
                } else {
                    $border = " pullquote-border-placement-left";
                }
                break;
        }
    
    
        // Check for classes
        if (isset($a['class']) && strlen($a['class']) > 0 && preg_match('/[a-zA-Z0-9_ -]*/', $a['class'])):
            $classes = strip_tags($a['class']);
            $classes = esc_attr($classes);
            $classes = ' ' . preg_replace('/[^a-z0-9_ -]+/i', '', $classes);
        else:
            $classes = null;
        endif;
    
        // Check for size
        if (isset($a['size']) && strlen($a['size']) > 0 && strlen($a['size']) < 3 && is_numeric($a['size'])):
            $size = 'font-size:' . $a['size'] . 'px !important;';
            $paragraphSize = ' style="font-size:' . $a['size'] . 'px !important;"';
        else:
            $size = null;
            $paragraphSize = null;
        endif;
    
        // border-color: HEX value
        if (isset($a['color']) && strlen($a['color']) > 1 && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $a['color'])):
            $color = 'border-color:' . $a['color'] . ' !important;';
        else:
            $color = null;
        endif;
    
        if (!is_null($color) || !is_null($size)):
            $styles = ' style="' . $color . $size . '"';
        else:
            $styles = null;
        endif;
    
        // Check for cite
        if (isset($a['cite']) && strlen($a['cite']) > 1):
            $citeText = '<span itemprop="name">' . strip_tags($a['cite']) . '</span>';
        else:
            $citeText = null;
        endif;
    
        // Check for link
        if (isset($a['link']) && strlen($a['link']) > 1 && preg_match("/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $a['link'])):
            $citeLink = $a['link'];
            $citeAttribute = ' cite="' . $citeLink . '"';
            $citeLinkWithText = '<a href="' . $a['link'] . '" class="url" target="_blank" itemprop="url">' . $citeText . '</a>';
        else:
            $citeLink = null;
            $citeAttribute = null;
            $citeLinkWithText = null;
        endif;
    
        // Create footer
        if ($citeLink && $citeText):
            $citeFooter = '<footer itemscope itemtype="http://schema.org/Person"><cite>' . $citeLinkWithText . '</cite></footer>';
        elseif ($citeText):
            $citeFooter = '<footer itemscope itemtype="http://schema.org/Person"><cite>' . $citeText . '</cite></footer>';
        else:
            $citeFooter = null;
        endif;
    
        return '<div class="gik25-quote vcard' . $alignment . $border . $classes . '"' . $styles . '><blockquote' . $citeAttribute . '><p' . $paragraphSize . '>' . do_shortcode($content) . '</p>' . $citeFooter . '</blockquote></div>';
    }

    public function perfectpullquote_styles(){
        wp_register_style('gik25-quotes-styles', plugins_url('/gik25-microdata/assets/css/gik25-quotes.css'), array(), '1.7.5', 'all');
        wp_enqueue_style('gik25-quotes-styles');
    }

    public function perfectpullquote_add_buttons($plugin_array) {
        $plugin_array['gik25_quotes'] = plugins_url( '/gik25-microdata/assets/js/quotes.js' );
        return $plugin_array;
    }
    
    public function perfectpullquote_register_buttons($buttons) {
        array_push( $buttons, 'pullquote-menu');
        return $buttons;
    }

}

$perfectpullquote = new Perfectpullquote();