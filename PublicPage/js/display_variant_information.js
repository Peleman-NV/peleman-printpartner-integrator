(function ($) {
    'use strict';
    $(function () {
        $('.variations_form').on('show_variation', e => {
            const variationId = $("[name='variation_id']").val();
            $('.upload-label').removeClass('upload-disabled');
            $('.ppi-upload-parameters').addClass('ppi-hidden');
            getVariantDetails(variationId);
            $('.upload-parameters').removeClass('ppi-hidden');

            if ($('.ppi-upload-form')[0] !== undefined) {
                $('.single_add_to_cart_button').addClass('ppi-disabled');
            }
        });

        $('.variations_form').on('hide_variation', e => {
            $('.upload-label').addClass('upload-disabled');
            $('.upload-parameters').addClass('ppi-hidden');
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
                    console.log(response);
                    if (response.status === 'success') {
                        if (response.height != '') {
                            $('#content-height').html(response.height + 'mm');
                            $('#content-height')
                                .parent()
                                .removeClass('ppi-hidden');
                        } else {
                            $('#content-height')
                                .parent()
                                .addClass('ppi-hidden');
                        }
                        if (response.width != '') {
                            $('#content-width').html(response.width + 'mm');
                            $('#content-width')
                                .parent()
                                .removeClass('ppi-hidden');
                        } else {
                            $('#content-width').parent().addClass('ppi-hidden');
                        }
                        if (response.min_pages != '') {
                            $('#content-min-pages').html(response.min_pages);
                            $('#content-min-pages')
                                .parent()
                                .removeClass('ppi-hidden');
                        } else {
                            $('#content-min-pages')
                                .parent()
                                .addClass('ppi-hidden');
                        }
                        if (response.max_pages != '') {
                            $('#content-max-pages').html(response.max_pages);
                            $('#content-max-pages')
                                .parent()
                                .removeClass('ppi-hidden');
                        } else {
                            $('#content-max-pages')
                                .parent()
                                .addClass('ppi-hidden');
                        }
                        if (
                            response.height != '' ||
                            response.width != '' ||
                            response.min_pages != '' ||
                            response.max_pages != ''
                        ) {
                            console.log('at least one filled in');
                            $('.ppi-upload-parameters').removeClass(
                                'ppi-hidden'
                            );
                        }
                    }
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
