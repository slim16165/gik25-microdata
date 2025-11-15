/**
 * This file is used to handle the submission of automatic internal links in the Wizard page.
 *
 * @package interlinks-manager
 */

(function ($) {

	'use strict';

	let hotInstance;

	$( document ).ready(
		function () {

			'use strict';

			initSelect2();

			initHot();

			bindEventListeners();

		}
	);

	function initHot() {

		'use strict';

		let hotData,
		daimContainer;

		// Initialize an empty table.
		hotData = [
		['Keyword', 'Target', 'Title'],
		];

		for (let i = 0; i < parseInt( window.objectL10n.wizardRows, 10 ); i++) {
			hotData.push( ['', '', ''] );
		}

		// Instantiate the handsontable table.
		daimContainer = document.getElementById( 'daim-table' );
		hotInstance   = new window.Handsontable(
			daimContainer,
			{

				// Set the table data.
				data: hotData,

				// Set the new maximum number of rows and columns.
				maxRows: parseInt( window.objectL10n.wizardRows, 10 ) + 1,
				maxCols: 3,

			}
		);

		hotInstance.updateSettings(
			{
				cells: function (row, col) {

					let cellProperties = {};

					if (row === 0 && (col === 0 || col === 1 || col === 2)) {
						cellProperties.readOnly               = true;
						cellProperties.disableVisualSelection = true;
					}

					return cellProperties;

				},
			}
		);

	}

	function bindEventListeners() {

		'use strict';

		$( document.body ).on(
			'click',
			'#generate-autolinks' ,
			function (event) {

				'use strict';

				event.preventDefault();

				generateAutolinks();

			}
		);

	}

	function generateAutolinks() {

		'use strict';

		let name,
		category_id,
		rawTableData,
		tableData = [];

		name        = $( '#name' ).val();
		category_id = parseInt( $( '#category_id' ).val(), 10 );

		// Remove first row from the array (because it includes the labels of the hot table).
		rawTableData = hotInstance.getData().slice( 1 );

		// Keep only the rows where the keyword and the target are present.
		for (let key1 in rawTableData) {
			if (rawTableData[key1][0] !== '' && rawTableData[key1][0] !== '') {
				tableData.push( rawTableData[key1] );
			}
		}

		// Convert the resulting JSON value to a JSON string.
		tableData = JSON.stringify( tableData );

		// Prepare ajax request.
		let data = {
			'action': 'daim_wizard_generate_ail',
			'security': window.DAIM_PARAMETERS.wizard_nonce,
			'name': name,
			'category_id': category_id,
			'table_data': tableData,
		};

		// Set ajax in synchronous mode.
		$.ajaxSetup( {async: false} );

		// Send ajax request.
		$.post(
			window.DAIM_PARAMETERS.ajax_url,
			data,
			function (result) {

				'use strict';

				if (result === 'invalid name') {

					// Reload the dashboard menu.
					window.location.replace( window.DAIM_PARAMETERS.admin_url + 'admin.php?page=daim-wizard&invalid_name=1' );

				} else {

					// Reload the dashboard menu.
					window.location.replace( window.DAIM_PARAMETERS.admin_url + 'admin.php?page=daim-wizard&result=' + result );

				}

			}
		);

		// Set ajax in asynchronous mode.
		$.ajaxSetup( {async: true} );

	}

	/**
	 * Initialize the select2 fields.
	 */
	function initSelect2() {

		'use strict';

		$( '#category_id' ).select2();

	}

}(window.jQuery));