<?php

//require(plugin_dir_path(__FILE__) . '../lib/pdfparser/vendor/autoload.php');

function upload_content_file()
{
    check_ajax_referer('file_upload_nonce', '_ajax_nonce');

    if ($_FILES['file']['error']) {
        $response['error'] = $_FILES['file']['error'];
        $response['message'] = "Error encountered while uploading your file.  Please try again with a different one.";
        return_response($response);
    }

    $max_file_upload_size = (int)(ini_get('upload_max_filesize')) * 1024 * 1024;
    if ($_FILES['file']['size'] >= $max_file_upload_size) {
        $response['size'] = $_FILES['file']['size'];
        $response['max_size'] = $max_file_upload_size;
        $response['message'] = "Your file is too large, Please upload a file smaller than 100MB.";
        return_response($response);
    }

    $filename = $_FILES['file']['name'];

    $uploadsDir = wp_upload_dir();
    $uploadlocation = realpath($uploadsDir['basedir'] . '/ppi/content');
    if (!is_dir($uploadlocation)) {
        $response['message'] = "Upload directory not found.  Your file cannot be processed.";
        return_response($response);
    }

    $file_type = pathinfo($uploadlocation  . '\\' . $filename, PATHINFO_EXTENSION);
    $file_type = strtolower($file_type);
    if ($file_type != 'pdf') {
        $response['type'] = $file_type;
        $response['message'] = "Please upload a PDF file.";
        return_response($response);
    };

    // TODO pages and size validation
    $pages =  rand(5, 80);
    $format = "A4";
    $response['file']['name'] = $filename;
    $response['file']['location'] = $uploadlocation  . '\\' . $filename;
    $response['file']['format'] = $format;

    $new_filename = "project_id_" . guid() . '.pdf';
    move_uploaded_file($_FILES['file']['tmp_name'], $uploadlocation  . '\\' . $new_filename);
    $response['message'] = "Successfully uploaded \"" . $filename . "\" (" . $format . ", " . $pages . " pages).";

    $response['file']['pages'] = $pages;

    return_response($response);
}
add_action('wp_ajax_upload_content_file', 'upload_content_file', 1);
add_action('wp_ajax_nopriv_upload_content_file', 'upload_content_file', 1);

function return_response($response)
{
    wp_send_json($response);
    wp_die();
}

function guid()
{
    mt_srand((float)microtime() * 10000); //optional for php 4.2.0 and up.
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = chr(45);
    $uuid =
        substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12);
    return $uuid;
}
