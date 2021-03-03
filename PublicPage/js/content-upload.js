(function ($) {
    'use strict';
    $(function () {
        $('.variations_form').on('show_variation', e => {
            $('#upload-info').html('');
        });

        $('#file-upload').on('change', e => {
            const timeStart = performance.now();
            console.time('Process duration');

            const variationId = $("[name='variation_id']").val();
            $('.single_add_to_cart_button').addClass('ppi-disabled');
            $('#upload-info').html('');
            $('#upload-info').removeClass();
            $('#ppi-loading').removeClass('ppi-hidden');
            $('.thumbnail-container').css('background-image', '');
            $('.thumbnail-container').prop('alt', '');

            const fileInput = document.getElementById('file-upload');
            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('action', 'upload_content_file');
            formData.append('file', file);
            formData.append('variant_id', variationId);
            formData.append('_ajax_nonce', ppi_content_upload_object.nonce);

            $('#file-upload').submit();
            e.preventDefault();

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
                    console.log(response);
                    $('#upload-info').html(response.message);
                    if (response.status === 'success') {
                        $('.single_add_to_cart_button').removeClass(
                            'ppi-disabled'
                        );
                        $('.single_add_to_cart_button').prop(
                            'href',
                            response.url
                        );
                        $('.thumbnail-container').css(
                            'background-image',
                            'url("' + response.file.thumbnail + '")'
                        );
                        $('.thumbnail-container').prop(
                            'alt',
                            response.file.name
                        );
                        $('#ppi-loading').addClass('ppi-hidden');
                        const timeEnd = performance.now();
                        const duration = ((timeEnd - timeStart) / 1000).toFixed(
                            4
                        );
                        console.log('It took ' + duration + ' seconds.');
                        console.timeEnd('Process duration');
                    } else {
                        $('#upload-info').html(response.message);
                        $('#upload-info').addClass('ppi-response-error');
                        $('#ppi-loading').addClass('ppi-hidden');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    $('#upload-info').html(
                        'Something went wrong.  Please try again with a different file.'
                    );
                    $('#upload-info').addClass('response-error');
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
