/**
 * Internal Links Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Track link clicks
        $('.gik25-il-track-click').on('click', function(e) {
            var linkId = $(this).data('link-id');
            var postId = $(this).data('post-id');

            if (linkId && postId) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gik25_il_track_link_click',
                        link_id: linkId,
                        post_id: postId,
                        nonce: gik25_il_ajax.nonce || ''
                    }
                });
            }
        });

        // Autolinks table actions
        $('.gik25-il-bulk-action').on('click', function(e) {
            e.preventDefault();
            var action = $(this).data('action');
            var selected = $('.gik25-il-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selected.length === 0) {
                alert('Please select at least one item');
                return;
            }

            if (confirm('Are you sure you want to ' + action + ' ' + selected.length + ' item(s)?')) {
                // TODO: Implement bulk actions
                console.log('Bulk action:', action, selected);
            }
        });
    });

})(jQuery);

