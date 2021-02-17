<?php

require(plugin_dir_path(__FILE__) . 'imaxel-service.php');

/**
 * Redirects the user to the Imaxel editor
 */
function redirect_to_imaxel_editor()
{
    check_ajax_referer('editor-redirect-nonce', '_ajax_nonce');

    $variant_id = $_POST['variant_id'];
    $product_variation_array = explode(',', wc_get_product($variant_id)->get_meta('template_id'));
    $product_variation_data = array();

    // TODO refactor when backend is responsive
    for ($i = 0; $i < count($product_variation_array); $i++) {
        if ($i == 0) {
            $product_variation_data['template_id'] = $product_variation_array[$i];
            continue;
        }
        $product_variation_data['variant_' . $i] = $product_variation_array[$i];
    }

    $imaxel = new Imaxel_Service();
    $create_project_response = $imaxel->create_project($product_variation_data['template_id'], $product_variation_data['variant_1']);

    $encoded_response = json_decode($create_project_response['body']);
    $new_project_id = $encoded_response->id;

    $user_id = wp_get_current_user();
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
