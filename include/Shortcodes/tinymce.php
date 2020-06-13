<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action('init', 'revious_microdata_buttons');

function revious_microdata_buttons()
{
    add_filter("mce_external_plugins", "revious_microdata_add_tinymce_plugins");
    add_filter('mce_buttons', 'revious_microdata_register_buttons');
}

function revious_microdata_add_tinymce_plugins($plugin_array)
{
    $plugin_array['revious_microdata'] = plugins_url('../../assets/js/revious-microdata.js', __FILE__);
    $plugin_array['QuestionAndAnswer'] = plugins_url('../../assets/js/revious-microdata.js', __FILE__);
    return $plugin_array;
}

function revious_microdata_register_buttons($buttons)
{
    array_push($buttons, 'md_telefono_btn');
    array_push($buttons, 'md_prezzo_btn');
    array_push($buttons, 'DomandeERisposte_btn');
    return $buttons;
}