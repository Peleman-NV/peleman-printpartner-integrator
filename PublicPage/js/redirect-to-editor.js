(function ($) {
    'use strict';

    /**
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(function () {
        $('.ppi-add-to-cart-button').on('click', (e) => {
            e.preventDefault();
            if ($('.ppi-add-to-cart-button').hasClass('disabled')) {
                return;
            }

            const data = {
                action: 'redirect_to_imaxel_editor',
                variant_id: $("[name='variation_id']").val(),
                _ajax_nonce: ppi_ajax_redirect.nonce,
            };

            $.ajax({
                url: ppi_ajax_redirect.ajax_url,
                method: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.type === 'success') {
                        window.location.href = response.url;
                    } else {
                        console.error('Something went wrong: ' + response);
                    }
                },
            });
        });
    });
})(jQuery);
