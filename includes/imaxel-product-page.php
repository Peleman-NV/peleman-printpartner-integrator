<?php

/**
 * Override the Woocommerce templates with the plugin templates
 *
 * @param string $template      Default template file path.
 * @param string $template_name Template file slug.
 * @param string $template_path Template file name.
 *
 * @return string The new Template file path.
 */
function ppi_override_wc_templates($template, $template_name, $template_path)
{
    if ('variable.php' === basename($template)) {
        $template = trailingslashit(plugin_dir_path(__FILE__)) . '../woocommerce/single-product/add-to-cart/variable.php';
    }
    if ('variation-add-to-cart-button.php' === basename($template)) {
        $template = trailingslashit(plugin_dir_path(__FILE__)) . '../woocommerce/single-product/add-to-cart/variation-add-to-cart-button.php';
    }
    if ('variation.php' === basename($template)) {
        $template = trailingslashit(plugin_dir_path(__FILE__)) . '../woocommerce/single-product/add-to-cart/variation.php';
    }

    return $template;
}
add_filter('woocommerce_locate_template', 'ppi_override_wc_templates', 10, 3);

/**
 * Change add to cart text for Imaxel products depending on the metadata
 */
function ppi_change_add_to_cart_text_for_imaxel_products()
{
    global $product;

    $product_id = $product->get_id();
    $parent_wc_product = wc_get_product($product_id);
    if ($parent_wc_product->is_type('variable')) {
        $variants_array = $parent_wc_product->get_children();
        $first_variant = wc_get_product($variants_array[0]);
        $requires_pdf = wc_get_product($first_variant)->get_meta('pdf_upload');
        $is_imaxel_product = wc_get_product($first_variant)->get_meta('template_id');

        if ($requires_pdf != "") {
            add_filter('woocommerce_product_single_add_to_cart_text', 'ppi_change_add_to_cart_text_for_imaxel_pdf_product', 10, 2);
            //echo '<form action="upload-content.php" enctype="multipart/form-data"><input type="file" onchange="this.form.submit()" name="file-upload"/></form>';
        } else if ($is_imaxel_product != "") {
            add_filter('woocommerce_product_single_add_to_cart_text', 'ppi_change_add_to_cart_text_for_imaxel_product', 10, 2);
        }
    }
}
add_action('woocommerce_single_variation', 'ppi_change_add_to_cart_text_for_imaxel_products');

/**
 * Changes Add to cart button text for Imaxel products requiring a PDF content file
 */
function ppi_change_add_to_cart_text_for_imaxel_pdf_product()
{
    return __('Upload PDF and create project', 'woocommerce');
}

/**
 * Changes Add to cart button text for Imaxel products
 */
function ppi_change_add_to_cart_text_for_imaxel_product()
{
    return __('Create project', 'woocommerce');
}

/**
 * UNUSED Adds a form with file input to the product page
 */
function add_file_input($variation_data, $product, $variation)
{
    $variation_id = $variation_data['variation_id'];
    $wc_product = wc_get_product($variation_id);
    $requires_pdf = $wc_product->get_meta('pdf_upload');

    if ($requires_pdf != '') {
        $variation_data['availability_html'] .= '<form action="upload-content.php"><input type="file"></form>';
    }
    return $variation_data;
}
//add_filter('woocommerce_available_variation', 'add_file_input', 10, 3);


function my_custom_validation($true,  $product_id,  $quantity)
{
    //     wc_add_notice(__('Please enter an amount greater than 1', 'cfwc'), 'error');

    //     return 0;
}
//add_action('woocommerce_add_to_cart_validation', 'my_custom_validation', 10, 3);


/**
 * UNUSED Enables or disables the Add to cart button
 *
 * @param Bool $is_purchasable
 * @param Object $product
 * @return Bool
 */
function enable_add_to_cart_button($is_purchasable, $product)
{
    print_r($product, true);
    // check if file is uploaded
    return 0;
}
//add_filter('woocommerce_is_purchasable', 'enable_add_to_cart_button', 10, 2);
