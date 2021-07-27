<?php

defined('ABSPATH') || exit;
global $product;

?>
<script type="text/template" id="tmpl-variation-template">
	<div class="woocommerce-variation-description">{{{ data.variation.variation_description }}}</div>
	<div class="woocommerce-variation-price"><?php esc_html_e('Individual price', PPI_TEXT_DOMAIN); ?>  {{{ data.variation.price_html }}}</div>
	
	<div class="woocommerce-variation-price extra-margin cart-unit-block">
	<?php esc_html_e('Purchase unit price', PPI_TEXT_DOMAIN); ?>
		<span class="price">
		<?= get_woocommerce_currency_symbol(); ?>{{{ data.variation.custom_cart_price }}}
		<small class="woocommerce-price-suffix">Incl. VAT</small>
		</span>
	</div>
	
	<div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div>
<?php
do_action('ppi_file_upload_params_div');
do_action('ppi_variant_info_div');
?>
</script>
<script type="text/template" id="tmpl-unavailable-variation-template">
	<p><?php esc_html_e('Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce'); ?></p>
</script>