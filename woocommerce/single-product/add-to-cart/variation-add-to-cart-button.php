<?php

/**
 * Single variation cart button
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

global $product;
?>

<div class="woocommerce-variation-add-to-cart variations_button">

	<?php
	$parent_wc_product = wc_get_product($product->get_id());
	if ($parent_wc_product->is_type('variable')) {
		$variants_array = $parent_wc_product->get_children();
		$first_variant = wc_get_product($variants_array[0]);
		if (wc_get_product($first_variant)->get_meta('pdf_upload') != "") {
			ppi_add_form_to_page_hook();
		}
	}
	do_action('woocommerce_before_add_to_cart_button');
	do_action('woocommerce_before_add_to_cart_quantity');
	woocommerce_quantity_input(
		array(
			'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
			'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
			'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
		)
	);
	do_action('woocommerce_after_add_to_cart_quantity');

	if (wc_get_product($first_variant)->get_meta('template_id') == "") {
		echo '<button type="submit" class="single_add_to_cart_button button alt">' . esc_html($product->single_add_to_cart_text()) . '</button>';
	} else {
		echo '<a class="ppi-add-to-cart-button single_add_to_cart_button button alt">' .  esc_html($product->single_add_to_cart_text()) . '</a>';
	}

	do_action('woocommerce_after_add_to_cart_button');
	?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>