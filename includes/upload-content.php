<?php

// worth a try...
// https://gist.github.com/ahmadawais/0ccb8a32ea795ffac4adfae84797c19a

function upload_content_file($x)
{
    error_log(print_r($x, true), 3, __DIR__ . '/log.txt');
    //check_ajax_referer('file_upload_nonce', '_ajax_nonce');
    $response['method'] = $_SERVER['REQUEST_METHOD'];
    $response['file'] = $_FILES['file']['name'];
    $response['nonce'] = $_POST['_ajax_nonce'];
    $response['custom'] = "Do something";
    $response['type'] = "success";

    $response['string'] = "";
    foreach ($_POST as $var) {
        $response['string'] .= $var . ' ';
    }

    error_log(print_r($response, true), 3, __DIR__ . '/log.txt');

    $response = json_encode($response);
    echo wp_send_json($response);
    wp_die();

    // if (isset($_FILES['file']['name'])) {

    //     /* Getting file name */
    //     $filename = $_FILES['file']['name'];

    //     /* Location */
    //     $location = "upload/" . $filename;
    //     $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
    //     $imageFileType = strtolower($imageFileType);

    //     /* Valid extensions */
    //     $valid_extensions = array("jpg", "jpeg", "png");

    //     $response = 0;
    //     /* Check file extension */
    //     if (in_array(strtolower($imageFileType), $valid_extensions)) {
    //         /* Upload file */
    //         if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
    //             $response = $location;
    //         }
    //     }

    //     echo $response;
    wp_die();
    //}
}
add_action('wp_ajax_upload_content_file', 'upload_content_file', 1);
add_action('wp_ajax_nopriv_upload_content_file', 'upload_content_file', 1);
