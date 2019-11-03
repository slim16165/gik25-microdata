<?php ////////////////////
/*
Plugin Name: Revious Microdata
Plugin URI:
Description: Add beautifully styled quotes to your Wordpress posts
Version:     0.1
Author:      Gianluigi Salvi
 */

//	error_reporting(E_ALL);
//	define('WP_DEBUG', true);
//	define('WP_DEBUG_DISPLAY', true);
//	ini_set('display_errors','On');
//	ini_set('error_reporting', E_ALL );
//
//	ini_set('display_startup_errors', 1);
//	ini_set('display_errors', 1);
//	error_reporting(-1);

if (defined('DOING_AJAX') && DOING_AJAX)
    return;

	require_once("include/GenericShortcode.php");
	require_once("include/site_specific/superinformati_specific.php");

