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
        'label' => '<a href="https://printspot.io/master-peleman/admin/">Template ID</a>',
        'type' => 'text',
        'desc_tip'    => true,
        'description' => __('TemplateID,VariantID<br>E.g. M002,00201<br>Leave empty for no customisation', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'template_id', true)
    ));

    woocommerce_wp_text_input(array(
        'id' => 'pdf_upload[' . $loop . ']',
        'placeholder' => 'PDF upload',
        'class' => 'short',
        'label' => 'PDF upload',
        'type' => 'text',
        'desc_tip'    => true,
        'description' => __('WidthMM,HeightMM,MinPages,MaxPages,PricePerPage<br>E.g. 200,300,10,400,0.08<br>Leave empty for no PDF upload', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'pdf_upload', true)
    ));
    echo '</div>';
}
add_action('woocommerce_product_after_variable_attributes', 'ppi_add_custom_fields_to_variable_products', 11, 3);

/**
 * Persists 2 custom input fields
 * 
 * @param Int  $loop An interator to give each input field a unique name
 * @param Int  $variation_id Id for the current variation
 */
function ppi_persist_custom_field_variations($variation_id, $i)
{
    $template_id = $_POST['template_id'][$i];
    $pdf_upload = $_POST['pdf_upload'][$i];

    if (isset($template_id)) update_post_meta($variation_id, 'template_id', esc_attr($template_id));
    if (isset($pdf_upload)) update_post_meta($variation_id, 'pdf_upload', esc_attr($pdf_upload));
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
    $variations['pdf_upload'] = get_post_meta($variations['variation_id'], 'pdf_upload', true);

    return $variations;
}
add_filter('woocommerce_available_variation', 'ppi_add_custom_field_variation_data_to_front_end');
