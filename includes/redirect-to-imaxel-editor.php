<?php

require(plugin_dir_path(__FILE__) . 'imaxel-service.php');

/**
 * Redirects the user to the Imaxel editor
 */
function redirect_to_imaxel_editor()
{
    check_ajax_referer('editor-redirect-nonce', '_ajax_nonce');

    $variant_id = $_POST['variant_id'];
    $product_variation = wc_get_product($variant_id)->get_meta('template_id');

    $imaxel = new Imaxel_Service();
    $create_project_response = $imaxel->create_project($product_variation);

    $encoded_response = json_decode($create_project_response['body']);
    $new_project_id = $encoded_response->id;
    // save to DB 'n stuff
    $editor_url = $imaxel->get_editor_url($new_project_id, 'https://devshop.peleman.com', 'https://devshop.peleman.com/?add-to-cart=' . $variant_id);

    $response['url'] = $editor_url;
    $response['type'] = 'success';

    wp_send_json($response);
    wp_die();
}
add_action('wp_ajax_redirect_to_imaxel_editor', 'redirect_to_imaxel_editor', 1);
add_action('wp_ajax_nopriv_redirect_to_imaxel_editor', 'redirect_to_imaxel_editor', 1);

/**
 * Adds a custom hook
 */
function ppi_add_to_cart_url()
{
    do_action('ppi_generate_add_to_cart_url');
}

/**
 * Outputs the link necessary to redirect a user to the Imaxel editor
 */
function ppi_output_add_to_cart_url()
{
    $url = 'http://www.yahoo.com';

    return $url;
}
add_action('ppi_generate_add_to_cart_url', 'ppi_output_add_to_cart_url', 10);
