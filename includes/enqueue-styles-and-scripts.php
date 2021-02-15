<?php

/**
 * Loads frontend javascript & css and bootstrap
 * Boostrap conflicts with Pangja theme - do not load
 */
function enqueue_frontend_assets()
{
    wp_register_style('ppi_style', plugins_url('../views/public/css/style.css', __FILE__));
    wp_enqueue_style('ppi_style');
    wp_register_script('ppi_main_script', plugins_url('../views/public/js/main.js', __FILE__),  array('jquery')); // load main with jquery as a dependency
    wp_enqueue_script('ppi_main_script');
    // wp_register_script('jquery_form_script', plugins_url('../views/public/js/jquery-form.js', __FILE__),  array('jquery')); // load jQuery-form with jquery as a dependency
    // wp_enqueue_script('jquery_form_script');
}
add_action('wp_enqueue_scripts', 'enqueue_frontend_assets');

/**
 * Loads admin css and bootstrap
 */
function enqueue_admin_assets()
{
    wp_register_style('ppi_admin_style', plugins_url('../views/admin/css/admin-style.css', __FILE__));
    wp_enqueue_style('ppi_admin_style');
}
add_action('admin_enqueue_scripts', 'enqueue_admin_assets');

/**
 * Localize the Ajax script to pass vars to JavaScript
 */
function enqueue_ajax()
{
    wp_enqueue_script('ppi-ajax-upload', plugins_url('../views/public/js/file-upload.js', __FILE__), array('jquery'));
    wp_localize_script(
        'ppi-ajax-upload',
        'ppi_ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('file_upload_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'enqueue_ajax', 1);
