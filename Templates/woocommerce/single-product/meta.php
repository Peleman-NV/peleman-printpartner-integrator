<?php
if (!defined('ABSPATH')) {
    exit;
}

$isB2cSite = get_option('ppi-is-b2b');

global $product;
$callUsToPurchase = $product->get_meta('call_to_order') === 'yes';

if ($isSimpleProduct = $product->is_type('simple')) {
    $showPricesWithVat = get_option('woocommerce_prices_include_tax') === 'yes' ? true : false;
    $priceSuffix = $product->get_price_suffix();

    $individualPrice = $showPricesWithVat ? wc_get_price_including_tax($product) : wc_get_price_excluding_tax($product);
    $individualPriceWithCurrencySymbol = get_woocommerce_currency_symbol() . number_format($individualPrice, 2);

    $bundlePrice = $product->get_meta('cart_price');
    $bundleUnits = $product->get_meta('cart_units');

    if ($isB2cSite) {
        if ($isBundleProduct = isset($bundlePrice) && !empty($bundlePrice) && isset($bundleUnits) && !empty($bundleUnits) && $bundleUnits > 1) {
            $bundlePriceWithCurrencySymbol =  get_woocommerce_currency_symbol() . number_format($bundlePrice, 2);
            $bundleLabel = ' (' . $bundleUnits . ' ' . __('pieces', PPI_TEXT_DOMAIN) . ')';
        }
    }
}
?>

<div id="call-us-btn" class="ppi-hidden">
    <a href="tel:+3238893241" class="button" style="background-color: #f7631e !important; padding-top: 20px !important; padding-bottom: 20px !important;">Call us for a quote at +32 3 889 32 41</a>
</div>
<div class="product_meta">
    <?php do_action('woocommerce_product_meta_start'); ?>

    <?php if (!$callUsToPurchase && ($isSimpleProduct && $individualPrice != '') || !$isSimpleProduct) : ?>
        <span class="sku_wrapper">
            <span id="call-us-price" class="label ppi-hidden">Price: call us for a quote at +32 3 889 32 41</span>
            <span class="individual-price <?php echo _e(!$isBundleProduct ? 'ppi-hidden' : ''); ?>">
                <span class="label">
                    <?php echo __('Individual price', PPI_TEXT_DOMAIN) . ': '; ?>
                </span>
                <span class="price-amount woocommerce-Price-amount amount">
                    <?php echo _e($isSimpleProduct && $isBundleProduct ? $individualPriceWithCurrencySymbol : ''); ?>
                </span>
                <span class="woocommerce-price-suffix">
                    <?php echo _e($priceSuffix); ?>
                </span>
            </span>
            <span class="add-to-cart-price">
                <span class="label">
                    <?php echo __('price', PPI_TEXT_DOMAIN) . ': '; ?>
                </span>
                <span class="price-amount woocommerce-Price-amount amount">
                    <?php echo _e($isSimpleProduct && $isBundleProduct ? $bundlePriceWithCurrencySymbol : $individualPriceWithCurrencySymbol); ?>
                </span>
                <span class="woocommerce-price-suffix">
                    <?php
                    echo _e($priceSuffix);
                    echo _e($isSimpleProduct && $isBundleProduct ? '<span class="bundle-suffix">' . $bundleLabel . '</span>' : '');
                    ?>
                </span>
            </span>
        </span>
    <?php endif; ?>

    <?php
    $articleCode = $product->get_meta('f2d_artcd');
    ?>
    <!-- Simple product: display article code instead of SKU -->
    <?php if ($isSimpleProduct && !empty($articleCode)) : ?>
        <span class="sku_wrapper">
            <span class="label">
                <?php esc_html_e('Article code:', PPI_TEXT_DOMAIN); ?>
            </span>
            <span class="sku">
                <?php echo _e($articleCode); ?>
            </span>
        </span>
    <?php endif; ?>

    <!-- Variable product: display article code placeholder to be filled with the magic of JavaScript! -->
    <?php if (!$isSimpleProduct) : ?>
        <span class="sku_wrapper article-code-container">
            <span class="label article-code-label">
                <?php esc_html_e('Article code:', PPI_TEXT_DOMAIN); ?>
            </span>
        </span>
    <?php endif; ?>

    <!-- Display SKU if user is admin -->
    <?php if (current_user_can('administrator')) : ?>
        <?php if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type('variable'))) : ?>
            <span class="sku_wrapper">
                <span class="label">
                    <?php esc_html_e('SKU:', 'woocommerce'); ?>
                </span>
                <span class="sku">
                    <?php echo ($sku = $product->get_sku()) ? _e($sku) : esc_html__('N/A', 'woocommerce'); ?>
                </span>
            </span>
        <?php endif; ?>
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