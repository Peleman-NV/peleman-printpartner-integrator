(function ($) {
    'use strict';
    $(function () {
        $('.variations_form').on('show_variation', (e) => {
            $('.single_add_to_cart_button').addClass('ppi-disabled');

            if ($('.ppi-upload-form')[0] === undefined) {
                const data = {
                    action: 'get_imaxel_url',
                    variant_id: $("[name='variation_id']").val(),
                    _ajax_nonce: ppi_url_object.nonce,
                };

                // TODO keep button disabled until result is in
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
                    if (response.status === 'success') {
                        $('.single_add_to_cart_button').removeClass(
                            'ppi-disabled'
                        );
                        $('.single_add_to_cart_button').prop(
                            'href',
                            response.url
                        );
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
