<?php

/**
 * Loads frontend javascript & css and bootstrap
 */
function enqueue_frontend_assets()
{
    wp_register_style('ppi_style', plugins_url('../views/public/css/style.css', __FILE__));
    wp_enqueue_style('ppi_style');
    wp_register_script('ppi_main_script', plugins_url('../views/public/js/main.js', __FILE__),  array('jquery')); // load main with jquery as a dependency
    wp_enqueue_script('ppi_main_script');
    wp_register_style('ppi_admin_bootstrap_style', plugins_url('/../views/public/css/bootstrap.min.css', __FILE__));
    wp_enqueue_style('ppi_admin_bootstrap_style');
    wp_register_script('ppi_admin_bootstrap_script', plugins_url('/../views/public/js/bootstrap.bundle.min.js', __FILE__));
    wp_enqueue_script('ppi_admin_bootstrap_script');
}
add_action('wp_enqueue_scripts', 'enqueue_frontend_assets');

/**
 * Loads admin css and bootstrap
 */
function enqueue_admin_assets()
{
    wp_register_style('ppi_admin_style', plugins_url('../views/admin/css/admin-style.css', __FILE__));
    wp_enqueue_style('ppi_admin_style');
    wp_register_style('ppi_admin_bootstrap_style', plugins_url('/../views/public/css/bootstrap.min.css', __FILE__));
    wp_enqueue_style('ppi_admin_bootstrap_style');
    wp_register_script('ppi_admin_bootstrap_script', plugins_url('/../views/public/js/bootstrap.bundle.min.js', __FILE__));
    wp_enqueue_script('ppi_admin_bootstrap_script');
}
add_action('admin_enqueue_scripts', 'enqueue_admin_assets');
