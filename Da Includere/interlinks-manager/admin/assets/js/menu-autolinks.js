/**
 * This file is used to handle the submission of automatic internal links in the Wizard page.
 *
 * @package interlinks-manager
 */

(function ($) {

	'use strict';

	$( document ).ready(
		function () {

			'use strict';

			initSelect2();

			$( document.body ).on(
				'click',
				'#cancel' ,
				function (event) {

					// Reload the Categories menu.
					event.preventDefault();
					window.location.replace( window.daim_admin_url + 'admin.php?page=daim-autolinks' );

				}
			);

			// Dialog Confirm -----------------------------------------------------------------------------------------.
			window.DAIM = {};
			$( document.body ).on(
				'click',
				'.daim-crud-table__row-actions-single-action-delete button',
				function (event) {

					'use strict';

					event.preventDefault();
					window.DAIM.autolinkToDelete = $( this ).val();
					$( '#dialog-confirm' ).dialog( 'open' );

				}
			);

		}
	);

	/**
	 * Initialize the select2 fields.
	 */
	function initSelect2() {

		'use strict';

		let options = {
			placeholder: window.objectL10n.chooseAnOptionText,
		};

		$( '#category_id' ).select2();
		$('#activate_post_types').select2(options);
		$('#categories').select2(options);
		$('#tags').select2(options);
		$('#term_group_id').select2();
		$('#string_before').select2();
		$('#string_after').select2();

	}

	/**
	 * Dialog confirm initialization.
	 */
	$(
		function () {

			'use strict';

			$( '#dialog-confirm' ).dialog(
				{
					autoOpen: false,
					resizable: false,
					height: 'auto',
					width: 340,
					modal: true,
					buttons: {
						[objectL10n.deleteText]: function () {

							'use strict';

							$( '#delete-item-' + window.DAIM.autolinkToDelete ).submit();

						},
						[objectL10n.cancelText]: function () {

							'use strict';

							$( this ).dialog( 'close' );

						},
					},
				}
			);
		}
	);

}(window.jQuery));