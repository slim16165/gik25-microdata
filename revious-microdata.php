<?php
/*
Plugin Name: Revious Microdata
Plugin URI:
Description:
Version:     1.11.0
Author:      Gianluigi Salvi
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Carica la classe Bootstrap
require_once __DIR__ . '/include/class/PluginBootstrap.php';

// Inizializza il plugin
\gik25microdata\PluginBootstrap::init(__FILE__);
