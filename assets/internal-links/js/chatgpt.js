/**
 * ChatGPT Integration Frontend JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // ChatGPT query handler
        $('.gik25-il-chatgpt-query').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $container = $button.closest('.gik25-il-chatgpt-container');
            var $input = $container.find('.gik25-il-chatgpt-input');
            var $output = $container.find('.gik25-il-chatgpt-output');
            var query = $input.val();

            if (!query) {
                alert('Please enter a query');
                return;
            }

            $button.prop('disabled', true).text('Processing...');
            $output.html('<div class="gik25-il-loading">Loading...</div>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'gik25_il_chatgpt_query',
                    query: query,
                    context: $container.data('context') || '',
                    nonce: gik25_il_ajax.nonce || ''
                },
                success: function(response) {
                    if (response.success) {
                        $output.html('<div class="gik25-il-response">' + response.data.response + '</div>');
                    } else {
                        $output.html('<div class="gik25-il-error">Error: ' + (response.data.message || 'Unknown error') + '</div>');
                    }
                },
                error: function() {
                    $output.html('<div class="gik25-il-error">Request failed. Please try again.</div>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Send');
                }
            });
        });
    });

})(jQuery);

