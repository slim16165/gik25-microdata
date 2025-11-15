/**
 * This file is used to initialize the jquery-ui-tooltip.
 *
 * @package interlinks-manager
 */
jQuery( document ).ready(
	function ($) {

		'use strict';

		// Init jquery-ui-tooltip.
		$(
			function () {

				'use strict';

				$( '.help-icon' ).tooltip( {show: false, hide: false} );

			}
		);

	}
);