<?php
/**
 * This file contains the class Daim_Ajax, used to include ajax actions.
 *
 * @package interlinks-manager
 */

/**
 * This class should be used to include ajax actions.
 */
class Daim_Ajax {

	/**
	 * The instance of the Daim_Ajax class.
	 *
	 * @var Daim_Ajax
	 */
	protected static $instance = null;

	/**
	 * The instance of the Daim_Shared class.
	 *
	 * @var Daim_Shared
	 */
	private $shared = null;

	/**
	 * The constructor of the Daim_Ajax class.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daim_Shared::get_instance();

		// Ajax requests --------------------------------------------------------.

		// For logged-in and not-logged-in users --------------------------------.
		add_action( 'wp_ajax_track_internal_link', array( $this, 'track_internal_link' ) );
		add_action( 'wp_ajax_nopriv_track_internal_link', array( $this, 'track_internal_link' ) );

		// For logged-in users --------------------------------------------------.
		add_action( 'wp_ajax_daim_wizard_generate_ail', array( $this, 'daim_wizard_generate_ail' ) );
		add_action( 'wp_ajax_daim_get_taxonomies', array( $this, 'daim_get_taxonomies' ) );
		add_action( 'wp_ajax_daim_get_terms', array( $this, 'daim_get_terms' ) );
	}

	/**
	 * Return an istance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Ajax handler used to track internal links in the front-end.
	 */
	public function track_internal_link() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daim', 'security', false ) ) {
			echo 'Invalid AJAX Request';
			die();}

		// Sanitization.
		$link_type      = isset( $_POST['link_type'] ) && sanitize_key( $_POST['link_type'] ) === 'ail' ? $link_type = 0 : $link_type = 1;
		$source_post_id = isset( $_POST['source_post_id'] ) ? intval( $_POST['source_post_id'], 10 ) : null;
		$target_url     = isset( $_POST['target_url'] ) ? mb_substr( esc_url_raw( wp_unslash( $_POST['target_url'] ) ), 0, 2038 ) : null;

		// Validation.
		if ( is_null( $source_post_id ) || is_null( $target_url ) ) {
			echo 'invalid-data';
			die();
		}

		// Get the current time.
		$date     = current_time( 'mysql' );
		$date_gmt = current_time( 'mysql', 1 );

		/**
		 * Remove all the filter associated with 'the_title' to get with the
		 * function get_the_title() the raw title saved in the posts table.
		 */
		remove_all_filters( 'the_title' );
		$post_title = get_the_title( $source_post_id );

		// Verify if the post with the link exists.
		if ( get_post_status( $source_post_id ) === false ) {
			echo 'The post doesn\'t exists.';
			die(); }

		// Save into the database.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$query_result = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}daim_hits SET 
            source_post_id = %d,
            post_title = %s,
            post_permalink = %s,
            post_edit_link = %s,
            target_url = %s,
            link_type = %s,
            date = %s,
            date_gmt = %s",
				$source_post_id,
				$post_title,
				get_the_permalink( $source_post_id ),
				get_edit_post_link( $source_post_id, 'url' ),
				$target_url,
				$link_type,
				$date,
				$date_gmt
			)
		);

		if ( false === $query_result ) {
			echo 'error';
		} else {
			echo 'success';
		}

		die();
	}

	/**
	 * Ajax handler used to generate the AIL based on the data available in the table of the Wizard menu.
	 *
	 * This method is called when the "Generate Autolinks" button available in the Wizard menu is clicked.
	 */
	public function daim_wizard_generate_ail() {

		// Check the referer.
		if ( ! check_ajax_referer( 'wizard_nonce', 'security', false ) ) {
			echo 'Invalid AJAX Request';
			die();
		}

		// Check the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_wizard_menu_required_capability' ) ) ) {
			echo 'Invalid Capability';
			die();
		}

		// Get the default values of the AIL from the plugin options.
		$default_title                            = get_option( $this->shared->get( 'slug' ) . '_default_title' );
		$default_open_new_tab                     = get_option( $this->shared->get( 'slug' ) . '_default_open_new_tab' );
		$default_use_nofollow                     = get_option( $this->shared->get( 'slug' ) . '_default_use_nofollow' );
		$default_activate_post_types              = get_option( $this->shared->get( 'slug' ) . '_default_activate_post_types' );
		$default_categories                       = get_option( $this->shared->get( 'slug' ) . '_default_categories' );
		$default_tags                             = get_option( $this->shared->get( 'slug' ) . '_default_tags' );
		$default_term_group_id                    = get_option( $this->shared->get( 'slug' ) . '_default_term_group_id' );
		$default_case_insensitive_search          = get_option( $this->shared->get( 'slug' ) . '_default_case_insensitive_search' );
		$default_left_boundary                    = get_option( $this->shared->get( 'slug' ) . '_default_string_before' );
		$default_right_boundary                   = get_option( $this->shared->get( 'slug' ) . '_default_string_after' );
		$default_keyword_before                   = get_option( $this->shared->get( 'slug' ) . '_default_keyword_before' );
		$default_keyword_after                    = get_option( $this->shared->get( 'slug' ) . '_default_keyword_after' );
		$default_max_number_autolinks_per_keyword = get_option( $this->shared->get( 'slug' ) . '_default_max_number_autolinks_per_keyword' );
		$default_priority                         = get_option( $this->shared->get( 'slug' ) . '_default_priority' );

		// get the name.
		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null;

		// get the category_id.
		$category_id = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'], 10 ) : null;

		// get the data of the table.
		$table_data_a = isset( $_POST['table_data'] ) ? $this->shared->sanitize_table_data( wp_unslash( $_POST['table_data'] ) ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Validation -------------------------------------------------------------------------------------------------.
		if ( mb_strlen( $name ) === 0 || mb_strlen( $name ) > 100 ) {
			echo 'invalid name';
			die();
		}

		global $wpdb;

		// add the new data.
		$values        = array();
		$place_holders = array();
		$query         = "INSERT INTO {$wpdb->prefix}daim_autolinks (
            name,
            category_id,
            keyword,
            url,
            title,
            string_before,
            string_after,
            keyword_before,
            keyword_after,
            activate_post_types,
            categories,
            tags,
            term_group_id,
            max_number_autolinks,
            case_insensitive_search,
            open_new_tab,
            use_nofollow,
            priority
        ) VALUES ";

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		foreach ( $table_data_a as $row_index => $row_data ) {

			$keyword = sanitize_text_field( $row_data[0] );
			$url     = substr( esc_url_raw( 'https://example.com' . $row_data[1] ), 19 );
			$title   = sanitize_text_field( $row_data[2] );

			// validation on "Keyword".
			if ( strlen( trim( $keyword ) ) === 0 || strlen( $keyword ) > 255 ) {
				continue;
			}

			/*
			 * Do not allow only numbers as a keyword. Only numbers in a keyword would cause the index of the protected block to
			 * be replaced. For example the keyword "1" would cause the "1" present in the index of the following protected
			 * blocks to be replaced with an autolink:
			 *
			 * - [pb]1[/pb]
			 * - [pb]31[/pb]
			 * - [pb]812[/pb]
			 */
			if ( preg_match( '/^\d+$/', $keyword ) === 1 ) {
				continue;
			}

			/**
			 * Do not allow to create specific keywords that would be able to replace the start delimiter of the
			 * protected block [pb], part of the start delimiter, the end delimited [/pb] or part of the end delimiter.
			 */
			if ( preg_match( '/^\[$|^\[p$|^\[pb$|^\[pb]$|^\[\/$|^\[\/p$|^\[\/pb$|^\[\/pb\]$|^\]$|^b\]$|^pb\]$|^\/pb\]$|^p$|^pb$|^pb\]$|^\/$|^\/p$|^\/pb$|^\/pb]$|^b$|^b\$/i', $keyword ) === 1 ) {
				continue;
			}

			/**
			 * Do not allow to create specific keywords that would be able to replace the start delimiter of the
			 * autolink [ail], part of the start delimiter, the end delimited [/ail] or part of the end delimiter.
			 */
			if ( ! preg_match( '/^\[$|^\[a$|^\[ai$|^\[ail$|^\[ail\]$|^a$|^ai$|^ail$|^ail\]$|^i$|^il$|^il\]$|^l$|^l\]$|^\]$|^\[$|^\[\/$|^\[\/a$|^\[\/ai$|^\[\/ail$|^\[\/ail\]$|^\/$|^\/]$|^\/a$|^\/ai$|^\/ail$|^\/ail\]$/i', $keyword ) === 1 ) {
				continue;
			}

			// Validation on "Target".
			if ( strlen( trim( $url ) ) === 0 ||
				strlen( $keyword ) > 2083 ||
				! preg_match( '/^(?!(http|https|fpt|file):\/\/)[-A-Za-z0-9+&@#\/%?=~_|$!:,.;]+$/', $url ) ) {
				continue;
			}

			// Validation on "Title".
			if ( strlen( $title ) > 1024 ) {
				continue;
			}

			// If the title is not defined use the default title.
			if ( strlen( trim( $title ) ) === 0 ) {
				$title = $default_title;
			}

			array_push(
				$values,
				$name,
				$category_id,
				$keyword,
				$url,
				$title,
				$default_left_boundary,
				$default_right_boundary,
				$default_keyword_before,
				$default_keyword_after,
				maybe_serialize( $default_activate_post_types ),
				maybe_serialize( $default_categories ),
				maybe_serialize( $default_tags ),
				$default_term_group_id,
				$default_max_number_autolinks_per_keyword,
				$default_case_insensitive_search,
				$default_open_new_tab,
				$default_use_nofollow,
				$default_priority
			);

			$place_holders[] = "(
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d'
            )";

		}

		if ( count( $values ) > 0 ) {

			// Add the rows.
			$query .= implode( ', ', $place_holders );
			//phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- The query is prepared here.
			$result = $wpdb->query(
				$wpdb->prepare( $query, $values )
			);
			//phpcs:enable

			if ( false === $result ) {
				$output = 'error';
			} else {
				$output = $result;
			}
		} else {

			// Do not add the rows and set $output to 0 as the number of rows added.
			$output = 0;

		}

		if ( 'error' === $output ) {
			$this->shared->save_dismissible_notice(
				__( 'Now rows have been added.', 'interlinks-manager'),
				'error'
			);
		} else {
			$this->shared->save_dismissible_notice(
				$output . ' ' . __( 'rows have been added.', 'interlinks-manager'),
				'updated'
			);
		}

		// Send output.
		echo esc_html( $output );
		die();
	}

	/**
	 * Get the list of taxonomies associated with the provided post type.
	 */
	public function daim_get_taxonomies() {

		// check the referer.
		if ( ! check_ajax_referer( 'daim', 'security', false ) ) {
			esc_html_e( 'Invalid AJAX Request', 'interlinks-manager');
			die();
		}

		// check the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_term_groups_menu_required_capability' ) ) ) {
			esc_html_e( 'Invalid Capability', 'interlinks-manager');
			die();
		}

		// get the data.
		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : null;

		$taxonomies = get_object_taxonomies( $post_type );

		$taxonomy_obj_a = array();
		if ( is_array( $taxonomies ) && count( $taxonomies ) > 0 ) {
			foreach ( $taxonomies as $key => $taxonomy ) {
				$taxonomy_obj_a[] = get_taxonomy( $taxonomy );
			}
		}

		echo wp_json_encode( $taxonomy_obj_a );
		die();
	}

	/**
	 * Get the list of terms associated with the provided taxonomy.
	 */
	public function daim_get_terms() {

		// check the referer.
		if ( ! check_ajax_referer( 'daim', 'security', false ) ) {
			esc_html_e( 'Invalid AJAX Request', 'deextamp' );
			die();
		}

		// check the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_term_groups_menu_required_capability' ) ) ) {
			esc_html_e( 'Invalid Capability', 'interlinks-manager');
			die();
		}

		// get the data.
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( $_POST['taxonomy'] ) : null;

		$terms = get_terms(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
				'taxonomy'   => $taxonomy,
			)
		);

		if ( is_object( $terms ) && get_class( $terms ) === 'WP_Error' ) {
			return '0';
		} else {
			echo wp_json_encode( $terms );
		}

		die();
	}
}
