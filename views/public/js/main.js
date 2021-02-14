jQuery(document).ready(function () {
    //jQuery('table.variations tr:last').remove(); //removes last empty table row in variations table
    console.log('hiero');

    jQuery('.single_variation_wrap').on(
        'show_variation',
        (event, variation) => {
            console.log(variation);
        }
    );
});
