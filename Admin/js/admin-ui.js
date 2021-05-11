(function ($) {
    'use strict';
    $(document).on('woocommerce_variations_loaded', function (event) {
        $('.ppi-options-group .checkbox').on('click', e => {
            const inputArray = $(e.target)
                .parent()
                .siblings()
                .children("input[id^='pdf']");

            if (e.target.checked) {
                jQuery.each(inputArray, index => {
                    $(inputArray[index]).prop('readonly', false);
                });
            } else {
                jQuery.each(inputArray, index => {
                    $(inputArray[index]).val('');
                    $(inputArray[index]).prop('readonly', true);
                });
            }
        });
    });
})(jQuery);
