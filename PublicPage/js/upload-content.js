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
            const variationId = $("[name='variation_id']").val();
            $('.single_add_to_cart_button').addClass('ppi-disabled');
            $('#upload-info').html('');
            $('#upload-info').removeClass(); // removes all classes from upload info
            $('#ppi-loading').removeClass('ppi-hidden'); // display loading animation
            $('.thumbnail-container').css('background-image', ''); // remove thumbnail
            $('.thumbnail-container').removeClass('ppi-min-height');
            $('.thumbnail-container').prop('alt', '');

            const fileInput = document.getElementById('file-upload');
            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('action', 'upload_content_file');
            formData.append('file', file);
            formData.append('variant_id', variationId);
            // formData.append('_ajax_nonce', ppi_upload_content_object.nonce);

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
                        updatePrice(response.file.price_vat_incl);
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

                        // add content file id to hidden input
                        $("[name='variation_id']").after(
                            '<input type="hidden" name="content_file_id" class="content_file_id" value="' +
                                response.file.content_file_id +
                                '"></input>'
                        );
                        $('#ppi-loading').addClass('ppi-hidden');
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

        function setUploadBtnColour() {
            let btnColour = '';
            const domain = getDomain();
            switch (domain) {
                case 'devwebshop.peleman.com':
                    btnColour = '#ffd721';
                    break;
                case 'store.peleman.com':
                    btnColour = '#ffd721';
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
                    btnColour = '#006ad0';
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

        /**
         * Updates the price for the user after uploading a content file
         *
         * @param {number} price
         */
        function updatePrice(price) {
            const pricetext = $(
                'div.woocommerce-variation-price span.woocommerce-Price-amount'
            ).text();

            const currencySymbol = pricetext.replace(/[0-9]./g, '');
            const newPriceText = currencySymbol + price.toFixed(2);

            $(
                'div.woocommerce-variation-price span.woocommerce-Price-amount'
            ).text(newPriceText);
        }
    });
})(jQuery);
