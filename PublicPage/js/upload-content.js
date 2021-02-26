(function ($) {
    'use strict';

    /**
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(function () {
        $('.variations_form').on('show_variation', e => {
            $('.upload-label').removeClass('upload-disabled');
            $('.upload-parameters').removeClass('hidden');

            $('.single_add_to_cart_button').addClass('ppi-disabled');
        });

        $('.variations_form').on('hide_variation', e => {
            $('.upload-label').addClass('upload-disabled');
            $('.upload-parameters').addClass('hidden');
        });

        $('#file-upload').on('change', e => {
            $('#file-upload').submit();
            e.preventDefault();
            $('#file-upload-validation').html('Uploading . . .');
            var fileInput = document.getElementById('file-upload');
            var file = fileInput.files[0];

            var formData = new FormData();
            formData.append('action', 'upload_content_file');
            formData.append('file', file);
            formData.append('variant_id', $("[name='variation_id']").val());
            formData.append('_ajax_nonce', ppi_ajax_object.nonce);

            $.ajax({
                url: ppi_ajax_object.ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                enctype: 'multipart/form-data',
                cache: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    $('#file-upload-validation').html(response.message);
                    if (response.status === 'success') {
                        $('.single_add_to_cart_button').removeClass(
                            'ppi-disabled'
                        );
                        $('.single_add_to_cart_button').prop(
                            'href',
                            response.url
                        );
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log({ jqXHR });
                    $('#file-upload-validation').html(
                        'Something went wrong.  Please try again with a different file.'
                    );
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
