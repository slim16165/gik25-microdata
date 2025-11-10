<?php
/**
 * Plugin Name: Revious Microdata
 * Plugin URI:  https://github.com/slim16165/gik25-microdata
 * Description: Plugin WordPress multipiattaforma per gestione shortcode, microdata, ottimizzazioni SEO e widget interattivi. Include sistema caroselli configurabili, widget cucine, navigazione app-like, MCP server per AI, health check e molto altro.
 * Version:     2.3.6
 * Author:      Gianluigi Salvi
 * Author URI:  https://github.com/slim16165
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: revious-microdata
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Network: false
 * 
 * Siti supportati: TotalDesign.it, SuperInformati.com, NonSoloDieti.it, ChieCosa.it, Prestinforma.it
 * 
 * @package ReviousMicrodata
 * @author  Gianluigi Salvi
 * @version 2.3.6
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Carica SafeExecution PRIMA di PluginBootstrap (necessario per protezione inizializzazione)
// SafeExecution deve essere caricata manualmente perché viene usata prima dell'autoloader
require_once __DIR__ . '/include/class/Utility/SafeExecution.php';

// Carica la classe Bootstrap
require_once __DIR__ . '/include/class/PluginBootstrap.php';

// Carica e inizializza endpoint logs avanzato (resolver/reader/parser/pipeline + tracing)
// Caricamento diretto per garantire la registrazione route anche se l'autoloader non è pronto
require_once __DIR__ . '/include/class/Logs/Core.php';
require_once __DIR__ . '/include/class/Logs/Rest.php';
\gik25microdata\Logs\Rest::init();

// Inizializza il plugin
\gik25microdata\PluginBootstrap::init(__FILE__);
