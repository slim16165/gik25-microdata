<?php
/**
 * Class used to implement the back-end functionalities of the "Maintenance" menu.
 *
 * @package interlinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Maintenance" menu.
 */
class Daim_Maintenance_Menu_Elements extends Daim_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param $shared
	 * @param $page_query_param
	 * @param $config
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'maintenance';
		$this->slug_plural        = 'maintenance';
		$this->label_singular     = 'Maintenance';
		$this->label_plural       = 'Maintenance';
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

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		if ( isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daim_execute_task', 'daim_execute_task_nonce' );

			// Sanitization ---------------------------------------------------------------------------------------------------.
			$data['task'] = isset($_POST['task']) ? intval( $_POST['task'], 10 ) : null;
			$data['from'] = isset($_POST['from']) ? intval( $_POST['from'], 10 ) : null;
			$data['to']   = isset($_POST['to']) ? intval( $_POST['to'], 10 ) : null;

			// Validation -----------------------------------------------------------------------------------------------------.

			$invalid_data_message = '';
			$invalid_data         = false;

			// validation.
			if ( $data['from'] >= $data['to'] ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid range.', 'interlinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			if ( ( $data['to'] - $data['from'] ) > 10000 ) {

				$this->shared->save_dismissible_notice(
					__( "For performance reasons the range can't include more than 10000 items.", 'interlinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			if ( false === $invalid_data ) {

				switch ( $data['task'] ) {

					// Delete AIL.
					case 0:
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$query_result = $wpdb->query( $wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}daim_autolinks WHERE id >= %d AND id <= %d",
							$data['from'],
							$data['to']
						) );

						if ( false !== $query_result ) {

							if ( $query_result > 0 ) {
								$this->shared->save_dismissible_notice(
									intval( $query_result, 10 ) . ' ' . __(
										'AIL have been successfully deleted.',
										'interlinks-manager'
									),
									'updated'
								);
							} else {
								$this->shared->save_dismissible_notice(
									__( 'There are no AIL in this range.', 'interlinks-manager' ),
									'error'
								);
							}
						}

						break;

					// Delete Categories.
					case 1:
						// Delete all the categories not used in the AIL.
						$deleted_categories = 0;
						for ( $category_id = $data['from']; $category_id <= $data['to']; $category_id++ ) {

							if ( $this->shared->category_exists( $category_id ) && $this->shared->category_is_used( $category_id ) === false ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery
								$query_result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}daim_category WHERE category_id = %d", $category_id ) );
								if ( 1 === $query_result ) {
									++$deleted_categories;
								}
							}
						}

						// Generate message.
						if ( $deleted_categories > 0 ) {
							$this->shared->save_dismissible_notice(
								intval(
									$deleted_categories,
									10
								) . ' ' . __(
									'categories have been successfully deleted.',
									'interlinks-manager'
								),
								'error'
							);
						} else {
							$this->shared->save_dismissible_notice(
								__(
									"The are no deletable categories in this range. Please note that categories associated with one or more AIL can't be deleted.",
									'interlinks-manager'
								),
								'error'
							);
						}

						break;

					// Delete Term Groups.
					case 2:
						// Delete all the term groups not used in autolinks.
						$deleted_term_groups = 0;
						for ( $term_group_id = $data['from']; $term_group_id <= $data['to']; $term_group_id++ ) {

							if ( $this->shared->term_group_exists( $term_group_id ) && false === $this->shared->term_group_is_used( $term_group_id ) ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery
								$query_result = $wpdb->query( $wpdb->prepare(
									"DELETE FROM {$wpdb->prefix}daim_term_group WHERE term_group_id = %d",
									$term_group_id
								) );
								if ( 1 === $query_result ) {
									++$deleted_term_groups;
								}
							}
						}

						// Generate message.
						if ( $deleted_term_groups > 0 ) {
							$this->shared->save_dismissible_notice(
								intval(
									$deleted_term_groups,
									10
								) . ' ' . esc_html__(
									'term groups have been successfully deleted.',
									'interlinks-manager'
								),
								'updated'
							);
						} else {
							$this->shared->save_dismissible_notice(
								__( "The are no deletable term groups in this range. Please note that term groups associated with one or more AIL can't be deleted.", 'interlinks-manager'),
								'error'
							);
						}

						break;

					// Delete Tracking.
					case 3:
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$query_result = $wpdb->query( $wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}daim_hits WHERE id >= %d AND id <= %d",
							$data['from'],
							$data['to']
						) );

						if ( false !== $query_result ) {

							if ( $query_result > 0 ) {
								$this->shared->save_dismissible_notice(
									intval(
										$query_result,
										10
									) . ' ' . esc_html__(
										'tracked clicks have been successfully deleted.',
										'interlinks-manager'
									),
									'updated'
								);
							} else {
								$this->shared->save_dismissible_notice(
									__( 'There are no tracked clicks in this range.', 'interlinks-manager'),
									'error'
								);
							}
						}

						break;

				}
			}
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

			<div class="daim-main-form">

				<form id="form-maintenance" method="POST"
				      action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-maintenance"
				      autocomplete="off">

				<div class="daim-main-form__daext-form-section">

					<div class="daim-main-form__daext-form-section-body">

							<input type="hidden" value="1" name="form_submitted">

							<?php wp_nonce_field( 'daim_execute_task', 'daim_execute_task_nonce' ); ?>

							<?php

							// Task.
							$this->select_field(
								'task',
								'Task',
								__('The task that should be performed.', 'interlinks-manager'),
								array(
									'0' => 'Delete AIL',
									'1' => 'Delete Categories',
									'2' => 'Delete Term Groups',
									'3' => 'Delete Hits',
								),
								null,
								'main'
							);

							// From.
							$this->input_field(
								'from',
								'From',
								'The initial ID of the range.',
								'The initial ID of the range.',
								'1',
								'main'
							);

							// To.
							$this->input_field(
								'to',
								'To',
								'The final ID of the range.',
								'The final ID of the range.',
								'1000',
								'main'
							);

							?>

							<!-- submit button -->
							<div class="daext-form-action">
								<input id="execute-task" class="daim-btn daim-btn-primary" type="submit"
										value="<?php esc_attr_e( 'Execute Task', 'interlinks-manager'); ?>">
							</div>

						</div>

					</div>

				</form>

			</div>

		</div>

		<!-- Dialog Confirm -->
		<div id="dialog-confirm" title="<?php esc_attr_e('Execute the task?', 'interlinks-manager'); ?>" class="daext-display-none">
			<p><?php esc_html_e('Multiple database items are going to be deleted. Do you really want to proceed?',
					'interlinks-manager'); ?></p>
		</div>

		<?php

	}

}
