<?php

require(plugin_dir_path(__FILE__) . 'imaxel-service.php');

/**
 * Redirects the user to the Imaxel editor
 */
function redirect_to_imaxel_editor()
{
    check_ajax_referer('editor-redirect-nonce', '_ajax_nonce');

    $imaxel = new Imaxel_Service();
    $create_project_response = $imaxel->create_project( /* product stuff */);
    // save to DB
    $editor_url = $imaxel->get_editor_url( /* crPr response stuff */);
    $response['type'] = 'success';
    $response['data'] = 'some stuff';

    wp_send_json($response);
}
add_action('wp_ajax_redirect_to_imaxel_editor', 'redirect_to_imaxel_editor', 1);
add_action('wp_ajax_nopriv_redirect_to_imaxel_editor', 'redirect_to_imaxel_editor', 1);
