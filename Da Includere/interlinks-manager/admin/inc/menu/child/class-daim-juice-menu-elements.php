<?php
/**
 * Class used to implement the back-end functionalities of the "Juice" menu.
 *
 * @package interlinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Juice" menu.
 */
class Daim_Juice_Menu_Elements extends Daim_Menu_Elements {

	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'juice';
		$this->slug_plural        = 'juice';
		$this->label_singular     = 'Juice';
		$this->label_plural       = 'Juice';
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
	 * Display the content of the body of the page.
	 *
	 * @return void
	 */
	function display_custom_content() {

		?>

		<div class="daim-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			// Display the license activation notice.
			$this->shared->display_license_activation_notice();

			?>

			<div id="react-root"></div>

		</div>

		<?php

	}
}
