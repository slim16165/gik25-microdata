<?php
/**
 * Plugin Name: Interlinks Manager
 * Description: Manages the internal links of your WordPress website.
 * Version: 1.41
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: interlinks-manager
 * License: GPLv3
 *
 * @package interlinks-manager
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die(); }

// Set constants.
define('DAIM_EDITION', 'PRO');

const DAIM_PLUGIN_UPDATE_CHECKER_SETTINGS = array(
	'slug'                          => 'interlinks-manager',
	'prefix'                        => 'daim',
	'wp_plugin_update_info_api_url' => 'https://daext.com/wp-json/daext-commerce/v1/wp-plugin-update-info/',
);

// Class shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daim-shared.php';

// Rest API.
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextdaim-rest.php';
add_action( 'plugins_loaded', array( 'Daextdaim_Rest', 'get_instance' ) );

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Register the update checker callbacks on filters.
 *
 * @return void
 */
function daim_register_update_checker_callbacks_on_filters() {

	$plugin_update_checker = new PluginUpdateChecker( DAIM_PLUGIN_UPDATE_CHECKER_SETTINGS );
	$plugin_update_checker->register_callbacks_on_filters();

}

add_action( 'plugins_loaded', 'daim_register_update_checker_callbacks_on_filters' );

// Perform the Gutenberg related activities only if Gutenberg is present.
if ( function_exists( 'register_block_type' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'blocks/src/init.php';
}

// Public.
require_once plugin_dir_path( __FILE__ ) . 'public/class-daim-public.php';
add_action( 'plugins_loaded', array( 'Daim_Public', 'get_instance' ) );

// Admin.
if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-daim-admin.php' );

	// If this is not an AJAX request, create a new singleton instance of the admin class.
	if(! defined( 'DOING_AJAX' ) || ! DOING_AJAX ){
		add_action( 'plugins_loaded', array( 'Daim_Admin', 'get_instance' ) );
	}

	// Activate the plugin using only the class static methods.
	register_activation_hook( __FILE__, array( 'Daim_Admin', 'ac_activate' ) );

	// Deactivate the plugin only with static methods.
	register_deactivation_hook( __FILE__, array( 'Daim_Admin', 'dc_deactivate' ) );

	// Update the plugin db tables and options if they are not up-to-date.
	Daim_Admin::ac_create_database_tables();
	Daim_Admin::ac_initialize_options();

}

// Ajax.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	// Admin.
	require_once plugin_dir_path( __FILE__ ) . 'class-daim-ajax.php';
	add_action( 'plugins_loaded', array( 'Daim_Ajax', 'get_instance' ) );

}

/**
 * Load the plugin text domain for translation.
 *
 * @return void
 */
function daim_load_plugin_textdomain() {
	load_plugin_textdomain( 'interlinks-manager', false, 'interlinks-manager/lang/' );
}

add_action( 'init', 'daim_load_plugin_textdomain' );
