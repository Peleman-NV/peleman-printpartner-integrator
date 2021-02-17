<?php

/**
 * Adds text inputs for the Imaxel template ID and PDF upload information to variable products
 * 
 * @param Int       $loop An interator to give each input field a unique name
 * @param Array     $variation_data Information about the specific variation
 * @param WP_Post   $variation Information about the product variation
 */
function ppi_add_custom_fields_to_variable_products($loop, $variation_data, $variation)
{
    echo '<div class="ppi-options-group"><h2 class="ppi-options-group-title">Fly2Data Properties</h2>';

    woocommerce_wp_text_input(array(
        'id' => 'template_id[' . $loop . ']',
        'placeholder' => 'Imaxel template ID',
        'class' => 'short',
        'label' => '<a href="https://services.imaxel.com/peleman/admin">Template ID</a>',
        'type' => 'text',
        'desc_tip'    => true,
        'description' => __('TemplateID<br>E.g. M002<br>Leave empty for no customisation', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'template_id', true)
    ));

    woocommerce_wp_text_input(array(
        'id' => 'variant_id[' . $loop . ']',
        'placeholder' => 'Variant code',
        'class' => 'short',
        'label' => '<a href="https://services.imaxel.com/peleman/admin">Variant code</a>',
        'type' => 'text',
        'desc_tip'    => true,
        'description' => __('Variant code<br>E.g. 00201<br>Leave empty for no customisation', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'variant_code', true)
    ));

    $pdf_upload_required = get_post_meta($variation->ID, 'pdf_upload_required', true);
    $pdf_fields_readonly = $pdf_upload_required == "no" || empty($pdf_upload_required) ? array('readonly' => 'readonly') : '';

    woocommerce_wp_checkbox(array(
        'id' => 'pdf_upload_required[' . $loop . ']',
        'label'       => __('PDF content required?', 'woocommerce'),
        'description' => __('Select to require a PDF upload', 'woocommerce'),
        'desc_tip'    => true,
        'value' => $pdf_upload_required,
    ));

    woocommerce_wp_text_input(array(
        'id' => 'pdf_width_mm[' . $loop . ']',
        'class' => 'short',
        'label' => 'Page width (mm)',
        'type' => 'number',
        'desc_tip'    => true,
        'description' => __('PDF page width in MM', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'pdf_width_mm', true),
        'custom_attributes' => $pdf_fields_readonly,
    ));

    woocommerce_wp_text_input(array(
        'id' => 'pdf_height_mm[' . $loop . ']',
        'class' => 'short',
        'label' => 'Page height (mm)',
        'type' => 'number',
        'desc_tip'    => true,
        'description' => __('PDF page height in MM', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'pdf_height_mm', true),
        'custom_attributes' => $pdf_fields_readonly,
    ));

    woocommerce_wp_text_input(array(
        'id' => 'pdf_min_pages[' . $loop . ']',
        'class' => 'short',
        'label' => 'Minimum number of pages',
        'type' => 'number',
        'desc_tip'    => true,
        'description' => __('Minimum number of pages in the PDF content file', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'pdf_min_pages', true),
        'custom_attributes' => $pdf_fields_readonly,
    ));

    woocommerce_wp_text_input(array(
        'id' => 'pdf_max_pages[' . $loop . ']',
        'class' => 'short',
        'label' => 'Maximum number of pages',
        'type' => 'number',
        'desc_tip'    => true,
        'description' => __('Maximum number of pages in the PDF content file', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'pdf_max_pages', true),
        'custom_attributes' => $pdf_fields_readonly,
    ));
    echo '</div>';
}
add_action('woocommerce_product_after_variable_attributes', 'ppi_add_custom_fields_to_variable_products', 11, 3);

/**
 * Persists custom input fields
 * 
 * @param Int  $loop An interator to give each input field a unique name
 * @param Int  $variation_id Id for the current variation
 */
function ppi_persist_custom_field_variations($variation_id, $i)
{
    $template_id = $_POST['template_id'][$i];
    $variant_code = $_POST['variant_code'][$i];
    //$pdf_upload_required = $_POST['pdf_upload_required'][$i];
    $pdf_upload_required = isset($_POST['pdf_upload_required']) ? 'yes' : 'no';

    $pdf_width_mm = $_POST['pdf_width_mm'][$i];
    $pdf_height_mm = $_POST['pdf_height_mm'][$i];
    $pdf_min_pages = $_POST['pdf_min_pages'][$i];
    $pdf_max_pages = $_POST['pdf_max_pages'][$i];


    if (isset($template_id)) update_post_meta($variation_id, 'template_id', esc_attr($template_id));
    if (isset($variant_code)) update_post_meta($variation_id, 'variant_code', esc_attr($variant_code));

    //if (isset($pdf_upload_required)) update_post_meta($variation_id, 'pdf_upload_required', esc_attr($pdf_upload_required));
    if (isset($pdf_upload_required)) update_post_meta($variation_id, 'pdf_upload_required', $pdf_upload_required);
    if (isset($pdf_width_mm)) update_post_meta($variation_id, 'pdf_width_mm', esc_attr($pdf_width_mm));
    if (isset($pdf_height_mm)) update_post_meta($variation_id, 'pdf_height_mm', esc_attr($pdf_height_mm));
    if (isset($pdf_min_pages)) update_post_meta($variation_id, 'pdf_min_pages', esc_attr($pdf_min_pages));
    if (isset($pdf_max_pages)) update_post_meta($variation_id, 'pdf_max_pages', esc_attr($pdf_max_pages));
}
add_action('woocommerce_save_product_variation', 'ppi_persist_custom_field_variations', 10, 2);

/**
 * Returns custom product variation data
 * Unused as of yet
 * 
 * @return Array   $variations An array of the product variation data
 */
function ppi_add_custom_field_variation_data_to_front_end($variations)
{
    $variations['template_id'] = get_post_meta($variations['variation_id'], 'template_id', true);
    $variations['variant_code'] = get_post_meta($variations['variation_id'], 'variant_code', true);
    $variations['pdf_upload_required'] = get_post_meta($variations['variation_id'], 'pdf_upload_required', true);
    $variations['pdf_width_mm'] = get_post_meta($variations['variation_id'], 'pdf_width_mm', true);
    $variations['pdf_height_mm'] = get_post_meta($variations['variation_id'], 'pdf_height_mm', true);
    $variations['pdf_min_pages'] = get_post_meta($variations['variation_id'], 'pdf_min_pages', true);
    $variations['pdf_max_pages'] = get_post_meta($variations['variation_id'], 'pdf_max_pages', true);

    return $variations;
}
add_filter('woocommerce_available_variation', 'ppi_add_custom_field_variation_data_to_front_end');
