<?php
/**
 * Uninstall plugin.
 *
 * @package interlinks-manager
 */

// Exit if this file is called outside WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die(); }

require_once plugin_dir_path( __FILE__ ) . 'shared/class-daim-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daim-admin.php';

// Delete options and tables.
Daim_Admin::un_delete();
