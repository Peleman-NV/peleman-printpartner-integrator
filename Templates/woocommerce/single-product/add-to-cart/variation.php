<?php

defined('ABSPATH') || exit;
global $product;
?>
<script type="text/template" id="tmpl-variation-template">
	<div class="woocommerce-variation-description">{{{ data.variation.variation_description }}}</div>
	<div class="woocommerce-variation-price">{{{ data.variation.price_html }}}</div>
	<div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div>
	<?php
	$parent_wc_product = wc_get_product($product->get_id());
	if ($parent_wc_product->is_type('variable')) {
		$variants_array = $parent_wc_product->get_children();
		$first_variant = wc_get_product($variants_array[0]);
		if (wc_get_product($first_variant)->get_meta('pdf_upload_required') == "yes") {
			do_action('ppi_file_upload_params_div');
		}
	}
	?>
</script>
<script type="text/template" id="tmpl-unavailable-variation-template">
	<p><?php esc_html_e('Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce'); ?></p>
</script>