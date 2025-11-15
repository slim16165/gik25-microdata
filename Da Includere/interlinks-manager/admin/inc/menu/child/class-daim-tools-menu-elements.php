<?php
/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 *
 * @package interlinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 */
class Daim_Tools_Menu_Elements extends Daim_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param $shared
	 * @param $page_query_param
	 * @param $config
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'tool';
		$this->slug_plural        = 'tools';
		$this->label_singular     = 'Tool';
		$this->label_plural       = 'Tools';
		$this->primary_key        = 'category_id';
		$this->db_table           = 'category';
		$this->list_table_columns = array(
			array(
				'db_field' => 'name',
				'label'    => 'Name',
			),
			array(
				'db_field' => 'description',
				'label'    => 'Description',
			),
		);
		$this->searchable_fields  = array(
			'name',
			'description',
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
	public function process_form() {

		// process the xml file upload. (import) ----------------------------------------------------------------------.
		if ( isset( $_FILES['file_to_upload'] ) &&
			isset( $_FILES['file_to_upload']['name'] )
		) {

			global $wpdb;

			// Nonce verification.
			check_admin_referer( 'daim_tools_import', 'daim_tools_import_nonce' );

			//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- The sanitization is performed with sanitize_uploaded_file().
			$file_data = $this->shared->sanitize_uploaded_file(
				array(
					'name'     => $_FILES['file_to_upload']['name'],
					'type'     => $_FILES['file_to_upload']['type'],
					'tmp_name' => $_FILES['file_to_upload']['tmp_name'],
					'error'    => $_FILES['file_to_upload']['error'],
					'size'     => $_FILES['file_to_upload']['size'],
				)
			);
			//phpcs:enable

			if ( 1 !== preg_match( '/^.+\.xml$/', $file_data['name'] ) ) {
				return;
			}

			if ( file_exists( $file_data['tmp_name'] ) ) {

				$counter_autolink         = 0;
				$counter_category         = 0;
				$counter_term_group       = 0;
				$category_id_hash_table   = array();
				$term_group_id_hash_table = array();

				// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
				$this->shared->set_met_and_ml();

				// Read xml file.
				$xml = simplexml_load_file( $file_data['tmp_name'] );

				// Import Categories ----------------------------------------------------------------------------------.
				$category_a = $xml->category;

				$num = count( $category_a );

				for ( $i = 0; $i < $num; $i++ ) {

					// convert object to array.
					$single_category_a = get_object_vars( $category_a[ $i ] );

					// replace objects with empty strings to prevent notices on the next insert() method.
					$single_category_a = $this->shared->replace_objects_with_empty_strings( $single_category_a );

					/**
					 * Save the category_id key for later use and remove the category_id key from the
					 * main array.
					 */
					$current_category_id = $single_category_a['category_id'];
					unset( $single_category_a['category_id'] );

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->insert(
						$wpdb->prefix . 'daim_category',
						$single_category_a
					);
					$inserted_category_id = $wpdb->insert_id;
					$counter_category    += $wpdb->rows_affected;

					// Add the old and new category_id in the hash table.
					$category_id_hash_table[ $current_category_id ] = $inserted_category_id;

				}

				// Import Term Groups ---------------------------------------------------------------------------------.
				$term_group_a = $xml->term_group;

				$num = count( $term_group_a );

				for ( $i = 0; $i < $num; $i++ ) {

					// convert object to array.
					$single_term_group_a = get_object_vars( $term_group_a[ $i ] );

					// replace objects with empty strings to prevent notices on the next insert() method.
					$single_term_group_a = $this->shared->replace_objects_with_empty_strings( $single_term_group_a );

					/**
					 * Save the term_group_id key for later use and remove the term_group_id key from the
					 * main array.
					 */
					$current_term_group_id = $single_term_group_a['term_group_id'];
					unset( $single_term_group_a['term_group_id'] );

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->insert(
						$wpdb->prefix . 'daim_term_group',
						$single_term_group_a
					);
					$inserted_term_group_id = $wpdb->insert_id;
					$counter_term_group    += $wpdb->rows_affected;

					// Add the old and new term_group_id in the has table.
					$term_group_id_hash_table[ $current_term_group_id ] = $inserted_term_group_id;

				}

				// Import Autolinks -----------------------------------------------------------------------------------.
				$autolink_a = $xml->autolinks;

				$num = count( $autolink_a );

				for ( $i = 0; $i < $num; $i++ ) {

					// convert object to array.
					$single_autolink_a = get_object_vars( $autolink_a[ $i ] );

					// replace objects with empty strings to prevent notices on the next insert() method.
					$single_autolink_a = $this->shared->replace_objects_with_empty_strings( $single_autolink_a );

					// remove the id key.
					unset( $single_autolink_a['id'] );

					// replace the category_id value with zero or the one available in $category_id_hash_table.
					if ( intval( $single_autolink_a['category_id'], 10 ) === 0 ) {
						$single_autolink_a['category_id'] = 0;
					} else {
						$single_autolink_a['category_id'] = $category_id_hash_table[ $single_autolink_a['category_id'] ];
					}

					// replace the term_group_id value with zero or the one available in $term_group_id_hash_table.
					if ( intval( $single_autolink_a['term_group_id'], 10 ) === 0 ) {
						$single_autolink_a['term_group_id'] = 0;
					} else {
						$single_autolink_a['term_group_id'] = $term_group_id_hash_table[ $single_autolink_a['term_group_id'] ];
					}

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->insert(
						$wpdb->prefix . 'daim_autolinks',
						$single_autolink_a
					);
					$inserted_autolink_id = $wpdb->insert_id;
					$counter_autolink    += $wpdb->rows_affected;

				}

				$this->shared->save_dismissible_notice(
					__( 'The following elements have been added:', 'interlinks-manager') . ' ' .
					$counter_autolink . ' ' . __( 'autolinks', 'interlinks-manager') . ', ' .
					$counter_category . ' ' . __( 'categories', 'interlinks-manager') . ' and ' .
					$counter_term_group . ' ' . __( 'term groups.', 'interlinks-manager'),
					'updated'
				);

			}
		}

		// process the export button click. (export) ------------------------------------------------------------------.

		/**
		 * Intercept requests that come from the "Export" button and generate the downloadable XML file.
		 */
		if ( isset( $_POST['daim_export'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daim_tools_export', 'daim_tools_export' );

			// generate the header of the XML file.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/xml; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=interlinks-manager-' . time() . '.xml' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// generate initial part of the XML file.
			echo '<?xml version="1.0" encoding="UTF-8" ?>';
			echo '<root>';

			// Echo the XML of the various db tables.
			$this->shared->convert_db_table_to_xml( 'autolinks', 'id' );
			$this->shared->convert_db_table_to_xml( 'category', 'category_id' );
			$this->shared->convert_db_table_to_xml( 'term_group', 'term_group_id' );

			// generate the final part of the XML file.
			echo '</root>';

			die();

		}


	}

	/**
	 * Display the form.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="daim-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			// Display the license activation notice.
			$this->shared->display_license_activation_notice();

			?>

			<div class="daim-tools-menu">

				<div class="daim-main-form">

				<div class="daim-main-form__wrapper-half">

					<div class="daim-main-form__daext-form-section">

						<div class="daim-main-form__section-header">
							<div class="daim-main-form__section-header-title">
								<?php $this->shared->echo_icon_svg( 'log-in-04' ); ?>
								<div class="daim-main-form__section-header-title-text"><?php esc_html_e( 'Import', 'interlinks-manager' ); ?></div>
							</div>
						</div>

						<div class="daim-main-form__daext-form-section-body">

							<!-- Import form -->

							<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form"
									action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $this->slug_plural ); ?>"
							>
								<?php wp_nonce_field( 'daim_tools_import', 'daim_tools_import_nonce' ); ?>
								<p>
								<div class="daim-input-wrapper">
									<label for="upload" class="custom-file-upload"><?php esc_html_e('Choose file', 'interlinks-manager'); ?></label>
									<div class="custom-file-upload-text" id="upload-text"><?php esc_html_e('No file chosen', 'interlinks-manager'); ?></div>
									<input type="file" id="upload" name="file_to_upload"
									       class="custom-file-upload-input">
								</div>

								</p>
								<p class="submit"><input type="submit" name="submit" id="submit" class="daim-btn daim-btn-primary"
														value="<?php esc_attr_e( 'Upload file and import', 'interlinks-manager' ); ?>"></p>
							</form>
							<p>
								<strong>
									<?php
									esc_html_e(
										'IMPORTANT: This functionality should only be used to import the XML files generated with the "Export" tool.',
										'interlinks-manager'
									);
									?>
								</strong></p>

						</div>

					</div>

					<div class="daim-main-form__daext-form-section">

						<div class="daim-main-form__section-header">
							<div class="daim-main-form__section-header-title">
								<?php $this->shared->echo_icon_svg( 'log-out-04' ); ?>
								<div class="daim-main-form__section-header-title-text"><?php esc_html_e( 'Export', 'interlinks-manager' ); ?></div>
							</div>
						</div>

						<div class="daim-main-form__daext-form-section-body">

							<!-- Export form -->

							<p>
								<?php
								esc_html_e(
									'Click the Export button to generate an XML file that includes AIL, categories and term groups.',
									'interlinks-manager'
								);
								?>
							</p>

							<!-- the data sent through this form are handled by the export_xml_controller() method called with the WordPress init action -->
							<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $this->slug_plural ); ?>">

								<div class="daext-widget-submit">
									<?php wp_nonce_field( 'daim_tools_export', 'daim_tools_export' ); ?>
									<input name="daim_export" class="daim-btn daim-btn-primary" type="submit"
											value="<?php esc_attr_e('Export', 'interlinks-manager'); ?>"
										<?php
										if ( ! $this->shared->exportable_data_exists() ) {
											echo 'disabled="disabled"';
										}
										?>
									>
								</div>

							</form>

						</div>

					</div>

				</div>

			</div>

			</div>

		</div>

		<?php

	}
}
