<?php
/**
 * Class used to implement the back-end functionalities of the "Wizard" menu.
 *
 * @package interlinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Wizard" menu.
 */
class Daim_Wizard_Menu_Elements extends Daim_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param $shared
	 * @param $page_query_param
	 * @param $config
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'wizard';
		$this->slug_plural        = 'wizard';
		$this->label_singular     = 'Wizard';
		$this->label_plural       = 'Wizard';
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

			<div class="daim-main-form">

				<div class="daim-main-form__daext-form-section">

					<div class="daim-main-form__daext-form-section-body">

						<?php

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

						$default_category_id = intval(get_option($this->shared->get('slug') . "_default_category_id"), 10);
						if(0 === $default_category_id){
							$default_category_id = null;
						}

						// Category.

						$this->input_field(
							'name',
							__('Name', 'interlinks-manager'),
							__('The name of the AIL.', 'interlinks-manager'),
							'',
							null,
							'main'
						);

						// Category.
						$this->select_field(
							'category_id',
							__('Category', 'interlinks-manager'),
							__('The category of the AIL.', 'interlinks-manager'),
							$category_a_option_value,
							$default_category_id,
							'main'
						);

						?>

						<!-- Data -->
						<div class="daim-handsontable-wrapper">
							<label for="data"><?php esc_html_e( 'Data', 'interlinks-manager'); ?></label>
							<div class="daim-handsontable-container">
								<div id="daim-table"></div>
							</div>
						</div>

						<!-- Submit (todo: replace it with a button displayed in the header bar) -->
						<div class="daext-form-action">
							<input id="generate-autolinks" class="daim-btn daim-btn-primary" type="submit" value="Generate AIL">
						</div>

					</div>

				</div>

			</div>

		</div>

		<?php

	}
}
