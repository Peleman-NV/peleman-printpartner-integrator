jQuery(document).ready(function () {
    jQuery('#file-upload').on('change', (e) => {
        jQuery('#file-upload').submit();
        e.preventDefault();
        jQuery('#file-upload-validation').html('Uploading . . .');
        var fileInput = document.getElementById('file-upload');
        var file = fileInput.files[0];

        var formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'upload_content_file');
        formData.append('_ajax_nonce', ppi_ajax_object.nonce);

        jQuery.ajax({
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
                jQuery('#file-upload-validation').html(response.message);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log({ jqXHR });
                jQuery('#file-upload-validation').html(
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
    });
});
