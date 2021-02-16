jQuery(document).ready(function () {
    jQuery('.ppi-add-to-cart-button').on('click', (e) => {
        e.preventDefault();
        if (jQuery('.ppi-add-to-cart-button').hasClass('disabled')) {
            return;
        }

        const data = {
            action: 'redirect_to_imaxel_editor',
            variant_id: jQuery("[name='variation_id']").val(),
            _ajax_nonce: ppi_ajax_redirect.nonce,
        };

        jQuery.ajax({
            url: ppi_ajax_redirect.ajax_url,
            method: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            success: function (response) {
                if (response.type === 'success') {
                    console.log(response);
                } else {
                    console.error('Something went wrong: ' + response);
                }
            },
        });
    });
});
