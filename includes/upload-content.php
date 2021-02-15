<?php

function upload_content_file()
{
    echo 'here I am!';
    check_ajax_referer('nonce_name');
    $response['custom'] = "Do something";
    $respone['success'] = true;

    $response = json_encode($response);
    echo $response;
    die();

    if (isset($_FILES['file']['name'])) {

        /* Getting file name */
        $filename = $_FILES['file']['name'];

        /* Location */
        $location = "upload/" . $filename;
        $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
        $imageFileType = strtolower($imageFileType);

        /* Valid extensions */
        $valid_extensions = array("jpg", "jpeg", "png");

        $response = 0;
        /* Check file extension */
        if (in_array(strtolower($imageFileType), $valid_extensions)) {
            /* Upload file */
            if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
                $response = $location;
            }
        }

        echo $response;
        exit;
    }
}
add_action('wp_ajax_upload_content_file', 'upload_content_file');
add_action('wp_ajax_nopriv_upload_content_file', 'upload_content_file');
