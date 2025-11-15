<?php
/**
 * This class adds the options with the related callbacks and validations.
 *
 * @package interlinks-manager
 */

/**
 * This class adds the options with the related callbacks and validations.
 */
class Daim_Options_Menu_Elements extends Daim_Menu_Elements {

	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'options';
		$this->slug_plural        = 'options';
		$this->label_singular     = 'Options';
		$this->label_plural       = 'Options';
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

			<div id="react-root"></div>

		</div>

		<?php

	}

}
