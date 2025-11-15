/**
 * This file is used for the tracking of the internal links in the front end of the site.
 *
 * @package interlinks-manager
 */

jQuery( document ).ready(
	function ($) {

		'use strict';

		$( document.body ).on(
			'mousedown',
			'a[data-ail]' ,
			function () {

				'use strict';

				const link_type = 'ail';

				// Save the click with an ajax request.
				track_link( link_type, $( this ) );

			}
		);

		$( document.body ).on(
			'mousedown',
			'a[data-mil]' ,
			function () {

				'use strict';

				const link_type = 'mil';

				// Save the click with an ajax request.
				track_link( link_type, $( this ) );

			}
		);

		// Track the link with an ajax request.
		function track_link(link_type, caller_element){

			'use strict';

			// Set source.
			const source_post_id = caller_element.attr( 'data-' + link_type );

			// Set target.
			const target_url = caller_element.attr( 'href' );

			// Prepare ajax request.
			const data = {
				"action": "track_internal_link",
				"security": window.DAIM_PARAMETERS.nonce,
				"link_type": link_type,
				"source_post_id": source_post_id,
				"target_url": target_url
			};

			// Send the ajax request.
			$.post( window.DAIM_PARAMETERS.ajax_url, data, function (data) {} );

		}

	}
);