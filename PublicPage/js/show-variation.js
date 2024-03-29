/**
 * This script is responsible the custom display of Peleman products.  This is either
 *      displaying the additional attributes that Peleman products require
 *              OR
 *      displaying the custom Add to cart text
 *
 * If a product requires a content file, the add to cart button is disabled and
 * an upload form with parameters is displayed.
 * 'upload-content.js' fires on the change event of that form,
 * and performs an AJAX call to the PHP function "upload_content_file"
 * in /PublicPage/PpiProductPage.php, where the file is validated
 * and uploaded to the server on success.
 * A response is then returned and displayed.
 * On success, the "add to cart" button is enabled.
 * On error, a message is displayed.
 *
 * If no content is required, the custom add to cart buton is displayed and enabled.
 *
 * The upload button's colour is also set, depending on the URL.
 *
 * An additional aspect is displaying the per piece and per unit prices for each variable
 */

(function ($) {
    ('use strict');
    $(function () {
        let buttonText = setAddToCartLabel();
        // Event: when a variation is selected
        $(document).on('show_variation', (event, variation) => {
            initRefreshVariantElements();
            getProductVariationData(variation.variation_id);
        });

        // Event: when a new variation is chosen
        $(document).on('hide_variation', e => {
            hideUploadElements();
            disableAddToCartBtn(buttonText);
            resetUnitPrice();
            hideArticleCodeElement();
        });

        function getProductVariationData(variationId) {
            const data = {
                variant: variationId,
                action: 'get_product_variation_data',
                //_ajax_nonce: ppi_product_variation_information_object.nonce,
            };
            let fallbackAddToCartLabel = setAddToCartLabel();

            $.ajax({
                url: ppi_product_variation_information_object.ajax_url,
                method: 'GET',
                data: data,
                cache: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        if (response.callUsToOrder) {
                            showCallUsTextAndButton();
                        } else {
                            showUnitPrice(response.bundleObject);
                            buttonText =
                                response.buttonText ?? fallbackAddToCartLabel;
                            if (response.f2dArtCode) {
                                displayArticleCode(response.f2dArtCode);
                            }
                            if (
                                response.isCustomizable === 'no' ||
                                response.isCustomizable === ''
                            ) {
                                /**
                                 * no upload & not customizable:
                                 * show custom add to cart button text and enable
                                 */
                                if (
                                    response.requiresPDFUpload === 'no' ||
                                    response.requiresPDFUpload === ''
                                ) {
                                    enableAddToCartBtn(buttonText);
                                }
                                /**
                                 * upload & not customizable:
                                 * display upload block
                                 * show custom add to cart button text and disable
                                 */
                                if (response.requiresPDFUpload === 'yes') {
                                    disableAddToCartBtn(buttonText);
                                    displayUploadElements(response);
                                }
                            }
                            if (response.isCustomizable === 'yes') {
                                /**
                                 * no upload & customizable:
                                 * show custom add to cart button text and enable
                                 */
                                if (
                                    response.requiresPDFUpload === 'no' ||
                                    response.requiresPDFUpload === ''
                                ) {
                                    enableAddToCartBtn(buttonText);
                                }
                                /**
                                 * upload & customizable:
                                 * display upload block
                                 * show custom add to cart button text and disable
                                 */
                                if (response.requiresPDFUpload === 'yes') {
                                    disableAddToCartBtn(buttonText);
                                    displayUploadElements(response);
                                }
                            }
                            /**
                             * If the variant isn't in stock, all the above doesn't matter
                             * and the add to cart & upload buttons should be disabled
                             */
                            if (!response.inStock) {
                                disableAddToCartBtn(buttonText);
                                $('.upload-label').addClass('ppi-disabled');
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
            // display loading animation
            $('#ppi-loading').removeClass('ppi-hidden');
            // clear any old upload information
            $('#upload-info').html('');
            // hide the ever present max upload file size
            $('#max-upload-size').addClass('ppi-hidden');
            // disable add-to-cart btn
            $('.single_add_to_cart_button').addClass('ppi-disabled');
            // hide upload button
            $('.ppi-upload-form').addClass('ppi-hidden');
            // hide upload parameters block
            $('.ppi-upload-parameters').addClass('ppi-hidden');

            $('.single_variation_wrap').show();
            $('.summary p.price').show();
            $('.add-to-cart-price').show();
            $('p').remove('#call-us');
            $('#call-us-btn').addClass('ppi-hidden');
            $('#call-us-price').addClass('ppi-hidden');

            // article code
            $('span.article-code-container').addClass('ppi-hidden');
        }

        function displayArticleCode(articleCode) {
            $('.article-code').remove();
            $('span.label.article-code-label').after(
                '<span class="article-code">' + articleCode + '</span>'
            );
            $('span.article-code-container').removeClass('ppi-hidden');
        }

        /**
         * Function displays the necessary parameters, when present
         */
        function displayUploadElements(response) {
            $('.upload-label').removeClass('ppi-disabled');
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

        /**
         * Function hides upload parameters,
         * because a  new variant may not have upload parameters
         */
        function hideUploadElements() {
            $('.upload-label').addClass('upload-disabled');
            $('.upload-parameters').addClass('ppi-hidden');
        }

        function enableAddToCartBtn(addToCartLabel) {
            $('.single_add_to_cart_button').removeClass('ppi-disabled');
            $('.single_add_to_cart_button').html(
                '<span id="ppi-loading" class="dashicons dashicons-update rotate ppi-hidden"></span>' +
                    addToCartLabel
            );
        }

        function disableAddToCartBtn(addToCartLabel) {
            $('.single_add_to_cart_button').addClass('ppi-disabled');
            $('.single_add_to_cart_button').html(
                '<span id="ppi-loading" class="dashicons dashicons-update rotate ppi-hidden"></span>' +
                    addToCartLabel
            );
        }

        function setAddToCartLabel() {
            const language = getSiteLanguage();
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
                    addToCartLabel = 'Aggiungi al carrello';
                    break;
                case 'es':
                    addToCartLabel = 'Añadir al carrito';
                    break;
            }

            return addToCartLabel;
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

        function showCallUsTextAndButton() {
            $('.single_variation_wrap').hide();
            $('.summary p.price').hide();
            $('.add-to-cart-price').hide();
            $('#call-us-btn').removeClass('ppi-hidden');
            $('#call-us-price').removeClass('ppi-hidden');
            $('.summary h1.product_title.entry-title').after(
                '<p id="call-us" class="price"><span class="woocommerce-Price-amount amount">Call us for a quote at +32 3 889 32 41<span></p>'
            );
        }

        function showUnitPrice(bundleObject) {
            const {
                bundlePriceExists,
                bundlePriceWithCurrencySymbol,
                priceSuffix,
                bundleSuffix,
                individualPriceWithCurrencySymbol,
            } = bundleObject;

            // hide individual price
            $('.individual-price').addClass('ppi-hidden');
            if (!bundlePriceExists) {
                $('.add-to-cart-price span.price-amount').html(
                    individualPriceWithCurrencySymbol
                );
                $('.add-to-cart-price span.woocommerce-price-suffix').html(
                    priceSuffix
                );
            } else {
                $('.individual-price').removeClass('ppi-hidden');
                $('.add-to-cart-price span.price-amount').html(
                    bundlePriceWithCurrencySymbol
                );
                $('.add-to-cart-price span.woocommerce-price-suffix').html(
                    priceSuffix +
                        '<span class="bundle-suffix">' +
                        bundleSuffix +
                        '</span>'
                );

                $('.individual-price span.price-amount').html(
                    individualPriceWithCurrencySymbol
                );
                $('.individual-price span.woocommerce-price-suffix').html(
                    priceSuffix
                );
            }
        }

        function resetUnitPrice() {
            $('.cart-unit-block').addClass('ppi-hidden');
            $('.individual-price-text').html();
        }

        function hideArticleCodeElement() {
            $('span.article-code-container').addClass('ppi-hidden');
        }
    });
})(jQuery);
