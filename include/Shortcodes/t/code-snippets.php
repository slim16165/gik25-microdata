<?php
function wpdocs_shortcode_scripts() {
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wpdocs-shortcode') ) {
        wp_enqueue_script( 'wpdocs-script');
    }
}
add_action( 'wp_enqueue_scripts', 'wpdocs_shortcode_scripts');


$content = 'This is some text, (perhaps pulled via $post->post_content). It has a  shortcode.';
 
if ( has_shortcode( $content, 'gallery' ) ) {
    // The content has a  short code, so this check returned true.
}