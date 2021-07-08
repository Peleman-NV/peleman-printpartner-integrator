/**
 * This script is responsible for redirecting customers to the Imaxel editor,
 * if the product has a template attached, in the admin backend.
 * It fires on the click event, and passes the variantId and,
 * if present, the content file Id.  It call the * the PHP function
 * "get_imaxel_redirection" in PublicPage/PpiProductPage.php,
 * which makes a request to Imaxel to create a project.
 * It then persists this as a customer project and redirects the user to the editor.
 *
 * In the event of an error, it's displayed.
 * Errors can be caused by:
 * -an outage of the Imaxel servers (rare)
 * -a non-existing template defined in the backend
 */

(function ($) {
    ('use strict');
    $(function () {
        $('.single_add_to_cart_button').on('click', e => {
            e.preventDefault();
            const variationId = $("[name='variation_id']").val();
            const contentFileId = $("[name='content_file_id']").val();

            getImaxelRedirection(variationId, contentFileId);

            function getImaxelRedirection(variationId, contentFileId = null) {
                $('#redirection-info').html('');
                const data = {
                    variant: variationId,
                    content: contentFileId,
                    action: 'get_imaxel_redirection',
                    _ajax_nonce: ppi_imaxel_redirection_object.nonce,
                };

                $.ajax({
                    url: ppi_imaxel_redirection_object.ajax_url,
                    method: 'GET',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        if (response.status === 'success') {
                            window.location.href = response.url;
                        }
                        if (response.status === 'error') {
                            $('#redirection-info').html(response.message);
                            $('#redirection-info').addClass(
                                'ppi-response-error'
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
    });
})(jQuery);
