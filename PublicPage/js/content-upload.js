(function ($) {
    'use strict';
    $(function () {
        $('.variations_form').on('show_variation', (e) => {
            $('.upload-label').removeClass('upload-disabled');
            $('.upload-parameters').removeClass('hidden');

            if ($('.ppi-upload-form')[0] !== undefined) {
                $('.single_add_to_cart_button').addClass('ppi-disabled');
            }
        });

        $('.variations_form').on('hide_variation', (e) => {
            $('.upload-label').addClass('upload-disabled');
            $('.upload-parameters').addClass('hidden');
        });

        $('#file-upload').on('change', (e) => {
            $('#variation-info').html('');
            $('#variation-info').removeClass();
            $('#ppi-loading').removeClass('ppi-hidden');

            $('#file-upload').submit();
            e.preventDefault();

            var fileInput = document.getElementById('file-upload');
            var file = fileInput.files[0];

            var formData = new FormData();
            formData.append('action', 'upload_content_file');
            formData.append('file', file);
            formData.append('variant_id', $("[name='variation_id']").val());
            formData.append('_ajax_nonce', ppi_content_upload_object.nonce);

            $.ajax({
                url: ppi_content_upload_object.ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                enctype: 'multipart/form-data',
                cache: false,
                dataType: 'json',
                success: function (response) {
                    $('#variation-info').html(response.message);
                    if (response.status === 'success') {
                        $('.single_add_to_cart_button').removeClass(
                            'ppi-disabled'
                        );
                        $('.single_add_to_cart_button').prop(
                            'href',
                            response.url
                        );
                        $('#ppi-loading').addClass('ppi-hidden');
                    } else {
                        $('#variation-info').html(response.message);
                        $('#variation-info').addClass('ppi-response-error');
                        $('#ppi-loading').addClass('ppi-hidden');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log({ jqXHR });
                    $('#variation-info').html(
                        'Something went wrong.  Please try again with a different file.'
                    );
                    $('#variation-info').addClass('response-error');
                    $('#ppi-loading').addClass('ppi-hidden');
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
            $('#file-upload').val('');
        });
    });
})(jQuery);
