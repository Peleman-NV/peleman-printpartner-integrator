jQuery(document).ready(function () {
    //jQuery('table.variations tr:last').remove(); //removes last empty table row in variations table
    console.log('hier');

    // https://www.youtube.com/watch?v=risgfTMYEIM&feature=emb_title
    // upload and check file
    jQuery('#file-upload').on('change', () => {
        jQuery('#file-upload-validation').html('');
        jQuery('#file-upload-validation').html('<p>Uploading</p>');
        var fd = new FormData();
        var files = jQuery('#file-upload')[0].files;
        fd.append('file', files[0]);

        jQuery.ajax({
            type: 'POST',
            url: ppi_file_upload.ajax_url,
            data: {
                action: 'upload_content_file',
                _ajax_nonce: ppi_file_upload.nonce,
                file: fd,
            },
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.type === 'success') {
                    jQuery('#file-upload-validation').html('<p>Uploaded</p>');
                } else {
                    alert('file not uploaded');
                }
            },
        });
    });
});
