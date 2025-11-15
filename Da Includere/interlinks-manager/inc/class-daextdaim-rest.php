<?php
/**
 * Here the REST API endpoint of the plugin are registered.
 *
 * @package interlinks-manager
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

/**
 * This class should be used to work with the REST API endpoints of the plugin.
 */
class Daextdaim_Rest {

	/**
	 * The singleton instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextrevop_Shared|null
	 */
	private $shared = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the shared class.
		$this->shared = Daim_Shared::get_instance();

		/**
		 * Add custom routes to the Rest API.
		 */
		add_action( 'rest_api_init', array( $this, 'rest_api_register_route' ) );
	}

	/**
	 * Create a singleton instance of the class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add custom routes to the Rest API.
	 *
	 * @return void
	 */
	public function rest_api_register_route() {

		// Add the POST 'interlinks-manager-pro/v1/read-options/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/read-options/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_read_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_options_callback_permission_check' ),
			)
		);

		// Add the POST 'interlinks-manager-pro/v1/options/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/options',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_update_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_update_options_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/statistics/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/statistics/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_read_statistics_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_statistics_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/juice/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/juice/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_read_juice_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_juice_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/juice-url/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/juice-url/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_read_juice_url_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_juice_url_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/http-status/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/http-status/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_read_http_status_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_http_status_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/hits/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/hits/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_read_hits_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_hits_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/dashboard-menu-export-csv/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/dashboard-menu-export-csv/',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'rest_api_interlinks_manager_pro_dashboard_menu_export_csv_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_statistics_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/juice-menu-export-csv/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/juice-menu-export-csv/',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'rest_api_interlinks_manager_pro_juice_menu_export_csv_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_juice_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/anchors-menu-export-csv/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/anchors-menu-export-csv/',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'rest_api_interlinks_manager_pro_anchors_menu_export_csv_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_juice_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/http-status-menu-export-csv/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/http-status-menu-export-csv/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_http_status_menu_export_csv_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_http_status_callback_permission_check' ),
			)
		);

		// Add the POST 'real-voice-pro/v1/hits-menu-export-csv/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/hits-menu-export-csv/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_hits_menu_export_csv_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_read_hits_callback_permission_check' ),
			)
		);

		// Add the POST 'interlinks-manager-pro/v1/generate-interlinks-suggestions/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/generate-interlinks-suggestions/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_generate_interlinks_suggestions_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_generate_interlinks_suggestions_callback_permission_check' ),
			)
		);

		// Add the POST 'interlinks-manager-pro/v1/generate-interlinks-optimization/' endpoint to the Rest API.
		register_rest_route(
			'interlinks-manager-pro/v1',
			'/generate-interlinks-optimization/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_interlinks_manager_pro_generate_interlinks_optimization_callback' ),
				'permission_callback' => array( $this, 'rest_api_interlinks_manager_pro_generate_interlinks_optimization_callback_permission_check' ),
			)
		);
	}

	/**
	 * Callback for the GET 'interlinks-manager-pro/v1/options' endpoint of the Rest API.
	 *
	 *   This method is in the following contexts:
	 *
	 *  - To retrieve the plugin options in the "Options" menu.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_read_options_callback() {

		// Generate the response.
		$response = array();
		foreach ( $this->shared->get( 'options' ) as $key => $value ) {
			$response[ $key ] = get_option( $key );
		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_read_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to read the Interlinks Manager options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/options' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 *  - To update the plugin options in the "Options" menu.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_update_options_callback( $request ) {

		// get and sanitize data --------------------------------------------------------------------------------------.

		$options = array();

		// Automatic Links ----------------------------------------------------------------------------------------.

		// Options.
		$options['daim_default_enable_ail_on_post']       = $request->get_param( 'daim_default_enable_ail_on_post' ) !== null ? intval( $request->get_param( 'daim_default_enable_ail_on_post' ), 10 ) : null;
		$options['daim_filter_priority']                  = $request->get_param( 'daim_filter_priority' ) !== null ? intval( $request->get_param( 'daim_filter_priority' ), 10 ) : null;
		$options['daim_ail_test_mode']                    = $request->get_param( 'daim_ail_test_mode' ) !== null ? intval( $request->get_param( 'daim_ail_test_mode' ), 10 ) : null;
		$options['daim_random_prioritization']            = $request->get_param( 'daim_random_prioritization' ) !== null ? intval( $request->get_param( 'daim_random_prioritization' ), 10 ) : null;
		$options['daim_ignore_self_ail']                  = $request->get_param( 'daim_ignore_self_ail' ) !== null ? intval( $request->get_param( 'daim_ignore_self_ail' ), 10 ) : null;
		$options['daim_categories_and_tags_verification'] = $request->get_param( 'daim_categories_and_tags_verification' ) !== null ? sanitize_key( $request->get_param( 'daim_categories_and_tags_verification' ) ) : null;
		$options['daim_general_limit_mode']               = $request->get_param( 'daim_general_limit_mode' ) !== null ? intval( $request->get_param( 'daim_general_limit_mode' ), 10 ) : null;
		$options['daim_characters_per_autolink']          = $request->get_param( 'daim_characters_per_autolink' ) !== null ? intval( $request->get_param( 'daim_characters_per_autolink' ), 10 ) : null;
		$options['daim_max_number_autolinks_per_post']    = $request->get_param( 'daim_max_number_autolinks_per_post' ) !== null ? intval( $request->get_param( 'daim_max_number_autolinks_per_post' ), 10 ) : null;
		$options['daim_general_limit_subtract_mil']       = $request->get_param( 'daim_general_limit_subtract_mil' ) !== null ? intval( $request->get_param( 'daim_general_limit_subtract_mil' ), 10 ) : null;
		$options['daim_same_url_limit']                   = $request->get_param( 'daim_same_url_limit' ) !== null ? intval( $request->get_param( 'daim_same_url_limit' ), 10 ) : null;

		// Protected Elements.
		$options['daim_protected_tags']                         = $request->get_param( 'daim_protected_tags' ) !== null && is_array($request->get_param( 'daim_protected_tags' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_protected_tags' ) ) : null;
		$options['daim_protected_gutenberg_blocks']             = $request->get_param( 'daim_protected_gutenberg_blocks' ) !== null && is_array($request->get_param( 'daim_protected_gutenberg_blocks' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_protected_gutenberg_blocks' ) ) : null;
		$options['daim_protected_gutenberg_custom_blocks']      = $request->get_param( 'daim_protected_gutenberg_custom_blocks' ) !== null ? sanitize_text_field( $request->get_param( 'daim_protected_gutenberg_custom_blocks' ) ) : null;
		$options['daim_protected_gutenberg_custom_void_blocks'] = $request->get_param( 'daim_protected_gutenberg_custom_void_blocks' ) !== null ? sanitize_text_field( $request->get_param( 'daim_protected_gutenberg_custom_void_blocks' ) ) : null;

		// Defaults.
		$options['daim_default_category_id']         = $request->get_param( 'daim_default_category_id' ) !== null ? intval( $request->get_param( 'daim_default_category_id' ), 10 ) : null;
		$options['daim_default_title']               = $request->get_param( 'daim_default_title' ) !== null ? sanitize_text_field( $request->get_param( 'daim_default_title' ) ) : null;
		$options['daim_default_open_new_tab']        = $request->get_param( 'daim_default_open_new_tab' ) !== null ? intval( $request->get_param( 'daim_default_open_new_tab' ), 10 ) : null;
		$options['daim_default_use_nofollow']        = $request->get_param( 'daim_default_use_nofollow' ) !== null ? intval( $request->get_param( 'daim_default_use_nofollow' ), 10 ) : null;
		$options['daim_default_activate_post_types'] = $request->get_param( 'daim_default_activate_post_types' ) !== null && is_array($request->get_param( 'daim_default_activate_post_types' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_default_activate_post_types' ) ) : null;
		$options['daim_default_categories']          = $request->get_param( 'daim_default_categories' ) !== null && is_array($request->get_param( 'daim_default_categories' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_default_categories' ) ) : null;
		$options['daim_default_tags']                = $request->get_param( 'daim_default_tags' ) !== null && is_array($request->get_param( 'daim_default_tags' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_default_tags' ) ) : null;
		$options['daim_default_term_group_id']       = $request->get_param( 'daim_default_term_group_id' ) !== null ? intval( $request->get_param( 'daim_default_term_group_id' ), 10 ) : null;
		$options['daim_default_case_insensitive_search']          = $request->get_param( 'daim_default_case_insensitive_search' ) !== null ? intval( $request->get_param( 'daim_default_case_insensitive_search' ), 10 ) : null;
		$options['daim_default_string_before']                    = $request->get_param( 'daim_default_string_before' ) !== null ? intval( $request->get_param( 'daim_default_string_before' ), 10 ) : null;
		$options['daim_default_string_after']                     = $request->get_param( 'daim_default_string_after' ) !== null ? intval( $request->get_param( 'daim_default_string_after' ), 10 ) : null;
		$options['daim_default_keyword_before']                   = $request->get_param( 'daim_default_keyword_before' ) !== null ? sanitize_text_field( $request->get_param( 'daim_default_keyword_before' ) ) : null;
		$options['daim_default_keyword_after']                    = $request->get_param( 'daim_default_keyword_after' ) !== null ? sanitize_text_field( $request->get_param( 'daim_default_keyword_after' ) ) : null;
		$options['daim_default_max_number_autolinks_per_keyword'] = $request->get_param( 'daim_default_max_number_autolinks_per_keyword' ) !== null ? intval( $request->get_param( 'daim_default_max_number_autolinks_per_keyword' ), 10 ) : null;
		$options['daim_default_priority']                         = $request->get_param( 'daim_default_priority' ) !== null ? intval( $request->get_param( 'daim_default_priority' ), 10 ) : null;

		// Suggestions --------------------------------------------------------------------------------------------.

		// Options.
		$options['daim_suggestions_pool_post_types'] = $request->get_param( 'daim_suggestions_pool_post_types' ) !== null && is_array($request->get_param( 'daim_suggestions_pool_post_types' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_suggestions_pool_post_types' ) ) : null;
		$options['daim_suggestions_pool_size']       = $request->get_param( 'daim_suggestions_pool_size' ) !== null ? intval( $request->get_param( 'daim_suggestions_pool_size' ), 10 ) : null;
		$options['daim_suggestions_titles']          = $request->get_param( 'daim_suggestions_titles' ) !== null ? sanitize_key( $request->get_param( 'daim_suggestions_titles' ) ) : null;
		$options['daim_suggestions_categories']      = $request->get_param( 'daim_suggestions_categories' ) !== null ? sanitize_key( $request->get_param( 'daim_suggestions_categories' ) ) : null;
		$options['daim_suggestions_tags']            = $request->get_param( 'daim_suggestions_tags' ) !== null ? sanitize_key( $request->get_param( 'daim_suggestions_tags' ) ) : null;
		$options['daim_suggestions_post_type']       = $request->get_param( 'daim_suggestions_post_type' ) !== null ? sanitize_key( $request->get_param( 'daim_suggestions_post_type' ) ) : null;

		// Link Analysis ------------------------------------------------------------------------------------------.

		// Juice.
		$options['daim_default_seo_power']                = $request->get_param( 'daim_default_seo_power' ) !== null ? intval( $request->get_param( 'daim_default_seo_power' ), 10 ) : null;
		$options['daim_penality_per_position_percentage'] = $request->get_param( 'daim_penality_per_position_percentage' ) !== null ? intval( $request->get_param( 'daim_penality_per_position_percentage' ), 10 ) : null;
		$options['daim_remove_link_to_anchor']            = $request->get_param( 'daim_remove_link_to_anchor' ) !== null ? intval( $request->get_param( 'daim_remove_link_to_anchor' ), 10 ) : null;
		$options['daim_remove_url_parameters']            = $request->get_param( 'daim_remove_url_parameters' ) !== null ? intval( $request->get_param( 'daim_remove_url_parameters' ), 10 ) : null;

		// Technical Options.
		$options['daim_set_max_execution_time']   = $request->get_param( 'daim_set_max_execution_time' ) !== null ? intval( $request->get_param( 'daim_set_max_execution_time' ), 10 ) : null;
		$options['daim_max_execution_time_value'] = $request->get_param( 'daim_max_execution_time_value' ) !== null ? intval( $request->get_param( 'daim_max_execution_time_value' ), 10 ) : null;
		$options['daim_set_memory_limit']         = $request->get_param( 'daim_set_memory_limit' ) !== null ? intval( $request->get_param( 'daim_set_memory_limit' ), 10 ) : null;
		$options['daim_memory_limit_value']       = $request->get_param( 'daim_memory_limit_value' ) !== null ? intval( $request->get_param( 'daim_memory_limit_value' ), 10 ) : null;
		$options['daim_limit_posts_analysis']     = $request->get_param( 'daim_limit_posts_analysis' ) !== null ? intval( $request->get_param( 'daim_limit_posts_analysis' ), 10 ) : null;
		$options['daim_dashboard_post_types']     = $request->get_param( 'daim_dashboard_post_types' ) !== null && is_array($request->get_param( 'daim_dashboard_post_types' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_dashboard_post_types' ) ) : null;
		$options['daim_juice_post_types']         = $request->get_param( 'daim_juice_post_types' ) !== null && is_array($request->get_param( 'daim_juice_post_types' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_juice_post_types' ) ) : null;
		$options['daim_http_status_post_types']   = $request->get_param( 'daim_http_status_post_types' ) !== null && is_array($request->get_param( 'daim_http_status_post_types' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_http_status_post_types' ) ) : null;

		// Advanced -----------------------------------------------------------------------------------------------.

		// Click Tracking.
		$options['daim_track_internal_links'] = $request->get_param( 'daim_track_internal_links' ) !== null ? intval( $request->get_param( 'daim_track_internal_links' ), 10 ) : null;

		// Optimization Parameters.
		$options['daim_optimization_num_of_characters'] = $request->get_param( 'daim_optimization_num_of_characters' ) !== null ? intval( $request->get_param( 'daim_optimization_num_of_characters' ), 10 ) : null;
		$options['daim_optimization_delta']             = $request->get_param( 'daim_optimization_delta' ) !== null ? intval( $request->get_param( 'daim_optimization_delta' ), 10 ) : null;

		// Meta boxes.
		$options['daim_interlinks_options_post_types']      = $request->get_param( 'daim_interlinks_options_post_types' ) !== null && is_array($request->get_param( 'daim_interlinks_options_post_types' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_interlinks_options_post_types' ) ) : null;
		$options['daim_interlinks_optimization_post_types'] = $request->get_param( 'daim_interlinks_optimization_post_types' ) !== null && is_array($request->get_param( 'daim_interlinks_optimization_post_types' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_interlinks_optimization_post_types' ) ) : null;
		$options['daim_interlinks_suggestions_post_types']  = $request->get_param( 'daim_interlinks_suggestions_post_types' ) !== null && is_array($request->get_param( 'daim_interlinks_suggestions_post_types' )) ? array_map( 'sanitize_text_field', $request->get_param( 'daim_interlinks_suggestions_post_types' ) ) : null;

		// Capabilities.
		$options['daim_dashboard_menu_required_capability']             = $request->get_param( 'daim_dashboard_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_dashboard_menu_required_capability' ) ) : null;
		$options['daim_juice_menu_required_capability']                 = $request->get_param( 'daim_juice_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_juice_menu_required_capability' ) ) : null;
		$options['daim_hits_menu_required_capability']                  = $request->get_param( 'daim_hits_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_hits_menu_required_capability' ) ) : null;
		$options['daim_http_status_menu_required_capability']           = $request->get_param( 'daim_http_status_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_http_status_menu_required_capability' ) ) : null;
		$options['daim_wizard_menu_required_capability']                = $request->get_param( 'daim_wizard_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_wizard_menu_required_capability' ) ) : null;
		$options['daim_ail_menu_required_capability']                   = $request->get_param( 'daim_ail_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_ail_menu_required_capability' ) ) : null;
		$options['daim_categories_menu_required_capability']            = $request->get_param( 'daim_categories_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_categories_menu_required_capability' ) ) : null;
		$options['daim_term_groups_menu_required_capability']           = $request->get_param( 'daim_term_groups_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_term_groups_menu_required_capability' ) ) : null;
		$options['daim_tools_menu_required_capability']                = $request->get_param( 'daim_tools_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_tools_menu_required_capability' ) ) : null;
		$options['daim_maintenance_menu_required_capability']           = $request->get_param( 'daim_maintenance_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_maintenance_menu_required_capability' ) ) : null;
		$options['daim_interlinks_options_mb_required_capability']      = $request->get_param( 'daim_interlinks_options_mb_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_interlinks_options_mb_required_capability' ) ) : null;
		$options['daim_interlinks_optimization_mb_required_capability'] = $request->get_param( 'daim_interlinks_optimization_mb_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_interlinks_optimization_mb_required_capability' ) ) : null;
		$options['daim_interlinks_suggestions_mb_required_capability']  = $request->get_param( 'daim_interlinks_suggestions_mb_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daim_interlinks_suggestions_mb_required_capability' ) ) : null;

		// HTTP Status.
		$options['daim_http_status_checks_per_iteration']   = $request->get_param( 'daim_http_status_checks_per_iteration' ) !== null ? intval( $request->get_param( 'daim_http_status_checks_per_iteration' ), 10 ) : null;
		$options['daim_http_status_cron_schedule_interval'] = $request->get_param( 'daim_http_status_cron_schedule_interval' ) !== null ? intval( $request->get_param( 'daim_http_status_cron_schedule_interval' ), 10 ) : null;
		$options['daim_http_status_request_timeout']        = $request->get_param( 'daim_http_status_request_timeout' ) !== null ? intval( $request->get_param( 'daim_http_status_request_timeout' ), 10 ) : null;

		// Misc.
		$options['daim_wizard_rows']        = $request->get_param( 'daim_wizard_rows' ) !== null ? intval( $request->get_param( 'daim_wizard_rows' ), 10 ) : null;
		$options['daim_supported_terms']    = $request->get_param( 'daim_supported_terms' ) !== null ? intval( $request->get_param( 'daim_supported_terms' ), 10 ) : null;
		$options['daim_protect_attributes'] = $request->get_param( 'daim_protect_attributes' ) !== null ? intval( $request->get_param( 'daim_protect_attributes' ), 10 ) : null;

		// Pagination.
		$options['daim_pagination_dashboard_menu']   = $request->get_param( 'daim_pagination_dashboard_menu' ) !== null ? intval( $request->get_param( 'daim_pagination_dashboard_menu' ), 10 ) : null;
		$options['daim_pagination_juice_menu']       = $request->get_param( 'daim_pagination_juice_menu' ) !== null ? intval( $request->get_param( 'daim_pagination_juice_menu' ), 10 ) : null;
		$options['daim_pagination_http_status_menu'] = $request->get_param( 'daim_pagination_http_status_menu' ) !== null ? intval( $request->get_param( 'daim_pagination_http_status_menu' ), 10 ) : null;
		$options['daim_pagination_hits_menu']        = $request->get_param( 'daim_pagination_hits_menu' ) !== null ? intval( $request->get_param( 'daim_pagination_hits_menu' ), 10 ) : null;
		$options['daim_pagination_ail_menu']         = $request->get_param( 'daim_pagination_ail_menu' ) !== null ? intval( $request->get_param( 'daim_pagination_ail_menu' ), 10 ) : null;
		$options['daim_pagination_categories_menu']  = $request->get_param( 'daim_pagination_categories_menu' ) !== null ? intval( $request->get_param( 'daim_pagination_categories_menu' ), 10 ) : null;
		$options['daim_pagination_term_groups_menu'] = $request->get_param( 'daim_pagination_term_groups_menu' ) !== null ? intval( $request->get_param( 'daim_pagination_term_groups_menu' ), 10 ) : null;


		// Tab - License ----------------------------------------------------------------------------------------------.

		// License Management -------------------------------------------------------------------------------.
		$options['daim_license_provider'] = $request->get_param( 'daim_license_provider' ) !== null ? sanitize_key( $request->get_param( 'daim_license_provider' ) ) : null;
		$options['daim_license_key']      = $request->get_param( 'daim_license_key' ) !== null ? sanitize_key( $request->get_param( 'daim_license_key' ) ) : null;

		foreach ( $options as $key => $option ) {
			if ( null !== $option ) {
				update_option( $key, $option );
			}
		}

		require_once $this->shared->get( 'dir' ) . 'vendor/autoload.php';
		$plugin_update_checker = new PluginUpdateChecker(DAIM_PLUGIN_UPDATE_CHECKER_SETTINGS);

		// Delete the transient used to store the plugin info previously retrieved from the remote server.
		$plugin_update_checker->delete_transient();

		// Fetch the plugin information from the remote server and saved it in the transient.
		$plugin_update_checker->fetch_remote_plugin_info();

		return new WP_REST_Response( 'Data successfully added.', '200' );

	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_update_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to update the Interlinks Manager options.',
				array( 'status' => 403 )
			);
		}

		return true;

	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/statistics' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Statistics" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_read_statistics_callback( $request ) {

		$data_update_required = intval( $request->get_param( 'data_update_required' ), 10 );

		if ( 0 === $data_update_required ) {

			// Use the provided form data.
			$optimization_status = intval( $request->get_param( 'optimization_status' ), 10 );
			$search_string       = sanitize_text_field( $request->get_param( 'search_string' ) );
			$sorting_column      = sanitize_text_field( $request->get_param( 'sorting_column' ) );
			$sorting_order       = sanitize_text_field( $request->get_param( 'sorting_order' ) );

		} else {

			// Set the default values of the form data.
			$optimization_status = 0;
			$search_string       = '';
			$sorting_column      = 'post_date';
			$sorting_order       = 'desc';

			// Run update_interlinks_archive() to update the archive with the statistics.
			$this->shared->update_interlinks_archive();

		}

		// Create the WHERE part of the query based on the $optimization_status value.
		global $wpdb;
		switch ( $optimization_status ) {
			case 0:
				$filter = '';
				break;
			case 1:
				$filter = 'WHERE optimization = 0';
				break;
			case 2:
				$filter = 'WHERE optimization = 1';
				break;
			default:
				$filter = '';
		}

		// Create the WHERE part of the string based on the $search_string value.
		if ( '' !== $search_string ) {
			if ( strlen( $filter ) === 0 ) {
				$filter .= $wpdb->prepare( 'WHERE (post_title LIKE %s)', '%' . $search_string . '%' );
			} else {
				$filter .= $wpdb->prepare( ' AND (post_title LIKE %s)', '%' . $search_string . '%' );

			}
		}

		// Create the ORDER BY part of the query based on the $sorting_column and $sorting_order values.
		if ( '' !== $sorting_column ) {
			$filter .= $wpdb->prepare( ' ORDER BY %i', $sorting_column );
		} else {
			$filter .= ' ORDER BY post_date';
		}

		if ( 'desc' === $sorting_order ) {
			$filter .= ' DESC';
		} else {
			$filter .= ' ASC';
		}

		// Get the data from the "_archive" db table using $wpdb and put them in the $response array.

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is prepared.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$requests = $wpdb->get_results( "
			SELECT *
			FROM {$wpdb->prefix}daim_archive $filter" );
		// phpcs:enable

		if ( is_array( $requests ) && count( $requests ) > 0 ) {

			/**
			 * Add the formatted date (based on the date format defined in the WordPress settings) to the $requests
			 * array.
			 */
			foreach ( $requests as $key => $request ) {
				$requests[ $key ]->formatted_post_date = mysql2date( get_option('date_format') , $request->post_date );
			}

			$response = array(
				'statistics' => array(
					'all_posts'   => count( $requests ),
					'average_mil' => $this->shared->get_average_mil( $requests ),
					'average_ail' => $this->shared->get_average_ail( $requests ),
				),
				'table'      => $requests,
			);

		} else {

			$response = array(
				'statistics' => array(
					'all_posts'   => 0,
					'average_mil' => 'N/A',
					'average_ail' => 'N/A',
				),
				'table'      => array(),
			);

		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_read_statistics_callback_permission_check() {

		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_dashboard_menu_required_capability' ) ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to read the Interlinks Manager statistics.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/juice' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Juice" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_read_juice_callback( $request ) {

		$data_update_required = intval( $request->get_param( 'data_update_required' ), 10 );

		if ( 0 === $data_update_required ) {

			// Use the provided form data.
			$search_string  = sanitize_text_field( $request->get_param( 'search_string' ) );
			$sorting_column = sanitize_text_field( $request->get_param( 'sorting_column' ) );
			$sorting_order  = sanitize_text_field( $request->get_param( 'sorting_order' ) );

		} else {

			// Set the default values of the form data.
			$search_string  = '';
			$sorting_column = 'juice';
			$sorting_order  = 'desc';

			// Update the juice archive.
			$this->shared->update_juice_archive();

		}

		// Create the WHERE part of the string based on the $search_string value.
		$filter = '';
		global $wpdb;
		if ( '' !== $search_string ) {
			if ( strlen( $filter ) === 0 ) {
				$filter .= $wpdb->prepare( 'WHERE (url LIKE %s)', '%' . $search_string . '%' );
			} else {
				$filter .= $wpdb->prepare( ' AND (url LIKE %s)', '%' . $search_string . '%' );

			}
		}

		// Create the ORDER BY part of the query based on the $sorting_column and $sorting_order values.
		if ( '' !== $sorting_column ) {
			$filter .= $wpdb->prepare( ' ORDER BY %i', $sorting_column );
		} else {
			$filter .= ' ORDER BY url';
		}

		if ( 'desc' === $sorting_order ) {
			$filter .= ' DESC';
		} else {
			$filter .= ' ASC';
		}

		// Get the data from the "_archive" db table using $wpdb and put them in the $response array.

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is prepared.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$requests   = $wpdb->get_results(
			"
			SELECT *
			FROM {$wpdb->prefix}daim_juice $filter"
		);
		// phpcs:enable

		if ( is_array( $requests ) && count( $requests ) > 0 ) {
			$response = array(
				'statistics' => array(
					'all_urls'      => count( $requests ),
					'average_iil'   => $this->shared->get_average_iil( $requests ),
					'average_juice' => $this->shared->get_average_juice( $requests ),
				),
				'table'      => $requests,
			);
		} else {
			$response = array(
				'statistics' => array(
					'all_urls'      => 0,
					'average_iil'   => 'N/A',
					'average_juice' => 'N/A',
				),
				'table'      => array(),
			);
		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_read_juice_callback_permission_check() {

		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_juice_menu_required_capability' ) ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to read the Interlinks Manager statistics.',
				array( 'status' => 403 )
			);
		}

		return true;

	}

	/**
	 * Ajax handler used to generate the modal window used to display and browse the anchors associated with a specific
	 *  url.
	 *
	 *  This method is called when in the "Juice" menu one of these elements is clicked:
	 *  - The modal window icon associate with a specific URL
	 *  - One of the pagination links included in the modal window
	 *
	 * @param object $request The request data.
	 *
	 * @return void
	 */
	public function rest_api_interlinks_manager_pro_read_juice_url_callback( $request ) {

		// Init Variables.
		$data      = array();
		$juice_max = 0;

		$juice_id = sanitize_text_field( $request->get_param( 'id' ) );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$juice_obj  = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daim_juice WHERE id = %d", $juice_id )
			, OBJECT );

		// Body -------------------------------------------------------------------------------------------------------.

		// Get the maximum value of the juice.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results    = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daim_anchors WHERE url = %s ORDER BY id ASC", $juice_obj->url )
			, ARRAY_A );

		if ( count( $results ) > 0 ) {

			// Calculate the maximum value.
			foreach ( $results as $result ) {
				if ( $result['juice'] > $juice_max ) {
					$juice_max = $result['juice'];
				}
			}
		} else {

			echo 'no data';
			die();

		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results    = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daim_anchors WHERE url = %s ORDER BY juice DESC", $juice_obj->url )
			, ARRAY_A );

		if ( count( $results ) > 0 ) {

			foreach ( $results as $result ) {

				$data[] = array(
					'id'            => $result['id'],
					'postTitle'     => $result['post_title'],
					'juice'         => intval( $result['juice'], 10 ),
					'juiceVisual'   => intval( 100 * $result['juice'] / $juice_max, 10 ),
					'anchor'        => $result['anchor'],
					'postId'        => intval( $result['post_id'], 10 ),
					'postPermalink' => $result['post_permalink'],
					'postEditLink' => $result['post_edit_link'],
				);

			}
		} else {

			echo 'no data';
			die();

		}

		// Return respose.
		echo wp_json_encode( $data );
		die();
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_read_juice_url_callback_permission_check() {

		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_juice_menu_required_capability' ) ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to read the Interlinks Manager statistics.',
				array( 'status' => 403 )
			);
		}

		return true;

	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/http-status' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "HTTP Status" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_read_http_status_callback( $request ) {

		$data_update_required = intval( $request->get_param( 'data_update_required' ), 10 );

		if ( 0 === $data_update_required ) {

			// Use the provided form data.
			$status_code    = sanitize_key( $request->get_param( 'status_code' ) );
			$search_string  = sanitize_text_field( $request->get_param( 'search_string' ) );
			$sorting_column = sanitize_text_field( $request->get_param( 'sorting_column' ) );
			$sorting_order  = sanitize_text_field( $request->get_param( 'sorting_order' ) );

		} else {

			// Set the default values of the form data.
			$status_code    = 'all';
			$search_string  = '';
			$sorting_column = 'last_check_date';
			$sorting_order  = 'desc';

			// Update the HTTP Status Archive.
			$this->shared->update_http_status_archive();

		}

		// Create the WHERE part of the query based on the $optimization_status value.
		global $wpdb;
		switch ( $status_code ) {
			case 0:
				$filter = '';
				break;
			default:
				$filter = 'WHERE code = $status_code';
				break;
		}

		// status code.
		if ( ! is_null( $status_code ) &&
			( trim( $status_code ) !== 'all' ) ) {

			switch ( trim( $status_code ) ) {

				case 'unknown':
					$filter = "WHERE code = ''";
					break;

				case '1xx':
					$filter  = "WHERE (code = '100'";
					$filter .= " OR code = '101'";
					$filter .= " OR code = '102'";
					$filter .= " OR code = '103')";
					break;

				case '2xx':
					$filter  = "WHERE (code = '200'";
					$filter .= " OR code = '201'";
					$filter .= " OR code = '202'";
					$filter .= " OR code = '203'";
					$filter .= " OR code = '204'";
					$filter .= " OR code = '205'";
					$filter .= " OR code = '206'";
					$filter .= " OR code = '207'";
					$filter .= " OR code = '208'";
					$filter .= " OR code = '226')";
					break;

				case '3xx':
					$filter  = "WHERE (code = '300'";
					$filter .= " OR code = '301'";
					$filter .= " OR code = '302'";
					$filter .= " OR code = '303'";
					$filter .= " OR code = '304'";
					$filter .= " OR code = '305'";
					$filter .= " OR code = '305'";
					$filter .= " OR code = '306'";
					$filter .= " OR code = '307'";
					$filter .= " OR code = '308')";
					break;

				case '4xx':
					$filter  = "WHERE (code = '400'";
					$filter .= " OR code = '401'";
					$filter .= " OR code = '402'";
					$filter .= " OR code = '403'";
					$filter .= " OR code = '404'";
					$filter .= " OR code = '405'";
					$filter .= " OR code = '406'";
					$filter .= " OR code = '407'";
					$filter .= " OR code = '408'";
					$filter .= " OR code = '409'";
					$filter .= " OR code = '410'";
					$filter .= " OR code = '411'";
					$filter .= " OR code = '412'";
					$filter .= " OR code = '413'";
					$filter .= " OR code = '414'";
					$filter .= " OR code = '415'";
					$filter .= " OR code = '416'";
					$filter .= " OR code = '417'";
					$filter .= " OR code = '418'";
					$filter .= " OR code = '421'";
					$filter .= " OR code = '422'";
					$filter .= " OR code = '423'";
					$filter .= " OR code = '424'";
					$filter .= " OR code = '426'";
					$filter .= " OR code = '428'";
					$filter .= " OR code = '429'";
					$filter .= " OR code = '431'";
					$filter .= " OR code = '451')";

					break;

				case '5xx':
					$filter  = "WHERE (code = '500'";
					$filter .= " OR code = '501'";
					$filter .= " OR code = '502'";
					$filter .= " OR code = '503'";
					$filter .= " OR code = '504'";
					$filter .= " OR code = '505'";
					$filter .= " OR code = '506'";
					$filter .= " OR code = '507'";
					$filter .= " OR code = '508'";
					$filter .= " OR code = '510'";
					$filter .= " OR code = '511')";

					break;

			}
		} else {
			$filter = '';
		}

		// Create the WHERE part of the string based on the $search_string value.
		if ( '' !== $search_string ) {
			if ( strlen( $filter ) === 0 ) {
				$filter .= $wpdb->prepare( 'WHERE (post_title LIKE %s)', '%' . $search_string . '%' );
			} else {
				$filter .= $wpdb->prepare( ' AND (post_title LIKE %s)', '%' . $search_string . '%' );

			}
		}

		// Create the ORDER BY part of the query based on the $sorting_column and $sorting_order values.
		if ( '' !== $sorting_column ) {
			$filter .= $wpdb->prepare( ' ORDER BY %i', $sorting_column );
		} else {
			$filter .= ' ORDER BY last_check_date';
		}

		if ( 'desc' === $sorting_order ) {
			$filter .= ' DESC';
		} else {
			$filter .= ' ASC';
		}

		/**
		 * Check in the "_http_status" db table if all the links have been checked. (if there are zero links to
		 * check)
		 */
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$count       = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_http_status WHERE checked = 0" );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$count_total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daim_http_status" );

		// Get the data from the "_archive" db table using $wpdb and put them in the $response array.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is prepared.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$requests   = $wpdb->get_results(
			"SELECT *
			FROM {$wpdb->prefix}daim_http_status $filter"
		);
		// phpcs:enable

		if ( is_array( $requests ) && count( $requests ) > 0 ) {

			/**
			 * Add the formatted date (based on the date format defined in the WordPress settings) to the $requests
			 * array.
			 */
			foreach ( $requests as $key => $request ) {
				$requests[ $key ]->formatted_last_check_date = mysql2date( get_option('date_format') . ' ' . get_option('time_format') , $request->last_check_date );
			}

			$response = array(
				'statistics' => array(
					'all_posts'            => count( $requests ),
					'successful_responses' => $this->shared->get_successful_responses( $requests ),
				),
				'table'      => $requests,
			);
		} else {
			$response = array(
				'statistics' => array(
					'all_posts'            => 0,
					'successful_responses' => 0,
				),
				'table'      => array(),
			);
		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_read_http_status_callback_permission_check() {

		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_http_status_menu_required_capability' ) ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to read the Interlinks Manager statistics.',
				array( 'status' => 403 )
			);
		}

		return true;

	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/hits' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Hits" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_read_hits_callback( $request ) {

		$search_string  = sanitize_text_field( $request->get_param( 'search_string' ) );
		$sorting_column = sanitize_text_field( $request->get_param( 'sorting_column' ) );
		$sorting_order  = sanitize_text_field( $request->get_param( 'sorting_order' ) );

		$filter = '';
		global $wpdb;

		// Create the WHERE part of the string based on the $search_string value.
		if ( '' !== $search_string ) {
			if ( strlen( $filter ) === 0 ) {
				$filter .= $wpdb->prepare( 'WHERE (post_title LIKE %s OR target_url LIKE %s)', '%' . $search_string . '%', '%' . $search_string . '%' );
			} else {
				$filter .= $wpdb->prepare( ' AND (post_title LIKE %s OR target_url LIKE %s)', '%' . $search_string . '%', '%' . $search_string . '%' );
			}
		}

		// Create the ORDER BY part of the query based on the $sorting_column and $sorting_order values.
		if ( '' !== $sorting_column ) {
			$filter .= $wpdb->prepare( ' ORDER BY %i', $sorting_column );
		} else {
			$filter .= ' ORDER BY id';
		}

		if ( 'desc' === $sorting_order ) {
			$filter .= ' DESC';
		} else {
			$filter .= ' ASC';
		}

		// Get the data from the "_archive" db table using $wpdb and put them in the $response array.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is already prepared.
		$requests   = $wpdb->get_results(
			"SELECT *
			FROM {$wpdb->prefix}daim_hits $filter"
		);
		// phpcs:enable

		if ( is_array( $requests ) && count( $requests ) > 0 ) {

			/**
			 * Add the formatted date (based on the date format defined in the WordPress settings) to the $requests
			 * array.
			 */
			foreach ( $requests as $key => $request ) {
				$requests[ $key ]->formatted_date = mysql2date( get_option('date_format') . ' ' . get_option('time_format') , $request->date );
			}

			$response = array(
				'statistics' => array(
					'all_clicks'           => count( $requests ),
					'autolinks_percentage' => $this->shared->get_hits_autolinks_percentage( $requests ),
				),
				'table'      => $requests,
			);
		} else {
			$response = array(
				'statistics' => array(
					'all_clicks'           => 0,
					'autolinks_percentage' => 'N/A',
				),
				'table'      => array(),
			);
		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_read_hits_callback_permission_check() {

		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_hits_menu_required_capability' ) ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to read the Interlinks Manager statistics.',
				array( 'status' => 403 )
			);
		}

		return true;

	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/hits' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Hits" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_dashboard_menu_export_csv_callback( $request ) {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		// get the data from the db table.
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$results    = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daim_archive ORDER BY post_date DESC", ARRAY_A );

		// if there are data generate the csv header and content.
		if ( count( $results ) > 0 ) {

			$csv_content = '';
			$new_line    = "\n";

			// set the csv header.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=dashboard-' . time() . '.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// set headings.
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Post', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Date', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Type', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Length', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Manual IL', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Auto IL', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Int. Inbound Links', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Recomm.', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Clicks', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Opt.', 'interlinks-manager') ) . '"';
			$csv_content .= $new_line;

			// set column content.
			foreach ( $results as $result ) {

				$csv_content .= '"' . $this->shared->esc_csv( $result['post_title'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( mysql2date( get_option( 'date_format' ), $result['post_date'] ) ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['post_type'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['content_length'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['manual_interlinks'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['auto_interlinks'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['iil'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['recommended_interlinks'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['num_il_clicks'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['optimization'] ) . '"';
				$csv_content .= $new_line;

			}
		} else {
			return false;
		}

		$response = array(
			'csv_content' => $csv_content,
		);

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/hits' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Hits" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_juice_menu_export_csv_callback() {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		// get the data from the db table.
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$results    = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daim_juice ORDER BY juice DESC", ARRAY_A );

		// if there are data generate the csv header and content.
		if ( count( $results ) > 0 ) {

			$csv_content = '';
			$new_line    = "\n";

			// set the csv header.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=juice-' . time() . '.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// set headings.
			$csv_content .= '"' . $this->shared->esc_csv( __( 'URL', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Internal Inbound Links', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Juice', 'interlinks-manager') ) . '"';
			$csv_content .= $new_line;

			// set column content.
			foreach ( $results as $result ) {

				$csv_content .= '"' . $this->shared->esc_csv( $result['url'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['iil'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['juice'] ) . '"';
				$csv_content .= $new_line;

			}
		} else {
			return false;
		}

		$response = array(
			'csv_content' => $csv_content,
		);

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/hits' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Hits" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_anchors_menu_export_csv_callback( $request ) {

		$url = sanitize_text_field( $request->get_param( 'url' ) );

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		// get the URL.
		$url = esc_url_raw( urldecode( $url ) );

		// get the data from the db table.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results    = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daim_anchors WHERE url = %s ORDER BY juice DESC", $url )
			, ARRAY_A );

		// if there are data generate the csv header and content.
		if ( count( $results ) > 0 ) {

			$csv_content = '';
			$new_line    = "\n";

			// set the csv header.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=juice-details-' . time() . '.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// set headings.
			$csv_content .= '"' . $this->shared->esc_csv( __( 'URL', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Post', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Anchor Text', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Juice', 'interlinks-manager') ) . '"';

			$csv_content .= $new_line;

			// set column content.
			foreach ( $results as $result ) {

				$csv_content .= '"' . $this->shared->esc_csv( $result['url'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['post_title'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['anchor'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['juice'] ) . '"';

				$csv_content .= $new_line;

			}
		} else {
			return false;
		}

		$response = array(
			'csv_content' => $csv_content,
		);

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/hits' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Hits" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_http_status_menu_export_csv_callback() {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		// get the data from the db table.
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$results    = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daim_http_status ORDER BY last_check_date DESC", ARRAY_A );

		// if there are data generate the csv header and content.
		if ( count( $results ) > 0 ) {

			$csv_content = '';
			$new_line    = "\n";

			// set the csv header.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=http-response-' . time() . '.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// set headings.
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Post', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Anchor Text', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'URL', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Status Code', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Last Check', 'interlinks-manager') ) . '"';
			$csv_content .= $new_line;

			// set column content.
			foreach ( $results as $result ) {

				$csv_content .= '"' . $this->shared->esc_csv( $result['post_title'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['anchor'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['url'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['code'] . ' ' . $result['code_description'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( mysql2date( get_option('date_format') . ' ' . get_option('time_format'), $result['last_check_date'] ) ) . '"';
				$csv_content .= $new_line;

			}
		} else {
			return false;
		}

		$response = array(
			'csv_content' => $csv_content,
		);

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/generate-interlinks-suggestions' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Hits" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_hits_menu_export_csv_callback() {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		// get the data from the db table.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results    = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daim_hits ORDER BY date DESC", ARRAY_A );

		// if there are data generate the csv header and content.
		if ( count( $results ) > 0 ) {

			$csv_content = '';
			$new_line    = "\n";

			// set the csv header.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=hits-' . time() . '.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// set headings.
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Tracking ID', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Post', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Date', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Target', 'interlinks-manager') ) . '",';
			$csv_content .= '"' . $this->shared->esc_csv( __( 'Type', 'interlinks-manager') ) . '"';
			$csv_content .= $new_line;

			// set column content.
			foreach ( $results as $result ) {

				$csv_content .= '"' . $this->shared->esc_csv( $result['id'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['post_title'] ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( mysql2date( get_option('date_format') . ' ' . get_option('time_format'), $result['date'] ) ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( stripslashes( $result['target_url'] ) ) . '",';
				$csv_content .= '"' . $this->shared->esc_csv( $result['link_type'] == 0 ? 'AIL' : 'MIL' ) . '"';
				$csv_content .= $new_line;

			}
		} else {
			return false;
		}

		$response = array(
			'csv_content' => $csv_content,
		);

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/hits' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Hits" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_generate_interlinks_suggestions_callback() {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		// Get the post id for which the suggestions should be generated.
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'], 10 ) : null;

		// Get the options values.
		$option_title      = get_option( $this->shared->get( 'slug' ) . '_suggestions_titles' );// consider, ignore.
		$option_post_type  = get_option( $this->shared->get( 'slug' ) . '_suggestions_post_type' );// require, consider, ignore.
		$option_categories = get_option( $this->shared->get( 'slug' ) . '_suggestions_categories' );// require, consider, ignore.
		$option_tags       = get_option( $this->shared->get( 'slug' ) . '_suggestions_tags' );// require, consider, ignore.

		/**
		 * Create a query to get the posts that belong to the selected
		 * 'Pool Post Types'.
		 */
		$post_types_a          = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_suggestions_pool_post_types' ) );
		$pool_post_types_query = '';
		if ( is_array( $post_types_a ) ) {
			foreach ( $post_types_a as $key => $value ) {

				if ( ! preg_match( '/[a-z0-9_-]+/', $value ) ) {
					continue;}

				$pool_post_types_query .= "post_type = '" . $value . "'";
				if ( count( $post_types_a ) - 1 !== $key ) {
					$pool_post_types_query .= ' or ';}
			}
		}
		if ( strlen( $pool_post_types_query ) > 0 ) {
			$pool_post_types_query = ' AND (' . $pool_post_types_query . ')';}

		/**
		 * Step1: $option_title.
		 *
		 * If $option_title is set to 'consider' compare each word that appears
		 * in the current post title with the ones that appears in every other
		 * available post and increase the score by 10 for each word.
		 *
		 * if $option_title is set to 'ignore' create an array with all the
		 * posts and 0 as the score.
		 *
		 * The array that saves the score is the $posts_ranking_a array.
		 */
		if ( 'consider' === $option_title ) {

			// get the current post title.
			$current_post_title = get_the_title( $post_id );

			/*
			 * Extract all the words from the current post title and save them
			 * in the $shared_words array.
			 */

			/*
			 * Save in $shared_words all the single words available in the title
			 * of the current post
			 */
			$shared_words = explode( ' ', $current_post_title );

			// Remove empty elements from the array.
			$shared_words = array_filter( $shared_words );

			/**
			 * Execute the query to get the posts that belong to the selected
			 * 'Pool Post Types'.
			 */
			global $wpdb;
			$limit_posts_analysis = intval( get_option( $this->shared->get( 'slug' ) . '_limit_posts_analysis' ), 10 );
			// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $pool_post_types_query is already prepared.
			$results = $wpdb->get_results(
				$wpdb->prepare( "SELECT ID, post_type, post_title FROM {$wpdb->prefix}posts WHERE post_status = 'publish' $pool_post_types_query ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis ),
				ARRAY_A
			);
			// phpcs:enable

			/**
			 * Compare each word that appears in the current post title with the
			 * ones that appears in every other available post and increase the
			 * score by 10 for each word
			 */
			foreach ( $results as $key => $single_result ) {

				$score = 0;

				// Assign 10 points for the word matches.
				foreach ( $shared_words as $key => $needle ) {
					if ( strpos( $single_result['post_title'], $needle ) !== false ) {
						$score = $score + 10;
					}
				}

				// Save post data in the $posts_ranking_a array.
				$posts_ranking_a[] = array(
					'id'        => $single_result['ID'],
					'post_type' => $single_result['post_type'],
					'score'     => $score,
				);

			}
		} else {

			// Create an array with all the posts and 0 as score ----------------.
			global $wpdb;
			$limit_posts_analysis = intval( get_option( $this->shared->get( 'slug' ) . '_limit_posts_analysis' ), 10 );

			// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $pool_post_types_query is already prepared.
			$results = $wpdb->get_results(
				$wpdb->prepare( "SELECT ID, post_type FROM {$wpdb->prefix}posts WHERE post_status = 'publish' $pool_post_types_query ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis ),
				ARRAY_A
			);
			// phpcs:enable

			// Cycle through all the posts.
			foreach ( $results as $key => $single_result ) {

				// Save post data in the $posts_ranking_a array.
				$posts_ranking_a[] = array(
					'id'        => $single_result['ID'],
					'post_type' => $single_result['post_type'],
					'score'     => 0,
				);

			}
		}

		/*
		 * step2: $option_post_type
		 *
		 * If $option_post_type is set to 'require' remove from the array
		 * $posts_ranking_a all the posts that don't belong to this post type.
		 *
		 * If $option_post_type is set to 'consider' add 20 to all the posts
		 * that belong to this post type on the $posts_ranking_a array.
		 *
		 * If $option_post_type is set to 'ignore' do nothing.
		 *
		 */

		// Proceed with this step only if the $posts_ranking_a exists and it's not empty.
		if ( isset( $posts_ranking_a ) && ( count( $posts_ranking_a ) > 0 ) ) {

			// Get the post type of this post.
			$current_post_type = get_post_type( $post_id );

			switch ( $option_post_type ) {

				case 'require':
					foreach ( $posts_ranking_a as $pra_key => $pra_value ) {
						if ( $pra_value['post_type'] !== $current_post_type ) {
							unset( $posts_ranking_a[ $pra_key ] );
						}
					}

					break;

				case 'consider':
					foreach ( $posts_ranking_a as $pra_key => $pra_value ) {
						if ( $pra_value['post_type'] === $current_post_type ) {
							$posts_ranking_a[ $pra_key ]['score'] = $posts_ranking_a[ $pra_key ]['score'] + 20;
						}
					}

					break;

				case 'ignore':
					break;

			}
		}

		/*
		 * step3: $option_categories
		 *
		 * If $option_categories is set to 'require' remove from the
		 * $posts_ranking_a array all the posts that don't have any category
		 * that the current post have
		 *
		 * If the $option_categories is set to 'consider' add 20 to all the
		 * posts that have the category that the current post have ( add 20 for
		 * each category found )
		 *
		 * if $option_categories is set to 'ignore' do nothing
		 *
		 * Please note that this option is applied only to the posts that have
		 * the "category" taxonomy and that are associated with one or more
		 * categories
		 */

		// Proceed with this step only if the $posts_ranking_a exists and it's not empty.
		if ( isset( $posts_ranking_a ) && ( count( $posts_ranking_a ) > 0 ) ) {

			if ( in_array( 'category', get_object_taxonomies( get_post_type( $post_id ) ), true ) ) {

				// Get an array with a list of the id of the categories.
				$current_post_categories = wp_get_post_categories( $post_id );

				if ( is_array( $current_post_categories ) && count( $current_post_categories ) > 0 ) {

					switch ( $option_categories ) {

						case 'require':
							foreach ( $posts_ranking_a as $pra_key => $pra_value ) {
								$found                    = false;
								$iterated_post_categories = wp_get_post_categories( $pra_value['id'] );
								foreach ( $current_post_categories as $cpc_key => $cpc_value ) {
									if ( in_array( $cpc_value, $iterated_post_categories, true ) ) {
										$found = true;
									}
								}
								if ( ! $found ) {
									unset( $posts_ranking_a[ $pra_key ] );
								}
							}

							break;

						case 'consider':
							foreach ( $posts_ranking_a as $pra_key => $pra_value ) {
								$found                    = false;
								$iterated_post_categories = wp_get_post_categories( $pra_value['id'] );
								foreach ( $current_post_categories as $cpc_key => $cpc_value ) {
									if ( in_array( $cpc_value, $iterated_post_categories, true ) ) {
										$found = true;
									}
								}
								if ( $found ) {
									$posts_ranking_a[ $pra_key ]['score'] = $posts_ranking_a[ $pra_key ]['score'] + 20;
								}
							}

							break;

						case 'ignore':
							break;

					}
				}
			}
		}

		/*
		 * step4: $option_tags
		 *
		 * If $option_tags is set to 'require' remove from the $posts_ranking_a
		 * array all posts that don't have any tag that the current post have
		 *
		 * If the $option_tags is set to 'consider' add 20 to all the
		 * posts that have the tag that the current post have ( add 20 for
		 * each tag found )
		 *
		 * if $option_tags is set to 'ignore' do nothing
		 *
		 * Please note that this option is applied only to the posts that have
		 * the "post_tag" taxonomy and that are associated with one or more
		 * tags
		 */

		// Proceed with this step only if the $posts_ranking_a exists and it's not empty.
		if ( isset( $posts_ranking_a ) && ( count( $posts_ranking_a ) > 0 ) ) {

			if ( in_array( 'post_tag', get_object_taxonomies( get_post_type( $post_id ) ), true ) ) {

				// Get an array with a list of the id of the categories.
				$current_post_tags = wp_get_post_tags( $post_id );

				if ( is_array( $current_post_tags ) && count( $current_post_tags ) > 0 ) {

					switch ( $option_tags ) {

						case 'require':
							foreach ( $posts_ranking_a as $pra_key => $pra_value ) {
								$found              = false;
								$iterated_post_tags = wp_get_post_tags( $pra_value['id'] );
								foreach ( $current_post_tags as $cpt_key => $cpt_value ) {
									if ( in_array( $cpt_value, $iterated_post_tags, true ) ) {
										$found = true;
									}
								}
								if ( ! $found ) {
									unset( $posts_ranking_a[ $pra_key ] );
								}
							}

							break;

						case 'consider':
							foreach ( $posts_ranking_a as $pra_key => $pra_value ) {
								$found              = false;
								$iterated_post_tags = wp_get_post_tags( $pra_value['id'] );
								foreach ( $current_post_tags as $cpt_key => $cpt_value ) {
									if ( in_array( $cpt_value, $iterated_post_tags, true ) ) {
										$found = true;
									}
								}
								if ( $found ) {
									$posts_ranking_a[ $pra_key ]['score'] = $posts_ranking_a[ $pra_key ]['score'] + 20;
								}
							}

							break;

						case 'ignore':
							break;

					}
				}
			}
		}

		if ( ! isset( $posts_ranking_a ) || count( $posts_ranking_a ) <= 5 ) {

			return new WP_REST_Response( [ 'message' => 'No suggestions found.' ], 200 );

		}

		/**
		 * Remove the current post from the $post_ranking_a ( The current post
		 * obviously should not be displayed as an interlinks suggestion ).
		 */
		foreach ( $posts_ranking_a as $key => $value ) {
			if ( $value['id'] === $post_id ) {
				unset( $posts_ranking_a[ $key ] );
			}
		}

		/*
		 * Order the $post_ranking_a with descending order based on the 'score'
		 */
		usort( $posts_ranking_a, array( $this->shared, 'usort_callback_1' ) );

		/*
		 * Create the $id_list_a[] array with the reference to the first
		 * $pool_size elements of $posts_ranking_a
		 */
		$id_list_a = array();
		$counter   = 1;
		$pool_size = intval( get_option( $this->shared->get( 'slug' ) . '_suggestions_pool_size' ), 10 );
		foreach ( $posts_ranking_a as $key => $value ) {
			if ( $counter > $pool_size ) {
				continue;}
			$id_list_a[] = $value['id'];
			++$counter;
		}

		/*
		 * Get the post URLs and anchors and generate the HTML content of the list
		 * based on the $id_list_a
		 */

		// Generate the list content and take 5 random posts from the pool $id_list_a.
		$random_id_a = array();
		$suggestions = array();

		for ( $i = 1; $i <= 5; $i++ ) {

			/**
			 * Avoid to include the same id multiple times in the list of random
			 * IDs taken from the pool.
			 */
			do {
				$rand_key  = array_rand( $id_list_a, 1 );
				$random_id = $id_list_a[ $rand_key ];
			} while ( in_array( $random_id, $random_id_a, true ) );

			// Get the post type object of the post type associated with the suggested post.
			$post_type_key    = get_post_type( $random_id );
			$post_type_object = get_post_type_object( $post_type_key );

			// Add the suggestion to the array.
			$suggestions[] = array(
				'index'       => $i,
				'link'        => get_permalink( $random_id ),
				'title'       => get_the_title( $random_id ),
				'post_type'   => $post_type_object->labels->singular_name,
				'icon_svg'    => '', // Assuming `get_icon_svg` returns the SVG as a string.
			);

			$random_id_a[] = $random_id;
		}

		// Return the suggestions as a JSON response.
		return new WP_REST_Response( $suggestions, 200 );

	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_generate_interlinks_suggestions_callback_permission_check() {

		if ( ! current_user_can( get_option($this->shared->get('slug') . '_interlinks_suggestions_mb_required_capability') ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to generate internal links suggestions.',
				array( 'status' => 403 )
			);
		}

		return true;

	}

	/**
	 * Callback for the POST 'interlinks-manager-pro/v1/hits' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Hits" menu to retrieve the statistics of the internal links on the posts.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_interlinks_manager_pro_generate_interlinks_optimization_callback($request) {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		// Get the post id for which the suggestions should be generated.
		$post_id = $request->get_param( 'id' ) !== null ? intval( $request->get_param( 'id' ), 10 ) : null;

		// Get the post object.
		$post = get_post( $post_id );

		$data = array();
		$data['suggested_min_number_of_interlinks'] = $this->shared->get_suggested_min_number_of_interlinks( $post->ID );
		$data['suggested_max_number_of_interlinks'] = $this->shared->get_suggested_max_number_of_interlinks( $post->ID );
		$data['post_content_with_autolinks']        = $this->shared->add_autolinks( $post->post_content, false, $post->post_type, $post->ID );
		$data['number_of_manual_interlinks']        = $this->shared->get_manual_interlinks( $post->post_content );
		$data['number_of_autolinks']                = $this->shared->get_autolinks_number( $data['post_content_with_autolinks'] );
		$data['total_number_of_interlinks']         = $data['number_of_manual_interlinks'] + $data['number_of_autolinks'];

		// Return the suggestions as a JSON response.
		return new WP_REST_Response( $data, 200 );

	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_interlinks_manager_pro_generate_interlinks_optimization_callback_permission_check() {

		if ( ! current_user_can( get_option($this->shared->get('slug') . '_interlinks_optimization_mb_required_capability') ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to generate internal links suggestions.',
				array( 'status' => 403 )
			);
		}

		return true;

	}
}
