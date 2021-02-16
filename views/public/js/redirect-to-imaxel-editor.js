jQuery(document).ready(function () {
    jQuery('#file-upload').on('change', e => {
        e.preventDefault();

        var formData = new FormData();
        formData.append('file', 'nope');
        formData.append('action', 'redirect_to_imaxel_editor');
        formData.append('_ajax_nonce', ppi_ajax_object.nonce);

        jQuery.ajax({
            url: ppi_ajax_redirect.ajax_url,
            method: 'POST',
            data: formData,
            cache: false,
            dataType: 'json',
            success: function (response) {
                if (response.type === 'success') {
                    console.log(response);
                } else {
                    alert('Something went wrong');
                }
            },
        });
    });
});
