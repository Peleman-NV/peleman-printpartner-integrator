/**
 * This script is only responsible to the content file upload.
 * If a product requires a content file, 'variable-product.js' will load an upload form.
 * This script fires on the change event of that form, and performs an AJAX call to
 * the PHP function "upload_content_file" in PublicPage/PpiProductPage.php,
 * where the file is validated and uploaded to the server on success.
 * A response is then return (success or error) after which the "add to cart" button is
 * enabled, or an error message is displayed.
 *
 * The upload button's colour is also set, depending on the URL.
 */

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
                default:
                    $('.ppi-upload-form .upload-label').css(
                        'border',
                        '1px solid grey'
                    );
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
