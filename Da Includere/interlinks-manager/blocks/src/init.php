<?php
/**
 * Use to include the block editor assets and to register the meta fields used in the components of the post sidebar.
 *
 * @package real-voice
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// The daim_init_editor_assets method has been added to the init hook to make current_user_can() available.
add_action( 'init', 'daim_init_editor_assets' );

/**
 * Add the action use to include the block editor assets.
 *
 * Note that the "Editor Tools Capability" is configured in the plugin settings page.
 */
function daim_init_editor_assets() {

	/**
	 * Do not enable the editor assets if we are in one of the following menus:
	 *
	 * - Appearance -> Widgets (widgets.php).
	 * - Appearance -> Editor (site-editor.php)
	 *
	 * Enabling the assets in the widgets.php or site-editor.php menus would cause errors because the post editor sidebar is
	 * not available in these menus.
	 */
	global $pagenow;
	if ( 'widgets.php' !== $pagenow &&
		'site-editor.php' !== $pagenow ) {
		add_action( 'enqueue_block_editor_assets', 'daim_editor_assets' );
	}

}

/**
 * Enqueue the Gutenberg block assets for the backend.
 *
 * 'wp-blocks': includes block type registration and related functions.
 * 'wp-element': includes the WordPress Element abstraction for describing the structure of your blocks.
 */
function daim_editor_assets() {

	// Assign an instance of the plugin shared class.
	$plugin_dir = substr( plugin_dir_path( __FILE__ ), 0, - 11 );
	require_once $plugin_dir . 'shared/class-daim-shared.php';
	$shared = Daim_Shared::get_instance();

	// Styles ---------------------------------------------------------------------------------------------------------.

	wp_enqueue_style(
		'daim-editor-css',
		plugins_url( 'css/editor.css', __DIR__ ),
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		$shared->get( 'ver' )
	);

	// Scripts --------------------------------------------------------------------------------------------------------.

	// Get the list of post types where the block sidebar sections should be added.
	$interlinks_options_post_types_a = maybe_unserialize( get_option( $shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
	$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
	$interlinks_suggestions_post_types_a = maybe_unserialize( get_option( $shared->get( 'slug' ) . '_interlinks_suggestions_post_types' ) );

	// Verify if the post type is in the list of post types where the interlinks options sidebar should be added.
	if ( ! is_array( $interlinks_options_post_types_a ) || ! in_array( get_post_type(), $interlinks_options_post_types_a, true ) ) {
		$interlinks_options_is_active_in_post_type = 0;
	}else{
		$interlinks_options_is_active_in_post_type = 1;
	}

	// Verify if the post type is in the list of post types where the interlinks optimization sidebar should be added.
	if ( ! is_array( $interlinks_optimization_post_types_a ) || ! in_array( get_post_type(), $interlinks_optimization_post_types_a, true ) ) {
		$interlinks_optimization_is_active_in_post_type = 0;
	}else{
		$interlinks_optimization_is_active_in_post_type = 1;
	}

	// Verify if the post type is in the list of post types where the interlinks suggestions sidebar should be added.
	if ( ! is_array( $interlinks_suggestions_post_types_a ) || ! in_array( get_post_type(), $interlinks_suggestions_post_types_a, true ) ) {
		$interlinks_suggestions_is_active_in_post_type = 0;
	}else{
		$interlinks_suggestions_is_active_in_post_type = 1;
	}

	// Block.
	wp_enqueue_script(
		'daim-editor-js', // Handle.
		plugins_url( '/build/index.js', __DIR__ ), // We register the block here.
		array( 'wp-blocks', 'wp-element' ), // Dependencies.
		$shared->get( 'ver' ),
		true // Enqueue the script in the footer.
	);

	// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
	$initialization_script  = 'window.DAIM_PARAMETERS = {';
	$initialization_script .= 'user_has_interlinks_options_mb_required_capability: "' . current_user_can(get_option( $shared->get( 'slug' ) . '_interlinks_options_mb_required_capability' )) . '",';
	$initialization_script .= 'user_has_interlinks_optimization_mb_required_capability: "' . current_user_can(get_option( $shared->get( 'slug' ) . '_interlinks_optimization_mb_required_capability' )) . '",';
	$initialization_script .= 'user_has_interlinks_suggestions_mb_required_capability: "' . current_user_can(get_option( $shared->get( 'slug' ) . '_interlinks_suggestions_mb_required_capability' )) . '",';
	$initialization_script .= 'interlinks_options_is_active_in_post_type: "' . $interlinks_options_is_active_in_post_type . '",';
	$initialization_script .= 'interlinks_optimization_is_active_in_post_type: "' . $interlinks_optimization_is_active_in_post_type . '",';
	$initialization_script .= 'interlinks_suggestions_is_active_in_post_type: "' . $interlinks_suggestions_is_active_in_post_type . '",';
	$initialization_script .= 'default_seo_power: "' . get_option($shared->get( 'slug' ) . '_default_seo_power' ) . '",';
	$initialization_script .= 'enable_ail: "' . get_option($shared->get( 'slug' ) . '_default_enable_ail_on_post' ) . '",';
	$initialization_script .= '};';

	wp_add_inline_script( 'daim-editor-js', $initialization_script, 'before' );
}



/**
 * Register the meta fields used in the components of the post sidebar.
 *
 * See: https://developer.wordpress.org/reference/functions/register_post_meta/
 */
function interlinks_manager_register_post_meta() {

	// Assign an instance of the plugin shared class.
	$plugin_dir = substr( plugin_dir_path( __FILE__ ), 0, - 11 );
	require_once $plugin_dir . 'shared/class-daim-shared.php';
	$shared = Daim_Shared::get_instance();

	// Register the support of the 'custom-fields' to all the post type with UI.
	$shared->register_support_on_post_types();

	/*
	 * Register the meta used to save the value of the textarea available in the "Text to Speech" section of the post
	 * sidebar included in the post editor.
	 */
	register_post_meta(
		'', // Registered in all post types.
		'_daim_seo_power',
		array(
			'auth_callback' => '__return_true',
			'default'       => '',
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
		)
	);

	/*
	 * Register the meta used to save the value of the selector available in the "Text to Speech" section of the post
	 * sidebar included in the post editor.
	 */
	register_post_meta(
		'', // Registered in all post types.
		'_daim_enable_ail',
		array(
			'auth_callback' => '__return_true',
			'default'       => '',
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
		)
	);
}

add_action( 'init', 'interlinks_manager_register_post_meta', 100000 );
