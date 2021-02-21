<?php

require(plugin_dir_path(__FILE__) . 'imaxel-service.php');

/**
 * Redirects the user to the Imaxel editor
 */
function redirect_to_imaxel_editor()
{
    check_ajax_referer('editor-redirect-nonce', '_ajax_nonce');

    $variant_id = $_POST['variant_id'];
    $template_id =  wc_get_product($variant_id)->get_meta('template_id');
    $variant_code = wc_get_product($variant_id)->get_meta('variant_code');

    $imaxel = new Imaxel_Service();
    $create_project_response = $imaxel->create_project($template_id, $variant_code);

    $encoded_response = json_decode($create_project_response['body']);
    $new_project_id = $encoded_response->id;

    $user_id = get_current_user_id();
    // $now =  new DateTime('NOW');
    // error_log($now->format('c') . wp_get_current_user() . PHP_EOL, 3, __DIR__ . '/Log.txt');

    insert_project($user_id, $new_project_id, $variant_id);

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

/**
 * Inserts project into database
 *
 * @param Int $user_id
 * @param Int $project_id
 * @param Int $product_id
 */
function insert_project($user_id, $project_id, $product_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppi_user_projects';
    $wpdb->insert($table_name, array('user_id' => $user_id, 'project_id' => $project_id, 'product_id' => $product_id));
}
