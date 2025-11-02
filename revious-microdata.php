<?php ////////////////////
/*
Plugin Name: Revious Microdata
Plugin URI:
Description:
Version:     1.8.1
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
        'www.totaldesign.it' => 'totaldesign_specific.php',
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

if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    add_action('admin_notices', function() {
        echo '<div class="error">';
        echo '<p><strong>Revious Microdata: Dipendenze mancanti</strong></p>';
        echo '<p>La directory <code>vendor/</code> non è stata trovata. Esegui questo comando via SSH:</p>';
        echo '<pre style="background: #f5f5f5; padding: 10px; border-left: 4px solid #2271b1;">';
        echo 'cd ' . __DIR__ . ' && composer install --no-dev';
        echo '</pre>';
        echo '<p>Se non hai Composer installato sul server, installalo prima o contatta il tuo amministratore di sistema.</p>';
        echo '</div>';
    });
    return; // Esci senza caricare il plugin
}

require __DIR__ . '/vendor/autoload.php';

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

