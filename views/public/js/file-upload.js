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
                if (response.type === 'success') {
                    const file = response.file;
                    jQuery('#file-upload-validation').html(
                        'Succesfully uploaded file ' +
                            file.name +
                            ' (' +
                            file.format +
                            ', ' +
                            file.pages +
                            ' pages).'
                    );
                } else {
                    alert('file not uploaded');
                }
            },
        });
    });
});
