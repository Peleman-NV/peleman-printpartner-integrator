<?php

namespace PelemanPrintpartnerIntegrator;

use PelemanPrintpartnerIntegrator\Includes\PpiActivator;
use PelemanPrintpartnerIntegrator\Includes\PpiDeactivator;
use PelemanPrintpartnerIntegrator\Includes\Plugin;

require 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . '/Includes/PpiActivator.php';
require_once plugin_dir_path(__FILE__) . '/Includes/PpiDeactivator.php';

/**
 * @since             1.0.0
 * @package           Peleman_Printpartner_Integrator
 *
 * @wordpress-plugin
 * Plugin Name:       Peleman printpartner integrator
 * Plugin URI:        https://www.peleman.com
 * Description:       Allows Peleman printpartners using WordPress & Woocommerce to sell Peleman products in addition to their own.
 * Version:           1.0.0
 * Author:            Noë Baeten, Jason Goossens, Chris Schippers
 * Text Domain:       peleman-printpartner-integrator
 * Domain Path:       /Languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Require WooCommerce
$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if (!in_array('woocommerce/woocommerce.php', $active_plugins)) {
	echo '<h1 class="error">This plugin requires WooCommerce (v5.0.0) to work properly.<br>To remove this message, please install and activate WooCommerce</h1>';
	wp_die();
}

// Constants definition
define('PELEMAN_PRINTPARTNER_INTEGRATOR_VERSION', '1.0.0');
!defined('PPI_UPLOAD_DIR') ? define('PPI_UPLOAD_DIR', realpath(ABSPATH . 'wp-content/uploads/ppi/content')) : "";
!defined('PPI_THUMBNAIL_DIR') ? define('PPI_THUMBNAIL_DIR', realpath(ABSPATH . 'wp-content/uploads/ppi/content-thumbnails')) : "";
!defined('PPI_LOG_DIR') ? define('PPI_LOG_DIR', realpath(ABSPATH . 'wp-content/uploads/ppi/logs')) : "";
!defined('PPI_USER_PROJECTS_TABLE') ? define('PPI_USER_PROJECTS_TABLE', $wpdb->prefix . 'ppi_user_projects') : "";

/**
 * The code that runs during plugin activation.
 */
function activate_pelemanPrintpartnerIntegrator()
{
	PpiActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_pelemanPrintpartnerIntegrator()
{
	PpiDeactivator::deactivate();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\activate_pelemanPrintpartnerIntegrator');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivate_pelemanPrintpartnerIntegrator');

/**
 * Begins execution of the plugin.
 */
function run_peleman_printpartner_integrator()
{
	$plugin = new Plugin();
	$plugin->run();
}
run_peleman_printpartner_integrator();
