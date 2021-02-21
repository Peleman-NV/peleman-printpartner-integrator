console.log('hello');
console.log(jQuery('.variations_form'));
jQuery('.variations_form').on(
    'woocommerce_variation_select_change',
    function () {
        console.log('object');
    }
);
jQuery('table.variations').on('click', function () {
    console.log('object');
});
