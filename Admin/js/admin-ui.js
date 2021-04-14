(function ($) {
    'use strict';
    $(document).on('woocommerce_variations_loaded', function (event) {
        $('.ppi-options-group .checkbox').on('click', e => {
            const inputArray = $(e.target)
                .parent()
                .siblings()
                .children("input[id^='pdf']");
            const pricePerPage = $(e.target)
                .parent()
                .siblings()
                .children("input[id^='price_per_page']");

            if (e.target.checked) {
                jQuery.each(inputArray, index => {
                    $(inputArray[index]).prop('readonly', false);
                });
                pricePerPage.prop('readonly', false);
            } else {
                jQuery.each(inputArray, index => {
                    $(inputArray[index]).val('');
                    $(inputArray[index]).prop('readonly', true);
                });
                pricePerPage.val('');
                pricePerPage.prop('readonly', true);
            }
        });
    });
})(jQuery);
