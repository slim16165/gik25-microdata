<?php
/**
 * Class used to implement the back-end functionalities of the "Autolinks" menu.
 *
 * @package interlinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Autolinks" menu.
 */
class Daim_Autolink_Menu_Elements extends Daim_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param $shared
	 * @param $page_query_param
	 * @param $config
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'autolink';
		$this->slug_plural        = 'autolinks';
		$this->label_singular     = 'Autolink';
		$this->label_plural       = 'Autolinks';
		$this->primary_key        = 'id';
		$this->db_table           = 'autolinks';
		$this->list_table_columns = array(
			array(
				'db_field' => 'name',
				'label'    => 'Name',
			),
			array(
				'db_field' => 'category_id',
				'label'    => 'Category',
				'prepare_displayed_value' => array($shared, 'get_category_name')
			),
			array(
				'db_field' => 'keyword',
				'label'    => 'Keyword',
			),
			array(
				'db_field' => 'url',
				'label'    => 'Target',
			),
		);
		$this->searchable_fields  = array(
			'name',
			'keyword',
		);

		$this->default_values     = array(
			'name'                    => '',
			'category_id'             => intval(get_option($this->shared->get('slug') . "_default_category_id"), 10),
			'keyword'                 => '',
			'url'                     => '',
			'title'                   => get_option($this->shared->get('slug') . '_default_title'),
			'string_before'           => get_option($this->shared->get('slug') . "_default_string_before"),
			'string_after'            => get_option($this->shared->get('slug') . "_default_string_after"),
			'keyword_before'          => get_option($this->shared->get('slug') . '_default_keyword_before'),
			'keyword_after'           => get_option($this->shared->get('slug') . '_default_keyword_after'),
			'activate_post_types'     => get_option($this->shared->get('slug') . '_default_activate_post_types'),
			'categories'              => get_option($this->shared->get('slug') . '_default_categories'),
			'tags'                    => get_option($this->shared->get('slug') . '_default_tags'),
			'term_group_id'           => intval(get_option($this->shared->get('slug') . "_default_term_group_id"), 10),
			'max_number_autolinks'    => intval(get_option($this->shared->get('slug') . '_default_max_number_autolinks_per_keyword', 10)),
			'case_insensitive_search' => intval(get_option($this->shared->get('slug') . '_default_case_insensitive_search'), 10),
			'open_new_tab'            => intval(get_option($this->shared->get('slug') . '_default_open_new_tab'), 10),
			'use_nofollow'            => intval(get_option($this->shared->get('slug') . '_default_use_nofollow'), 10),
			'priority'                => intval(get_option($this->shared->get('slug') . '_default_priority'), 10),
		);
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 * 1. Sanitization
	 * 2. Validation
	 * 3. Database update
	 *
	 * @return void
	 */
	public function process_form( $db_table ) {

		if ( isset( $_POST['update_id'] ) ||
		     isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daim_create_update_' . $this->menu_slug, 'daim_create_update_' . $this->menu_slug . '_nonce' );

		}

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		// Sanitization -------------------------------------------------------------------------------------------------------.

		$data = array();

		// Actions.
		$data['update_id']      = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;
		$data['form_submitted'] = isset( $_POST['form_submitted'] ) ? intval( $_POST['form_submitted'], 10 ) : null;

		// Sanitization.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// Main Form data.
			$data['name']         = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null;
			$data['category_id']  = isset( $_POST['category_id'] ) ? intval( wp_unslash( $_POST['category_id'] ), 10 ) : null;
			$data['keyword']      = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : null;
			$data['url']          = isset( $_POST['url'] ) ? substr( esc_url_raw( 'https://example.com' . wp_unslash( $_POST['url'] ) ), 19 ) : null;
			$data['title']        = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : null;
			$data['open_new_tab'] = isset( $_POST['open_new_tab'] ) ? 1 : 0;
			$data['use_nofollow'] = isset( $_POST['use_nofollow'] ) ? 1 : 0;

			if ( isset( $_POST['activate_post_types'] ) && is_array( $_POST['activate_post_types'] ) ) {

				// Sanitize all the post types in the array.
				$data['activate_post_types'] = array_map( 'sanitize_key', $_POST['activate_post_types'] );

			} else {
				$data['activate_post_types'] = '';
			}

			if ( isset( $_POST['categories'] ) && is_array( $_POST['categories'] ) ) {

				// Sanitize (convert to integer base 10) all the category id in the array.
				$data['categories'] = array_map(
					function ( $value ) {
						return intval( $value, 10 );
					},
					$_POST['categories']
				);

			} else {
				$data['categories'] = '';
			}

			if ( isset( $_POST['tags'] ) && is_array( $_POST['tags'] ) ) {

				// Sanitize (convert to integer base 10) all the tag id in the array.
				$data['tags'] = array_map(
					function ( $value ) {
						return intval( $value, 10 );
					},
					$_POST['tags']
				);

			} else {
				$data['tags'] = '';
			}

			$data['term_group_id']           = isset( $_POST['term_group_id'] ) ? intval( $_POST['term_group_id'], 10 ) : null;
			$data['case_insensitive_search'] = isset( $_POST['case_insensitive_search'] ) ? 1 : 0;
			$data['string_before']           = isset( $_POST['string_before'] ) ? intval( $_POST['string_before'], 10 ) : null;
			$data['string_after']            = isset( $_POST['string_after'] ) ? intval( $_POST['string_after'], 10 ) : null;
			$data['keyword_before']          = isset( $_POST['keyword_before'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword_before'] ) ) : null;
			$data['keyword_after']           = isset( $_POST['keyword_after'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword_after'] ) ) : null;
			$data['max_number_autolinks']    = isset( $_POST['max_number_autolinks'] ) ? intval( $_POST['max_number_autolinks'], 10 ) : null;
			$data['priority']                = isset( $_POST['priority'] ) ? intval( $_POST['priority'], 10 ) : null;

		}

		// Validation.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			$invalid_data_message = '';

			// Validation on "name".
			if ( mb_strlen( trim( $data['name'] ) ) === 0 || mb_strlen( trim( $data['name'] ) ) > 100 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Name" field.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Keyword".
			if ( 0 === strlen( trim( $data['keyword'] ) ) || strlen( $data['keyword'] ) > 255 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Keyword" field.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}

			/**
			 * Do not allow only numbers as a keyword. Only numbers in a keyword would cause the index of the protected block to
			 * be replaced. For example the keyword "1" would cause the "1" present in the index of the following protected
			 * blocks to be replaced with an autolink:
			 *
			 * - [pb]1[/pb]
			 * - [pb]31[/pb]
			 * - [pb]812[/pb]
			 */
			if ( preg_match( '/^\d+$/', $data['keyword'] ) === 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'The specified keyword is not allowed.', 'interlinks-manager'),
					'error'
				);
				$invalid_data                  = true;
				$specified_keyword_not_allowed = true;
			}

			/**
			 * Do not allow to create specific keywords that would be able to replace the start delimiter of the
			 * protected block [pb], part of the start delimiter, the end delimited [/pb] or part of the end delimiter.
			 */
			if ( preg_match( '/^\[$|^\[p$|^\[pb$|^\[pb]$|^\[\/$|^\[\/p$|^\[\/pb$|^\[\/pb\]$|^\]$|^b\]$|^pb\]$|^\/pb\]$|^p$|^pb$|^pb\]$|^\/$|^\/p$|^\/pb$|^\/pb]$|^b$|^b\$/i', $data['keyword'] ) === 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'The specified keyword is not allowed.', 'interlinks-manager'),
					'error'
				);
				$invalid_data                  = true;
				$specified_keyword_not_allowed = true;
			}

			/**
			 * Do not allow to create specific keywords that would be able to replace the start delimiter of the
			 * autolink [ail], part of the start delimiter, the end delimited [/ail] or part of the end delimiter.
			 */
			if ( ! isset( $specified_keyword_not_allowed ) && preg_match( '/^\[$|^\[a$|^\[ai$|^\[ail$|^\[ail\]$|^a$|^ai$|^ail$|^ail\]$|^i$|^il$|^il\]$|^l$|^l\]$|^\]$|^\[$|^\[\/$|^\[\/a$|^\[\/ai$|^\[\/ail$|^\[\/ail\]$|^\/$|^\/]$|^\/a$|^\/ai$|^\/ail$|^\/ail\]$/i', $data['keyword'] ) === 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'The specified keyword is not allowed.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Title".
			if ( strlen( $data['title'] ) > 1024 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Title" field.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Post Types".
			if ( ! is_array( $data['activate_post_types'] ) || count( $data['activate_post_types'] ) === 0 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter at least one post type in the "Post Types" field.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}

			// validation on "keyword_before".
			if ( mb_strlen( trim( $data['keyword_before'] ) ) > 255 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Keyword Before" field.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "keyword_after".
			if ( mb_strlen( trim( $data['keyword_after'] ) ) > 255 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Keyword After" field.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Max Number AIL".
			if ( ! preg_match( $this->shared->regex_number_ten_digits, $data['max_number_autolinks'] ) || intval( $data['max_number_autolinks'], 10 ) < 1 || intval( $data['max_number_autolinks'], 10 ) > 1000000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a number from 1 to 1000000 in the "Limit" field.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}

			// validation on "Priority".
			if ( ! preg_match( $this->shared->regex_number_ten_digits, $data['priority'] ) || intval( $data['priority'], 10 ) > 100 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a number from 0 to 100 in the "Priority" field.', 'interlinks-manager'),
					'error'
				);
				$invalid_data = true;
			}
		}

		// update ---------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) && ! isset( $invalid_data ) ) {

			// Update the database.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daim_autolinks SET 
                name = %s,
                category_id = %d,
                keyword = %s,
                url = %s,
                title = %s,
                string_before = %d,
                string_after = %d,
                keyword_before = %s,
                keyword_after = %s,
                activate_post_types = %s,
                categories = %s,
                tags = %s,
                term_group_id = %d,
                max_number_autolinks = %d,
                case_insensitive_search = %d,
                open_new_tab = %d,
                use_nofollow = %d,
                priority = %d
                WHERE id = %d",
					$data['name'],
					$data['category_id'],
					$data['keyword'],
					$data['url'],
					$data['title'],
					$data['string_before'],
					$data['string_after'],
					$data['keyword_before'],
					$data['keyword_after'],
					maybe_serialize( $data['activate_post_types'] ),
					maybe_serialize( $data['categories'] ),
					maybe_serialize( $data['tags'] ),
					$data['term_group_id'],
					$data['max_number_autolinks'],
					$data['case_insensitive_search'],
					$data['open_new_tab'],
					$data['use_nofollow'],
					$data['priority'],
					$data['update_id']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The automatic internal link has been successfully updated.', 'interlinks-manager'),
					'updated'
				);
			}
		} else {

			// Add record to database ------------------------------------------------------------------.
			if ( ! is_null( $data['form_submitted'] ) && ! isset( $invalid_data ) ) {

				// Insert into the database.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}daim_autolinks SET 
                    name = %s,
                    category_id = %d,
                    keyword = %s,
                    url = %s,
                    title = %s,
                    string_before = %d,
                    string_after = %d,
                    keyword_before = %s,
                    keyword_after = %s,
                    activate_post_types = %s,
                    categories = %s,
                    tags = %s,
                    term_group_id = %d,
                    max_number_autolinks = %d,
                    case_insensitive_search = %d,
                    open_new_tab = %d,
                    use_nofollow = %d,
                    priority = %d",
						$data['name'],
						$data['category_id'],
						$data['keyword'],
						$data['url'],
						$data['title'],
						$data['string_before'],
						$data['string_after'],
						$data['keyword_before'],
						$data['keyword_after'],
						maybe_serialize( $data['activate_post_types'] ),
						maybe_serialize( $data['categories'] ),
						maybe_serialize( $data['tags'] ),
						$data['term_group_id'],
						$data['max_number_autolinks'],
						$data['case_insensitive_search'],
						$data['open_new_tab'],
						$data['use_nofollow'],
						$data['priority']
					)
				);

				if ( false !== $query_result ) {
					$this->shared->save_dismissible_notice(
						__( 'The automatic internal link has been successfully added.', 'interlinks-manager'),
						'updated'
					);
				}
			}
		}
	}

	/**
	 * Defines the form fields present in the add/edit form and call the method to print them.
	 *
	 * @param object $item_obj The item object.
	 *
	 * @return void
	 */
	public function print_form_fields( $item_obj = null ) {

		// Get the categories.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$category_a = $wpdb->get_results(
			"SELECT category_id, name FROM {$wpdb->prefix}daim_category ORDER BY category_id DESC"
			, ARRAY_A );

		$category_a_option_value      = array();
		$category_a_option_value['0'] = __( 'None', 'interlinks-manager' );
		foreach ( $category_a as $key => $value ) {
			$category_a_option_value[ $value['category_id'] ] = $value['name'];
		}

		// Get the available post types.
		$available_post_types_a = get_post_types(
			array(
				'public'  => true,
				'show_ui' => true,
			)
		);

		// Remove the "attachment" post type.
		$available_post_types_a = array_diff( $available_post_types_a, array( 'attachment' ) );

		// Get the term groups.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_group_a = $wpdb->get_results(
			"SELECT term_group_id, name FROM {$wpdb->prefix}daim_term_group ORDER BY term_group_id DESC"
			, ARRAY_A );

		$term_group_a_option_value      = array();
		$term_group_a_option_value['0'] = __( 'None', 'interlinks-manager' );
		foreach ( $term_group_a as $key => $value ) {
			$term_group_a_option_value[ $value['term_group_id'] ] = $value['name'];
		}

		// Get the categories.
		$categories = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
			)
		);

		$categories_option = array();
		foreach ( $categories as $key => $category ) {
			$categories_option[ $category->term_id ] = $category->name;
		}

		// Get the tags.
		$tags = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
				'taxonomy'   => 'post_tag',
			)
		);

		$tags_option = array();
		foreach ( $tags as $key => $tag ) {
			$tags_option[ $tag->term_id ] = $tag->name;
		}

		// Boundary options.
		$boundary_options = array(
			'1' => __( 'Generic', 'interlinks-manager' ),
			'2' => __( 'White Space', 'interlinks-manager' ),
			'3' => __( 'Comma', 'interlinks-manager' ),
			'4' => __( 'Point', 'interlinks-manager' ),
			'5' => __( 'None', 'interlinks-manager' ),
		);

		// Add the form data in the $sections array.
		$sections = array(
			array(
				'label'          => 'Main',
				'section_id'     => 'main',
				'icon_id'        => 'dots-grid',
				'display_header' => false,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'name',
						'label'       => __('Name', 'interlinks-manager'),
						'description' => __('The name of the automatic internal link.', 'interlinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['name'] : null,
						'maxlength'   => 100,
						'required'    => true,
					),
					array(
						'type'        => 'select',
						'name'        => 'category_id',
						'label'       => __('Category', 'interlinks-manager'),
						'description' => __('The category of the automatic internal link.', 'interlinks-manager'),
						'options'     => $category_a_option_value,
						'value'       => isset( $item_obj ) ? $item_obj['category_id'] : null,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'keyword',
						'label'       => __('Keyword', 'interlinks-manager'),
						'description' => __('The keyword that will be converted to a link.', 'interlinks-manager'),
						'placeholder' => __('The Keyword', 'interlinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['keyword'] : null,
						'maxlength'   => 255,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'url',
						'label'       => __('Target (URL Path and/or File)', 'interlinks-manager'),
						'description' => __('The target of the link automatically generated on the keyword. Please note that the URL scheme and domain are implied.', 'interlinks-manager'),
						'placeholder' => __('/hello-world/', 'interlinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['url'] : null,
						'maxlength'   => 2083,
						'required'    => true,
					),
				),
			),
			array(
				'label'          => 'HTML',
				'section_id'     => 'html-options',
				'icon_id'        => 'code-browser',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'title',
						'label'       => __('Title', 'interlinks-manager'),
						'description' => __('The title attribute of the link automatically generated on the keyword.', 'interlinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['title'] : null,
						'maxlength'   => 255,
						'required'    => false,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'open_new_tab',
						'label'       => __('Open in New Tab', 'interlinks-manager'),
						'description' => __('If you select "Yes" the link generated on the defined keyword opens the linked document in a new tab.', 'interlinks-manager'),
						'options'     => array(
							'0' => 'No',
							'1' => 'Yes',
						),
						'value'       => isset( $item_obj ) ? $item_obj['open_new_tab'] : null,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'use_nofollow',
						'label'       => __('Use Nofollow', 'interlinks-manager'),
						'description' => __('If you select "Yes" the link generated on the defined keyword will include the rel="nofollow" attribute.', 'interlinks-manager'),
						'options'     => array(
							'0' => 'No',
							'1' => 'Yes',
						),
						'value'       => isset( $item_obj ) ? $item_obj['use_nofollow'] : null,
					),
				),
			),
			array(
				'label'          => 'Affected Posts',
				'section_id'     => 'affected-posts',
				'icon_id'        => 'layout-alt-03',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'select_multiple',
						'name'        => 'activate_post_types',
						'label'       => __('Post Types', 'interlinks-manager'),
						'description' => __('Set in which post types the defined keywords will be automatically converted to a link.', 'interlinks-manager'),
						'options'     => $available_post_types_a,
						'value'       => isset( $item_obj ) ? $item_obj['activate_post_types'] : null,
					),
					array(
						'type'        => 'select_multiple',
						'name'        => 'categories',
						'label'       => __('Categories', 'interlinks-manager'),
						'description' => __('Set in which categories the defined keyword will be automatically converted to a link. Leave this field empty to convert the keyword in any category.', 'interlinks-manager'),
						'options'     => $categories_option,
						'value'       => isset( $item_obj ) ? $item_obj['categories'] : null,
					),
					array(
						'type'        => 'select_multiple',
						'name'        => 'tags',
						'label'       => __('Tags', 'interlinks-manager'),
						'description' => __('Set in which tags the defined keyword will be automatically converted to a link. Leave this field empty to convert the keyword in any tag.', 'interlinks-manager'),
						'options'     => $tags_option,
						'value'       => isset( $item_obj ) ? $item_obj['tags'] : null,
					),
					array(
						'type'        => 'select',
						'name'        => 'term_group_id',
						'label'       => __('Term Group', 'interlinks-manager'),
						'description' => __('Set in which term groups the defined keyword will be automatically converted to a link. Leave this field empty to convert the keyword in any term group.', 'interlinks-manager'),
						'options'     => $term_group_a_option_value,
						'value'       => isset( $item_obj ) ? $item_obj['term_group_id'] : null,
					),
				),
			),
			array(
				'label'          => 'Advanced Match',
				'section_id'     => 'advanced-match',
				'icon_id'        => 'settings-01',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'toggle',
						'name'        => 'case_insensitive_search',
						'label'       => __('Case Insensitive Search', 'interlinks-manager'),
						'description' => __('If you select "Yes" your keyword will match both lowercase and uppercase variations.', 'interlinks-manager'),
						'options'     => array(
							'0' => 'No',
							'1' => 'Yes',
						),
						'value'       => isset( $item_obj ) ? $item_obj['case_insensitive_search'] : null,
					),
					array(
						'type'        => 'select',
						'name'        => 'string_before',
						'label'       => __('Left Boundary', 'interlinks-manager'),
						'description' => __('Target keywords preceded by a generic boundary or by a specific character.', 'interlinks-manager'),
						'options'     => $boundary_options,
						'value'       => isset( $item_obj ) ? $item_obj['string_before'] : null,
					),
					array(
						'type'        => 'select',
						'name'        => 'string_after',
						'label'       => __('Right Boundary', 'interlinks-manager'),
						'description' => __('Target keywords followed by a generic boundary or by a specific character.', 'interlinks-manager'),
						'options'     => $boundary_options,
						'value'       => isset( $item_obj ) ? $item_obj['string_after'] : null,
					),
					array(
						'type'        => 'text',
						'name'        => 'keyword_before',
						'label'       => __('Keyword Before', 'interlinks-manager'),
						'description' => __('Use this option to match occurrences preceded by a specific string.', 'interlinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['keyword_before'] : null,
						'maxlength'   => 255,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'keyword_after',
						'label'       => __('Keyword After', 'interlinks-manager'),
						'description' => __('Use this option to match occurrences followed by a specific string.', 'interlinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['keyword_after'] : null,
						'maxlength'   => 255,
						'required'    => false,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'max_number_autolinks',
						'label'       => __('Limit', 'interlinks-manager'),
						'description' => __('Set the maximum number of matches of the defined keyword that will be automatically converted to a link.', 'interlinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['max_number_autolinks'] : null,
						'min'         => 1,
						'max'         => 1000,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'priority',
						'label'       => __('Priority', 'interlinks-manager'),
						'description' => __('The priority value determines the order used to apply the automatic internal links on the post.', 'interlinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['priority'] : null,
						'min'         => 0,
						'max'         => 100,
					),
				),
			),
		);

			$this->print_form_fields_from_array( $sections );
	}

	/**
	 * Check if the item is deletable. If not, return the message to be displayed.
	 *
	 * @param int $item_id The item id.
	 *
	 * @return array
	 */
	public function item_is_deletable( $item_id ) {

		return array(
			'is_deletable'               => true,
			'dismissible_notice_message' => null,
		);
	}
}
