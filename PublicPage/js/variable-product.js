(function ($) {
    ('use strict');
    $(function () {
        // Event: when a variation is selected
        $('.variations_form').on('show_variation', e => {
            const variationId = $("[name='variation_id']").val();
            initRefreshVariantElements();

            getProductVariationData(variationId);
        });

        // Event: when a new variation is chosen
        $('.variations_form').on('hide_variation', e => {
            hideUploadElements();
            disableAddToCartBtn();
        });

        function getProductVariationData(variationId) {
            const data = {
                variant: variationId,
                action: 'get_product_variation_data',
                _ajax_nonce: ppi_product_variation_information_object.nonce,
            };
            let language = getSiteLanguage();
            let addToCartLabel = setAddToCartLabel(language);

            $.ajax({
                url: ppi_product_variation_information_object.ajax_url,
                method: 'GET',
                data: data,
                cache: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        if (
                            response.isCustomizable === 'no' ||
                            response.isCustomizable === ''
                        ) {
                            if (
                                response.requiresPDFUpload === 'no' ||
                                response.requiresPDFUpload === ''
                            ) {
                                // no upload & not customizable - display & enable add to cart btn
                                enableAddToCartBtn(response, addToCartLabel);
                            }
                            if (response.requiresPDFUpload === 'yes') {
                                // upload & not customizable - display upload block & block add to cart
                                disableAddToCartBtn();
                                displayUploadElements(response);
                            }
                        }
                        if (response.isCustomizable === 'yes') {
                            if (
                                response.requiresPDFUpload === 'no' ||
                                response.requiresPDFUpload === ''
                            ) {
                                // no upload & not customizable - display Imaxel link with custom add to cart btn
                                enableAddToCartBtn(
                                    response,
                                    response.imaxelData?.buttonText ??
                                        addToCartLabel
                                );
                            }
                            if (response.requiresPDFUpload === 'yes') {
                                // upload & customizable - display upload block & block Imaxel link with custom add to cart btn
                                enableAddToCartBtn(
                                    response,
                                    response.imaxelData?.buttonText ??
                                        addToCartLabel
                                );
                                disableAddToCartBtn();
                                displayUploadElements(response);
                            }
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

        // when showing a (new) variation, all previous elements need to be cleared or hidden
        function initRefreshVariantElements() {
            $('#ppi-loading').removeClass('ppi-hidden');
            // clear any old upload information
            $('#upload-info').html('');
            // hide the ever present max upload file size
            $('#max-upload-size').addClass('ppi-hidden');
            // disable add-to-cart btn
            $('.single_add_to_cart_button').addClass('ppi-disabled');
            $('.ppi-upload-form').addClass('ppi-hidden');
            $('.ppi-upload-parameters').addClass('ppi-hidden');
        }

        function displayUploadElements(response) {
            const { height, width, min_pages, max_pages, price_per_page } =
                response;
            $('#ppi-loading').addClass('ppi-hidden');

            $('.ppi-upload-form').removeClass('ppi-hidden');
            $('.upload-label').removeClass('upload-disabled');

            $('.ppi-upload-form').removeClass('ppi-hidden');
            $('#max-upload-size').removeClass('ppi-hidden');
            if (height != '') {
                $('#content-height').html(height + 'mm');
                $('#content-height').parent().removeClass('ppi-hidden');
            } else {
                $('#content-height').parent().addClass('ppi-hidden');
            }
            if (width != '') {
                $('#content-width').html(width + 'mm');
                $('#content-width').parent().removeClass('ppi-hidden');
            } else {
                $('#content-width').parent().addClass('ppi-hidden');
            }
            if (min_pages != '') {
                $('#content-min-pages').html(min_pages);
                $('#content-min-pages').parent().removeClass('ppi-hidden');
            } else {
                $('#content-min-pages').parent().addClass('ppi-hidden');
            }
            if (max_pages != '') {
                $('#content-max-pages').html(max_pages);
                $('#content-max-pages').parent().removeClass('ppi-hidden');
            } else {
                $('#content-max-pages').parent().addClass('ppi-hidden');
            }
            if (price_per_page != '') {
                $('#content-price-per-page').html(price_per_page);
                $('#content-price-per-page').parent().removeClass('ppi-hidden');
            } else {
                $('#content-price-per-page').parent().addClass('ppi-hidden');
            }
            $('.ppi-upload-parameters').removeClass('ppi-hidden');
        }

        function hideUploadElements() {
            $('.upload-label').addClass('upload-disabled');
            $('.upload-parameters').addClass('ppi-hidden');
        }

        function enableAddToCartBtn(response, addToCartLabel) {
            $('.single_add_to_cart_button').remove();
            $('#ppi-loading').addClass('ppi-hidden');
            $('.single_add_to_cart_button').removeClass('ppi-disabled');

            if (!response.customButton) {
                $('.quantity').after(
                    '<button type="submit" class="single_add_to_cart_button button alt">' +
                        addToCartLabel +
                        '</button>'
                );
            } else {
                $('.quantity').after(
                    "<a href='" +
                        response.imaxelData.url +
                        "' class='ppi-add-to-cart-button single_add_to_cart_button button alt'><span id='ppi-loading' class='ppi-hidden dashicons dashicons-update rotate'></span>" +
                        addToCartLabel +
                        '</a>'
                );
                $('#ppi-loading').addClass('ppi-hidden');
            }
        }

        function disableAddToCartBtn() {
            $('.single_add_to_cart_button').addClass('ppi-disabled');
        }

        function getSiteLanguage() {
            const cookies = document.cookie;
            const cookieArray = cookies.split(';');
            for (const cookie of cookieArray) {
                if (cookie.startsWith(' wp-wpml_current_language=')) {
                    return cookie.slice(-2);
                }
            }
            return 'en';
        }

        function setAddToCartLabel(language) {
            let addToCartLabel = 'Add to cart';

            switch (language) {
                case 'en':
                    addToCartLabel = 'Add to cart';
                    break;
                case 'nl':
                    addToCartLabel = 'Voeg toe aan winkelmand';
                    break;
                case 'fr':
                    addToCartLabel = 'Ajouter au panier';
                    break;
                case 'de':
                    addToCartLabel = 'In den Warenkorb legen';
                    break;
                case 'it':
                    addToCartLabel = 'Añadir a la cest';
                    break;
                case 'es':
                    addToCartLabel = 'Aggiungi al carrello';
                    break;
            }

            return addToCartLabel;
        }
    });
})(jQuery);
