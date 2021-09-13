<?php
if (!defined('ABSPATH')) {
    exit;
}

global $product;

if ($isSimpleProduct = $product->is_type('simple')) {
    $showPricesWithVat = get_option('woocommerce_prices_include_tax') === 'yes' ? true : false;
    $individualPrice = $showPricesWithVat ? wc_get_price_including_tax($product) : wc_get_price_excluding_tax($product);
    $wcCountries = new \WC_Countries();
    $priceSuffix = $wcCountries->ex_tax_or_vat();
    $individualPriceWithCurrencySymbol = get_woocommerce_currency_symbol()
        . number_format($individualPrice, 2)
        . ' '
        . $priceSuffix;

    $bundlePrice = $product->get_meta('cart_price');
    $bundleUnits = $product->get_meta('cart_units');

    if ($isBundleProduct = isset($bundlePrice) && !empty($bundlePrice) && isset($bundleUnits) && !empty($bundleUnits) && $bundleUnits > 1) {

        $bundlePriceWithCurrencySymbol =  get_woocommerce_currency_symbol()
            . number_format($bundlePrice, 2)
            . ' '
            . $priceSuffix
            . ' ('
            . $bundleUnits
            . ' '
            . __('pieces', PPI_TEXT_DOMAIN)
            . ')';
    }
}
?>

<div class="product_meta">
    <?php do_action('woocommerce_product_meta_start'); ?>
    <span class="sku_wrapper">
        <span class="add-to-cart-price">
            <span class="label">
                <?php echo __('price', PPI_TEXT_DOMAIN) . ': '; ?>
            </span>
            <span class="price-amount">
                <?= $isSimpleProduct && $isBundleProduct ? $bundlePriceWithCurrencySymbol : $individualPriceWithCurrencySymbol; ?>
            </span>
        </span>
        <br>
        <span class="individual-price <?= !$isBundleProduct ? 'ppi-hidden' : ''; ?>">
            <span class="label">
                <?php echo __('Individual price', PPI_TEXT_DOMAIN) . ': '; ?>
            </span>
            <span class="price-amount">
                <?= $isSimpleProduct && $isBundleProduct ? $individualPriceWithCurrencySymbol : ''; ?>
            </span>
        </span>
    </span>

    <?php if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type('variable'))) : ?>
        <span class="sku_wrapper">
            <span class="label">
                <?php esc_html_e('SKU:', 'woocommerce'); ?>
            </span>
            <span class="sku">
                <?php echo ($sku = $product->get_sku()) ? $sku : esc_html__('N/A', 'woocommerce'); ?>
            </span>
        </span>
    <?php endif; ?>

    <?php
    echo wc_get_product_category_list(
        $product->get_id(),
        ', ',
        '<span class="posted_in"><span class="label">' . _n('Category:', 'Categories:', count($product->get_category_ids()), 'woocommerce') . '</span> ',
        '</span>'
    );
    ?>

    <?php
    echo wc_get_product_tag_list(
        $product->get_id(),
        ', ',
        '<span class="tagged_as"><span class="label">' . _n('Tag:', 'Tags:', count($product->get_tag_ids()), 'woocommerce') . '</span> ',
        '</span>'
    ); ?>

    <?php do_action('woocommerce_product_meta_end'); ?>

</div>