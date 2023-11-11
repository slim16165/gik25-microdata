<?php ////////////////////
/*
Plugin Name: Revious Microdata
Plugin URI:
Description:
Version:     1.7.0
Author:      Gianluigi Salvi
 */

use gik25microdata\Utility\OptimizationHelper;

function AutomaticallyDetectTheCurrentWebsite(): void
{
//require_once("include/site_specific/superinformati_specific.php");
    $domain = $_SERVER['HTTP_HOST'];

    $domain_specific_files = [
        'www.nonsolodiete.it' => 'nonsolodiete_specific.php',
        'www.superinformati.com' => 'superinformati_specific.php',
        // Aggiungi altre corrispondenze qui
    ];

    $current_domain = $_SERVER['HTTP_HOST'];

    if (array_key_exists($current_domain, $domain_specific_files)) {
        $specific_file = $domain_specific_files[$current_domain];
        require_once("include/site_specific/" . $specific_file);
    } else {
        // Gestisci il caso in cui il dominio corrente non è mappato a un file specifico
    }

}

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}
else exit();

if (defined('DOING_AJAX') && DOING_AJAX)
    return;

define('MY_PLUGIN_PATH', plugins_url(__FILE__));
const PLUGIN_NAME_PREFIX = 'md_';

//Automatically detect the current website


OptimizationHelper::ConditionalLoadCssJsOnPostsWhichContainAnyEnabledShortcode();
AutomaticallyDetectTheCurrentWebsite();

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

