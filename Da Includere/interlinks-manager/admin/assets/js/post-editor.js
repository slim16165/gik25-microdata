/**
 * This file is used to handle the JavaScript related operations in the post editor.
 *
 * Note that this file runs only if the block editor is disabled.
 *
 * @package interlinks-manager
 */

/**
 * This file should run only if in the post editor the block editor is disabled.
 */
(function () {
	if (typeof wp.blocks !== 'undefined') {
		// If the block editor is available, exit the script.
		return;
	}

	const { __ } = wp.i18n; // Import the __ function for translations

	jQuery(document).ready(function ($) {
		'use strict';

		/**
		 * REST API call that generates a list of interlinks suggestion in the "Interlinks Suggestions" meta box.
		 */
		$(document.body).on('click', '#generate-ideas', function () {
			// If another request is being processed, do not proceed.
			if ($('#ajax-request-status').val() === 'processing') {
				return;
			}

			// Get the post ID.
			const postId = parseInt($(this).attr('data-post-id'), 10);

			// Get the post status from the hidden input field in the Classic Editor.
			const postStatus = $('#hidden_post_status').val();
			if (postStatus === 'auto-draft') {
				alert(__('Please save the post before generating interlink suggestions.', 'interlinks-manager'));
				return;
			}

			// Show the spinner and set the request status.
			$('#daim-meta-suggestions .spinner').css('visibility', 'visible');
			$('#ajax-request-status').val('processing');

			// Use wp.apiFetch to call the REST API endpoint.
			wp.apiFetch({
				path: '/interlinks-manager-pro/v1/generate-interlinks-suggestions',
				method: 'POST',
				data: { id: postId },
			})
				.then((response) => {
					if (response.error) {
						// Do nothing if the response contains an error.
					} else {
						// Generate HTML and insert it into the #daim-meta-message element.
						const metaMessageContainer = document.getElementById('daim-interlinks-suggestions-list');
						metaMessageContainer.innerHTML = ''; // Clear existing content

						if (!Array.isArray(response) || response.length === 0) {

							metaMessageContainer.innerHTML = `
							<p>
							  ${__('There are no interlink suggestions available. Please ensure you have at least five other posts that meet the criteria defined in the "Suggestions" options.', 'interlinks-manager')}
							</p>
						  `;

						}else{

							response.forEach((item, index) => {
								const suggestionHTML = `
                            <div class="daim-interlinks-suggestions-item">
                            <div class="daim-interlinks-suggestions-container-left">
                                <a href="${item.link}" data-index="${index + 1}" target="_blank" class="daim-suggestion-link">
                                    ${item.icon_svg ? `<span class="daim-suggestion-icon">${item.icon_svg}</span>` : ''}
                                    <span class="daim-suggestion-title">${item.title}</span>
                                </a>
                                <span class="daim-interlinks-suggestions-post-type">${item.post_type}</span>
                            </div>
							<div
							  class="daim-interlinks-suggestions-copy-button"
							  data-index="${index + 1}"
							>
							  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.625 5.5h9.75c.069 0 .125.056.125.125v9.75a.125.125 0 0 1-.125.125h-9.75a.125.125 0 0 1-.125-.125v-9.75c0-.069.056-.125.125-.125ZM4 5.625C4 4.728 4.728 4 5.625 4h9.75C16.273 4 17 4.728 17 5.625v9.75c0 .898-.727 1.625-1.625 1.625h-9.75A1.625 1.625 0 0 1 4 15.375v-9.75Zm14.5 11.656v-9H20v9C20 18.8 18.77 20 17.251 20H6.25v-1.5h11.001c.69 0 1.249-.528 1.249-1.219Z"></path></svg>
							</div>
							</div>`;
								metaMessageContainer.innerHTML += suggestionHTML;
							});

						}

						// Make the daim-interlinks-suggestions-list element visible.
						document.getElementById('daim-interlinks-suggestions-list').style.display = 'block';
					}
				})
				.catch((error) => {
					alert(__('An error occurred: ', 'interlinks-manager') + error.message);
				})
				.finally(() => {
					// Hide the spinner and reset the request status.
					$('#daim-meta-suggestions .spinner').css('visibility', 'hidden');
					$('#ajax-request-status').val('inactive');
				});
		});

		/**
		 * When a click is performed on the copy button of the single interlink
		 * suggestion the href attribute of the link associated with the copy button
		 * is first copied to an hidden input field, then selected, and then copied to
		 * the clipboard.
		 *
		 * This method is used to skip the limitations associated with the use of the
		 * clipboard.
		 *
		 * Ref: https://developer.mozilla.org/en-US/docs/Mozilla/Add-ons/WebExtensions/Interact_with_the_clipboard
		 */
		$(document.body).on('click', '.daim-interlinks-suggestions-copy-button', function () {
			'use strict';

			// Save the index of the copied suggestion (the index goes from 1 to 5).
			let index = $(this).attr('data-index');

			// Get the href value of the links associated with the index.
			let hrefValue = $(`.daim-suggestion-link[data-index="${index}"]`).attr('href');

			// Put the href value in the hidden input.
			$('#daim-interlinks-suggestions-hidden-input').val(hrefValue);

			// Select the text in the input (this input allows the clipboard functionality to work properly).
			let copyText = document.querySelector('#daim-interlinks-suggestions-hidden-input');
			copyText.select();

			// Copy the selected text to the clipboard.
			document.execCommand('copy');

			// Show the notification
			showCopyNotification('Copied link to clipboard.');
		});

		/**
		 * Show a temporary notification in the bottom right corner of the screen.
		 */
		function showCopyNotification(message) {
			let existingNotice = document.querySelector('#daim-copy-notice');

			// Create the element if it doesn't exist
			if (!existingNotice) {
				const notice = document.createElement('div');
				notice.id = 'daim-copy-notice';
				notice.style.cssText = `
			position: fixed;
			bottom: 20px;
			left: 20px;
			background: #23282d;
			color: #fff;
			padding: 10px 15px;
			border-radius: 3px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.3);
			font-size: 13px;
			z-index: 9999;
			opacity: 0;
			transition: opacity 0.3s ease;
		`;
				notice.textContent = message;
				document.body.appendChild(notice);

				// Animate it in
				requestAnimationFrame(() => {
					notice.style.opacity = 1;
				});

				// Remove after 2 seconds
				setTimeout(() => {
					notice.style.opacity = 0;
					setTimeout(() => {
						notice.remove();
					}, 300);
				}, 2000);
			} else {
				// If already exists, just reset the timer and message
				existingNotice.textContent = message;
				existingNotice.style.opacity = 1;

				setTimeout(() => {
					existingNotice.style.opacity = 0;
					setTimeout(() => {
						existingNotice.remove();
					}, 300);
				}, 2000);
			}
		}

		/**
		 * Populate the Interlinks Suggestions section on page load
		 */
		const postId = parseInt($('#post_ID').val(), 10);
		if (postId) {
			updateInterlinksOptimizationMetaBox(postId);
		}

		/**
		 * Here the wp.data API is used to detect when a post is modified and the Interlinks Optimization meta-box needs to be
		 * updated.
		 *
		 * Note that the update of the Interlinks Optimization meta-box is performed only if:
		 *
		 * - The Gutenberg editor is available. (wp.blocks is checked against undefined)
		 * - The Interlinks Optimization meta-box is present in the DOM (because in specific post types or when the user
		 *   doesn't have the proper capability too see it it's not available)
		 *
		 * References:
		 *
		 * - https://github.com/WordPress/gutenberg/issues/4674#issuecomment-404587928
		 * - https://wordpress.org/gutenberg/handbook/packages/packages-data/
		 * - https://www.npmjs.com/package/@wordpress/data
		 */
		if (typeof wp.blocks !== 'undefined' && $('#daim-meta-optimization').length > 0) {
			let objectIsEmpty = true;
			let obj = wp.data.select('core/editor');
			for (let key in obj) {
				if (obj.hasOwnProperty(key)) {
					objectIsEmpty = false;
				}
			}
			if (objectIsEmpty) {
				return;
			}

			let lastModified = '';

			const unsubscribe = wp.data.subscribe(function () {
				'use strict';

				const postId = wp.data.select('core/editor').getCurrentPost().id;
				let postModifiedIsChanged = false;

				if (
					typeof wp.data.select('core/editor').getCurrentPost().modified !== 'undefined' &&
					wp.data.select('core/editor').getCurrentPost().modified !== lastModified
				) {
					lastModified = wp.data.select('core/editor').getCurrentPost().modified;
					postModifiedIsChanged = true;
				}

				/**
				 * Update the Interlinks Optimization meta-box if:
				 *
				 * - The post has been saved.
				 * - This is not an autosave.
				 * - The "lastModified" flag used to detect if the post "modified" date has changed is set to true.
				 */
				if (
					wp.data.select('core/editor').isSavingPost() &&
					!wp.data.select('core/editor').isAutosavingPost() &&
					postModifiedIsChanged === true
				) {
					updateInterlinksOptimizationMetaBox(postId);
				}
			});
		}

		/**
		 * Updates the Interlinks Optimization meta-box content.
		 *
		 * @param post_id The id of the current post
		 */
		function updateInterlinksOptimizationMetaBox(post_id) {
			'use strict';

			// Use wp.apiFetch to call the REST API endpoint.
			wp.apiFetch({
				path: '/interlinks-manager-pro/v1/generate-interlinks-optimization',
				method: 'POST',
				data: { id: post_id },
			})
				.then((response) => {
					if (response) {
						// Extract data from the response.
						const totalNumberOfInterlinks = response['total_number_of_interlinks'];
						const numberOfManualInterlinks = response['number_of_manual_interlinks'];
						const numberOfAutoInterlinks = response['number_of_autolinks'];
						const suggestedMin = response['suggested_min_number_of_interlinks'];
						const suggestedMax = response['suggested_max_number_of_interlinks'];

						// Generate the HTML content based on the response.
						let htmlContent = '';
						if (totalNumberOfInterlinks >= suggestedMin && totalNumberOfInterlinks <= suggestedMax) {
							htmlContent += `<p>${__('The number of internal links in this post is optimized.', 'interlinks-manager')}</p>`;
						} else {
							htmlContent += `<p>${__('Please optimize the number of internal links. This post currently contains', 'interlinks-manager')} ${totalNumberOfInterlinks} ${totalNumberOfInterlinks === 1 ? __('internal link', 'interlinks-manager') : __('internal links', 'interlinks-manager')} (${numberOfManualInterlinks} ${numberOfManualInterlinks === 1 ? __('manual internal link', 'interlinks-manager') : __('manual internal links', 'interlinks-manager')} ${__('and', 'interlinks-manager')} ${numberOfAutoInterlinks} ${numberOfAutoInterlinks === 1 ? __('auto internal link', 'interlinks-manager') : __('auto internal links', 'interlinks-manager')}).</p>`;

							if (suggestedMin === suggestedMax) {
								htmlContent += `<p>${__('Based on the content length and your settings, the ideal number of internal links should be', 'interlinks-manager')} ${suggestedMin}.</p>`;
							} else {
								htmlContent += `<p>${__('Based on the content length and your settings, the ideal number of internal links should fall between', 'interlinks-manager')} ${suggestedMin} ${__('and', 'interlinks-manager')} ${suggestedMax}.</p>`;
							}
						}

						// Update the content of the meta-box.
						document.querySelector('#daim-meta-optimization td').innerHTML = htmlContent;
					}
				})
				.catch((error) => {
					console.error('Error fetching interlinks optimization data:', error);
				});
		}
	});
})();