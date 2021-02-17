<?php

/**
 * Loads frontend javascript & css and javascript
 * Boostrap conflicts with Pangja theme - do not load
 */
function enqueue_frontend_assets()
{
    wp_register_style('ppi_style', plugins_url('../views/public/css/style.css', __FILE__));
    wp_enqueue_style('ppi_style');
    wp_register_script('ppi_main_script', plugins_url('../views/public/js/main.js', __FILE__),  array('jquery')); // load main with jquery as a dependency
    wp_enqueue_script('ppi_main_script');
}
add_action('wp_enqueue_scripts', 'enqueue_frontend_assets');

/**
 * Loads admin css and javascript
 */
function enqueue_admin_assets()
{
    global $pagenow;
    if ($_GET['page'] == "ppi-menu.php" || $pagenow == 'post.php') {
        wp_register_style('ppi_admin_style', plugins_url('../views/admin/css/admin-style.css', __FILE__));
        wp_enqueue_style('ppi_admin_style');
    }
    if ($pagenow == 'post.php') {
        wp_register_script('ppi_admin_ui_helper', plugins_url('../views/admin/js/custom-fields-ui-helper.js', __FILE__), array('jquery'));
        wp_enqueue_script('ppi_admin_ui_helper');
    }
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
    wp_enqueue_script('ppi-redirect-to-imaxel-editor', plugins_url('../views/public/js/redirect-to-imaxel-editor.js', __FILE__), array('jquery'));
    wp_localize_script(
        'ppi-redirect-to-imaxel-editor',
        'ppi_ajax_redirect',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('editor-redirect-nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'enqueue_ajax', 1);
