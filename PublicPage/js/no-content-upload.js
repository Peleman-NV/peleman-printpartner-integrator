(function ($) {
    'use strict';
    $(function () {
        $('.variations_form').on('show_variation', e => {
            $('.single_add_to_cart_button').addClass('ppi-disabled');

            if ($('.ppi-upload-form')[0] === undefined) {
                const data = {
                    action: 'get_imaxel_url',
                    variant_id: $("[name='variation_id']").val(),
                    _ajax_nonce: ppi_url_object.nonce,
                };
                console.log(data);
                $('#ppi-loading').removeClass('ppi-hidden');
                $('#variation-info').html('');
                $('#variation-info').removeClass();

                getImaxelUrl(data);
            }
        });

        function getImaxelUrl(data) {
            $.ajax({
                url: ppi_url_object.ajax_url,
                method: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    $('.single_add_to_cart_button').remove();
                    if (response.status === 'success') {
                        if (response.showButton) {
                            $('.quantity').after(
                                "<button type='submit' class='single_add_to_cart_button button alt'>Add To Cart</button>"
                            );
                        } else {
                            $('.quantity').after(
                                "<a href='" +
                                    response.url +
                                    "' class='ppi-add-to-cart-button single_add_to_cart_button button alt'><span id='ppi-loading' class='ppi-hidden dashicons dashicons-update rotate'></span>" +
                                    response.buttonText +
                                    '</a>'
                            );
                            $('#ppi-loading').addClass('ppi-hidden');
                        }
                    } else {
                        $('#variant-info').html(response.message);
                        $('#variant-info').addClass('ppi-response-error');
                        $('#ppi-loading').addClass('ppi-hidden');
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
