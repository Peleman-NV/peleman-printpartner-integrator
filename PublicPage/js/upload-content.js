(function ($) {
    'use strict';
    $(function () {
        setUploadBtnColour();

        // Event: when the file input changes, ie: when a new file is selected
        $('#file-upload').on('change', e => {
            const timeStart = performance.now();
            const variationId = $("[name='variation_id']").val();
            $('.single_add_to_cart_button').addClass('ppi-disabled');
            $('#upload-info').html('');
            $('#upload-info').removeClass(); // removes all classes from upload info
            $('#ppi-loading').removeClass('ppi-hidden'); // display loading animation
            $('.thumbnail-container').css('background-image', ''); // remove thumbnail
            $('.thumbnail-container').removeClass('ppi-min-height');
            $('.thumbnail-container').prop('alt', '');

            // create AJAX POST object for PHP
            const fileInput = document.getElementById('file-upload');
            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('action', 'upload_content_file');
            formData.append('file', file);
            formData.append('variant_id', variationId);
            formData.append('_ajax_nonce', ppi_upload_content_object.nonce);

            // autmatically submit form on change event
            $('#file-upload').submit();
            e.preventDefault();

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
                    $('#upload-info').html(response.message);
                    if (response.status === 'success') {
                        // enable add to cart button
                        $('.single_add_to_cart_button').removeClass(
                            'ppi-disabled'
                        );
                        // this put the correct URL in place - shouldn't happen here
                        // $('.single_add_to_cart_button').prop(
                        //     'href',
                        //     response.url
                        // );
                        //$('.ppi-upload-parameters').removeClass('ppi-hidden'); // dunno why this is here
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
                    } else {
                        // if AJAX return is good but it contains an error, add error styling and show msg
                        $('#upload-info').html(response.message);
                        $('#upload-info').addClass('ppi-response-error');
                        $('#ppi-loading').addClass('ppi-hidden');
                    }
                },
                // if AJAX fails
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

        // function replaceBtnWithLink(url) {
        //     // get btn textStatus
        //     const btnText = $('.single_add_to_cart_button').html();

        //     $('.single_add_to_cart_button').remove();

        //     $('.quantity').after(
        //         "<a href='" +
        //             url +
        //             "' class='ppi-add-to-cart-button single_add_to_cart_button button alt'><span id='ppi-loading' class='dashicons dashicons-update rotate'></span>" +
        //             btnText +
        //             '</a>'
        //     );
        //     $('.single_add_to_cart_button').removeClass('ppi-disabled');
        //     $('#ppi-loading').addClass('ppi-hidden');
        // }

        function setUploadBtnColour() {
            let btnColour = '';
            const domain = getDomain();
            switch (domain) {
                case 'devwebshop.peleman.com':
                    btnColour = '#ffd721'; /* devwebshop.com */
                    break;
                case 'devhumancolours.peleman.com':
                    btnColour = '#ff661f';
                    $('.ppi-upload-form .upload-label').css('color', 'white');
                    break;
                case 'humancolours.peleman.com':
                    btnColour = '#ff661f';
                    $('.ppi-upload-form .upload-label').css('color', 'white');
                    break;
                case 'devshop.peleman.com':
                    btnColour = '#006ad0'; /* devshop.com */
                    $('.ppi-upload-form .upload-label').css('color', 'white');
                    break;
            }

            $('.ppi-upload-form').css('background', btnColour);
        }

        function getDomain() {
            const url = window.location.href;
            return url.substring(
                url.indexOf('//') + 2,
                url.indexOf('.com') + 4
            );
        }
    });
})(jQuery);
