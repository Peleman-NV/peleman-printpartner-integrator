<?php

/**
 * Add plugin setting page to WordPress admin area
 */
function ppi_add_admin_menu()
{
    add_menu_page('Peleman Printshop Integrator', 'Peleman', 'manage_options', 'ppi-menu.php', 'require_admin_page',  'dashicons-format-gallery');
}

/**
 * Require admin page
 */
function require_admin_page()
{
    require_once plugin_dir_path(__FILE__) . '../views/admin/html/ppi-menu.php';
}
add_action('admin_menu', 'ppi_add_admin_menu');

/**
 * Register plugin settings
 */
function ppi_register_plugin_settings()
{
    register_setting('ppi_custom_settings', 'ppi-imaxel-private-key');
    register_setting('ppi_custom_settings', 'ppi-imaxel-public-key');
}
add_action('admin_init', 'ppi_register_plugin_settings');
