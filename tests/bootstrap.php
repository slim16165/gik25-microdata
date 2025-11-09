<?php
/**
 * PHPUnit Bootstrap per test plugin WordPress
 * 
 * Questo file viene eseguito prima di ogni test suite.
 * Configura l'ambiente WordPress per i test.
 */

// Definisci costanti WordPress necessarie per i test
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../vendor/wordpress/wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', dirname(__FILE__) . '/../');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', dirname(__FILE__) . '/../');
}

if (!defined('WP_TESTS_PHPUNIT_POLYFILLS_PATH')) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname(__FILE__) . '/../vendor/yoast/phpunit-polyfills/');
}

// Carica autoloader Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';

// Mock WordPress functions se non disponibili (per test unitari)
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return esc_html($text);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook_name, $value, ...$args) {
        return $value;
    }
}

if (!function_exists('do_action')) {
    function do_action($hook_name, ...$args) {
        // No-op per test unitari
    }
}

if (!function_exists('add_action')) {
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) {
        return $gmt ? gmdate($type) : date($type);
    }
}

// Carica il plugin principale solo per test di integrazione
// Per test unitari, caricare manualmente solo le classi necessarie

