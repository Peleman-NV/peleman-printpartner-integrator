jQuery(document).ready(function () {
    jQuery('#file-upload').on('change', (e) => {
        jQuery('#file-upload').submit();
        e.preventDefault();
        var fileInput = document.getElementById('file-upload');
        var file = fileInput.files[0];

        var formData = new FormData();
        formData.append('file', file);

        var data = {
            action: 'upload_content_file',
            nonce: ppi_ajax_object.nonce,
            file: formData,
        };

        // worth a try...
        // https://gist.github.com/ahmadawais/0ccb8a32ea795ffac4adfae84797c19a

        jQuery.ajax({
            method: 'POST',
            url: ppi_ajax_object.ajax_url,
            // url: ppi_ajax_object.ajax_url + '?action=upload_content_file', // only this works - no POST vars coming through...
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json',
            cache: false,
            success: function (response) {
                console.log(response);
                if (response.type == 'success') {
                    alert('success!');
                } else {
                    alert('file not uploaded');
                }
            },
        });
    });
});
