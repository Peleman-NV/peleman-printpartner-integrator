<?php


function upload_content_file()
{
    check_ajax_referer('file_upload_nonce', '_ajax_nonce');
    $filename = $_FILES['file']['name'];
    $response['type'] = "success";
    $response['action'] = "Uploaded " . $filename;
    $pages = 73;
    $format = "A4";

    //$im = new Imagick($filename);
    //$pages = $im->getNumberImages();
    // $pdf->setSourceFile($filename);
    // $pages = $pdf->AliasNbPages();

    $response['file']['name'] = $filename;
    $response['file']['pages'] = $pages;
    $response['file']['format'] = $format;

    wp_send_json($response);
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
