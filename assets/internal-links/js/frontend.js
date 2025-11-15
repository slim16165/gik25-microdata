/**
 * Internal Links Frontend JavaScript
 */

(function() {
    'use strict';

    // Track clicks on internal links
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a[data-gik25-il-link-id]');
        if (!link) {
            return;
        }

        var linkId = link.getAttribute('data-gik25-il-link-id');
        var postId = link.getAttribute('data-gik25-il-post-id');

        if (linkId && postId && typeof ajaxurl !== 'undefined') {
            // Send tracking request
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(
                'action=gik25_il_track_link_click' +
                '&link_id=' + encodeURIComponent(linkId) +
                '&post_id=' + encodeURIComponent(postId) +
                '&nonce=' + (typeof gik25_il_ajax !== 'undefined' ? gik25_il_ajax.nonce : '')
            );
        }
    });

})();

