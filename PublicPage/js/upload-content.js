(function ($) {
    'use strict';
    $(function () {
        // Event: when the file input changes, ie: when a new file is selected
        $('#file-upload').on('change', e => {
            const timeStart = performance.now();
            const variationId = $("[name='variation_id']").val();
            $('.single_add_to_cart_button').addClass('ppi-disabled');
            $('#upload-info').html('');
            $('#upload-info').removeClass();
            $('#ppi-loading').removeClass('ppi-hidden');
            $('.thumbnail-container').css('background-image', '');
            $('.thumbnail-container').removeClass('ppi-min-height');
            $('.thumbnail-container').prop('alt', '');

            const fileInput = document.getElementById('file-upload');
            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('action', 'upload_content_file');
            formData.append('file', file);
            formData.append('variant_id', variationId);
            formData.append('_ajax_nonce', ppi_upload_content_object.nonce);

            $('#file-upload').submit();
            e.preventDefault();
            // add GET param to add to cart URL to be saved in DB
            $.ajax({
                url: ppi_upload_content_object.ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                enctype: 'multipart/form-data',
                cache: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.do_not_redirect === true) {
                        console.log('custom project is here');
                        replaceBtnWithLink(response.url);
                    }
                    $('#upload-info').html(response.message);
                    if (response.status === 'success') {
                        $('.single_add_to_cart_button').removeClass(
                            'ppi-disabled'
                        );
                        $('.single_add_to_cart_button').prop(
                            'href',
                            response.url
                        );
                        $('.ppi-upload-parameters').removeClass('ppi-hidden');
                        $('.thumbnail-container').addClass('ppi-min-height');
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
                        const fileSize =
                            (response.file.filesize / 1024 / 1024).toFixed(2) +
                            ' MB.';
                        console.log(
                            'It took ' +
                                duration +
                                ' seconds to upload ' +
                                fileSize
                        );
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

        function replaceBtnWithLink(url) {
            // get btn textStatus
            const btnText = $('.single_add_to_cart_button').html();

            $('.single_add_to_cart_button').remove();
            $('#ppi-loading').addClass('ppi-hidden');
            $('.single_add_to_cart_button').removeClass('ppi-disabled');

            $('.quantity').after(
                "<a href='" +
                    url +
                    "' class='ppi-add-to-cart-button single_add_to_cart_button button alt'><span id='ppi-loading' class='ppi-hidden dashicons dashicons-update rotate'></span>" +
                    btnText +
                    '</a>'
            );
            $('#ppi-loading').addClass('ppi-hidden');
        }
    });
})(jQuery);
