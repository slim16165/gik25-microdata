<?php
/**
 * Class used to implement the back-end functionalities of the "Dashboard" menu.
 *
 * @package interlinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Dashboard" menu.
 */
class Daim_Dashboard_Menu_Elements extends Daim_Menu_Elements {

	/**
	 * Daim_Dashboard_Menu_Elements constructor.
	 *
	 * @param $shared
	 * @param $page_query_param
	 * @param $config
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'dashboard';
		$this->slug_plural        = 'dashboard';
		$this->label_singular     = 'Dashboard';
		$this->label_plural       = 'Dashboard';
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
	 * Display the content of the body.
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
