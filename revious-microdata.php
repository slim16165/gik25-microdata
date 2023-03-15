<?php ////////////////////
/*
Plugin Name: Revious Microdata
Plugin URI:
Description:
Version:     1.6.0
Author:      Gianluigi Salvi
 */

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

if (defined('DOING_AJAX') && DOING_AJAX)
    return;

define('MY_PLUGIN_PATH', plugins_url(__FILE__));
const PLUGIN_NAME_PREFIX = 'md_';

//TODO: Automatically detect the current website
require_once("include/site_specific/superinformati_specific.php");

/**
 * @param $methods
 * @return mixed
 */
function mmx_remove_xmlrpc_methods($methods): mixed
{
    unset($methods['system.multicall']);
    return $methods;
}

add_filter('xmlrpc_methods', 'mmx_remove_xmlrpc_methods');


//EnableErrorLogging();

//Avoid link and pages for tags of just one link
//TagHelper::add_filter_DisableTagWith1Post();

//OptimizationHelper::ConditionalLoadCssJsOnPostsWhichContainAnyEnabledShortcode();





