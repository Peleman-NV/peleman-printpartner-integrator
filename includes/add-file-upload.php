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
function ppi_output_form($variant)
{
    //echo phpinfo();
    // grey out until a variant is chosen
    // once it's chosen, show the params div
    $form = '
        <div class="ppi-upload-form">
            <label for="file-upload">Click here to upload your PDF file</label>
            <input id="file-upload" type="file" accept="application/pdf" name="pdf_upload" style="display: none;">
        </div>
        <div class="upload-parameters hidden">
            <p>Maximum file upload size: 100MB</p>
            <p>PDF page height: 297mm</p>
            <p>PDF page width: 210mm</p>
            <p>Minumum nr of pages: 3</p>
            <p>Maximum nr of pages: 400</p>
        </div>
        <div id="file-upload-validation"></div>';
    echo $form;
}
add_action('ppi_file_upload_output_form', 'ppi_output_form', 7, 1);

/**
 * Change the maximum file upload size limit
 */
function filter_site_upload_size_limit($size)
{
    return 100 * 1024 * 1024;
}
add_filter('upload_size_limit', 'filter_site_upload_size_limit', 20);
