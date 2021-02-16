<?php

/**
 * Adds a custom hook
 */
function ppi_add_form_to_page_hook()
{
    do_action('ppi_file_upload_output_form');
}

/**
 * Outputs a form with a file upload
 */
function ppi_output_form()
{

    $form = '
        <div class="ppi-upload-form">
            <label for="file-upload">Click here to upload your PDF file</label>
            <input id="file-upload" type="file" name="pdf_upload" style="display: none;">
        </div>
        <div id="file-upload-validation"></div>';

    echo $form;
}
add_action('ppi_file_upload_output_form', 'ppi_output_form', 7, 1);
