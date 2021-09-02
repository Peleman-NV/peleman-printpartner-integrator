<?php

defined('ABSPATH') || exit;
global $product;
?>
<script type="text/template" id="tmpl-variation-template">

	<div class="woocommerce-variation-description">{{{ data.variation.variation_description }}}</div>
	<div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div>
	<div class="woocommerce-variation-price individual-price-block">
		<span class="individual-price-text"></span>
		<span class="price"><small class="woocommerce-price-suffix"></small></span>
	</div>
	
	<div class="woocommerce-variation-price cart-unit-block ppi-hidden">
		<span class="unit-price-text"></span>
		<span class="price"><small class="woocommerce-price-suffix"></small></span>
	</div>
	
	<?php
	do_action('ppi_file_upload_params_div');
	do_action('ppi_variant_info_div');
	?>
	
</script>
<script type="text/template" id="tmpl-unavailable-variation-template">
	<p><?php esc_html_e('Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce'); ?></p>
</script>