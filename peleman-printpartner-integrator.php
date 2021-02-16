<?php

/**
 * Plugin Name: Peleman printpartner integrator
 * Description: Allows Peleman printpartners using WordPress & Woocommerce to sell Peleman products in addition to their own.
 * Version: 0.4 (15/02/2021)
 * Author: Noë Baeten, Jason Goossens, Chris Schippers
 * Author URI: https://www.peleman.com
 */

/**
 * Check if woocommerce is installed.  If not, a javascript alert message will be shown.
 */
$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if (!in_array('woocommerce/woocommerce.php', $active_plugins)) {
?>
    <script type="text/javascript">
        alert('This plugin was developed to integrate with Woocommerce (5.0.0).\nPlease install Woocommerce to use this plugin.\nIf not, please deactive it.\nContinued use of this plugin without Woocommerce may break this website.');
    </script>
<?php
}

require(plugin_dir_path(__FILE__) . 'includes/enqueue-styles-and-scripts.php');
require(plugin_dir_path(__FILE__) . 'includes/admin-menu.php');
require(plugin_dir_path(__FILE__) . 'includes/add-custom-fields.php');
require(plugin_dir_path(__FILE__) . 'includes/imaxel-product-page.php');
require(plugin_dir_path(__FILE__) . 'includes/upload-content.php');
require(plugin_dir_path(__FILE__) . 'includes/add-file-upload.php');
