<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_shortcode('youtube', 'youtube_handler');
add_shortcode('quote', 'quote_handler');
add_shortcode('flexlist', 'flexlist_handler');

function youtube_handler($atts, $content = null)
{
    if(isset($atts["url"])) {
        $result = wp_oembed_get($atts["url"]);
        return $result;
    }
}

function quote_handler($atts, $content = null)
{
    $result = "<blockquote>$content</blockquote>";
    return $result;
}

function flexlist_handler($atts, $content = null)
{
    $html = $content;
    $dom = new DOMDocument;
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//ul");
    foreach ($nodes as $node) {
        $node->setAttribute('style', 'display: flex; flex-wrap: wrap;');
    }

    $nodes = $xpath->query("//li");
    foreach ($nodes as $node) {
        $node->setAttribute('style', 'margin-right: 5px;');
    }

    return $dom->saveHTML();
}

#region	Metodi per fixare wpautop, wptexturize e simili negli shortcode


//	NON FUNZIONA - ROMPE l'altro programma
//	include "class/shortcode-wpautop-control.php";
//	chiedolabs_shortcode_wpautop_control(array('domande_e_risposte'));

//	function shortcode_fix( $content ) {
//		// List all your shortcodes as an array
//		$block = join( '|', array( 'domande_e_risposte' ) );
//
//		$rep = preg_replace( "/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/", '[$2$3]', $content );
//		$rep = preg_replace( "/(<p>)?\[\/($block)](<\/p>|<br \/>)?/", '[/$2]', $rep );
//
//		return $rep;
//	}
//	add_filter( 'the_content', 'shortcode_fix' );

//Other transformation
//	remove_filter('the_content', 'wpautop');
//	remove_filter('the_content', 'wptexturize');
//	remove_filter('the_content', 'convert_chars');

//Evita di applicare la funzione
apply_filters( 'no_texturize_shortcodes', array('registerNoTexturizeShortcodes'));

remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop' , 9);

function registerNoTexturizeShortcodes( $shortcodes )
{
    $shortcodes[] = 'domande_e_risposte';
    return $shortcodes; //array_merge($shortcodes, array('domande_e_risposte'));
}

#endregion
