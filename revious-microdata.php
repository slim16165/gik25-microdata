<?php
/**
 * Plugin Name: Revious Microdata
 * Plugin URI:  https://github.com/slim16165/gik25-microdata
 * Description: Plugin WordPress multipiattaforma per gestione shortcode, microdata, ottimizzazioni SEO e widget interattivi. Include sistema caroselli configurabili, widget cucine, navigazione app-like, MCP server per AI, health check e molto altro.
 * Version:     2.3.8
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
 * @version 2.3.8
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

// Register activation/deactivation hooks for Internal Links
register_activation_hook(__FILE__, function() {
    if (class_exists('\gik25microdata\InternalLinks\Core\Activator')) {
        \gik25microdata\InternalLinks\Core\Activator::activate();
    }
});

register_deactivation_hook(__FILE__, function() {
    if (class_exists('\gik25microdata\InternalLinks\Core\Activator')) {
        \gik25microdata\InternalLinks\Core\Activator::deactivate();
    }
});

// Uninstall hook must use a named function, not a closure (WordPress serializes it)
if (!function_exists('gik25microdata_internal_links_uninstall')) {
    function gik25microdata_internal_links_uninstall() {
        if (class_exists('\gik25microdata\InternalLinks\Core\Activator')) {
            \gik25microdata\InternalLinks\Core\Activator::uninstall();
        }
    }
}
register_uninstall_hook(__FILE__, 'gik25microdata_internal_links_uninstall');