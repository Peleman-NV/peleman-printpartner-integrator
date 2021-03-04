(function ($) {
    'use strict';
    $(function () {
        $('.variations_form').on('show_variation', e => {
            const variationId = $("[name='variation_id']").val();
            $('.upload-label').removeClass('upload-disabled');
            $('.ppi-params-loading').removeClass('ppi-hidden');
            getVariantDetails(variationId);
            $('.upload-parameters').removeClass('hidden');

            if ($('.ppi-upload-form')[0] !== undefined) {
                $('.single_add_to_cart_button').addClass('ppi-disabled');
            }
        });

        $('.variations_form').on('hide_variation', e => {
            $('.upload-label').addClass('upload-disabled');
            $('.upload-parameters').addClass('hidden');
        });

        function getVariantDetails(variantId) {
            const data = {
                variant: variantId,
                action: 'display_variant_info',
                _ajax_nonce: ppi_variant_information_object.nonce,
            };

            $.ajax({
                url: ppi_variant_information_object.ajax_url,
                method: 'GET',
                data: data,
                cache: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#content-height').html(
                            response.height != '' ? response.height + 'mm' : '-'
                        );
                        $('#content-width').html(
                            response.width != '' ? response.width + 'mm' : '-'
                        );
                        $('#content-min-pages').html(
                            response.min_pages != '' ? response.min_pages : '-'
                        );
                        $('#content-max-pages').html(
                            response.max_pages != '' ? response.max_pages : '-'
                        );
                    }
                    $('.ppi-params-loading').addClass('ppi-hidden');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log({ jqXHR });
                    console.error(
                        'Something went wrong:\n' +
                            jqXHR.status +
                            ': ' +
                            jqXHR.statusText +
                            '\nTextstatus: ' +
                            textStatus +
                            '\nError thrown: ' +
                            errorThrown
                    );
                },
            });
        }
    });
})(jQuery);
