<?php

defined('ABSPATH') || exit;

global $product;

if (!$product->is_purchasable()) {
	return;
}
echo wc_get_stock_html($product); // WPCS: XSS ok.

$cartPrice = $product->get_meta('cart_price');
$cartUnits = $product->get_meta('cart_units');

if (isset($cartPrice) && !empty($cartPrice) && isset($cartUnits) && !empty($cartUnits) && $cartUnits > 1) {
	$inclVatText = __('Incl. VAT', PPI_TEXT_DOMAIN);
	$individualPriceLabel = __('Individual price', PPI_TEXT_DOMAIN);
	$unitPriceLabel = __('Purchase unit price', PPI_TEXT_DOMAIN);
	$individualPriceDiv = '<div class="woocommerce-variation-price">'
		. $individualPriceLabel
		. ' <span class="price">' . get_woocommerce_currency_symbol() . number_format($product->get_price(), 2) . ' ' . '<small class="woocommerce-price-suffix">'
		. $inclVatText
		. '</small></span></div>';
	$unitPriceDiv = '<div class="woocommerce-variation-price extra-margin">'
		. $unitPriceLabel
		. ' <span class="price">' . get_woocommerce_currency_symbol() . number_format($cartPrice, 2) . ' ' . '<small class="woocommerce-price-suffix">'
		. $inclVatText
		. ' (' . $cartUnits . ' pieces)</small></span><div>';

	echo $individualPriceDiv;
	echo $unitPriceDiv;
}

if ($product->is_in_stock()) : ?>

	<?php do_action('woocommerce_before_add_to_cart_form'); ?>

	<form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action('woocommerce_before_add_to_cart_button'); ?>

		<?php
		do_action('woocommerce_before_add_to_cart_quantity');

		woocommerce_quantity_input(
			array(
				'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
				'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
				'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
			)
		);

		do_action('woocommerce_after_add_to_cart_quantity');
		?>

		<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>

		<?php do_action('woocommerce_after_add_to_cart_button'); ?>
	</form>

	<?php do_action('woocommerce_after_add_to_cart_form'); ?>

<?php endif; ?>