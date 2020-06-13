<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_filter( 'infophilic_fontawesome_essentials', 'infophilic_fontawesome_essentials' );
function infophilic_fontawesome_essentials()
{
    return true;
}

//Removing Emojis

add_action( 'init', 'infophilic_disable_wp_emojicons' );
function infophilic_disable_wp_emojicons()
{
    // all actions related to emojis
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
}

// Remove WP embed script
function infophilic_stop_loading_wp_embed() {
    if (!is_admin()) {
        wp_deregister_script('wp-embed');
    }
}
add_action('init', 'infophilic_stop_loading_wp_embed');

//Remove Multi Purpose Style
add_action( 'wp_enqueue_scripts', 'infophilic_remove_multi_purpose', 20 );
function infophilic_remove_multi_purpose() {
    wp_dequeue_style( 'td-plugin-multi-purpose' );
}

