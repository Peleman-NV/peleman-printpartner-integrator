<?php

defined('ABSPATH') || exit;

global $product;
?>

<div class="woocommerce-variation-add-to-cart variations_button">

	<?php
	$wc_product = wc_get_product($product->get_id());
	do_action('ppi_file_upload_output_form');
	do_action('ppi_upload_information_div');

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
	echo _e('<button type="submit" class="ppi-add-to-cart-button single_add_to_cart_button button alt"><span id="ppi-loading" class="ppi-hidden dashicons dashicons-update rotate"></span>' . esc_html($product->single_add_to_cart_text()) . '</button>');

	do_action('woocommerce_after_add_to_cart_button');
	do_action('ppi_redirection_info_div');
	?>
	<input type="hidden" name="add-to-cart" value="<?php echo _e(absint($product->get_id())); ?>" />
	<input type="hidden" name="product_id" value="<?php echo _e(absint($product->get_id())); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>