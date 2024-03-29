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
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
// if (!defined('WPINC')) {
// 	die;
// }

// Constants definition
define('PELEMAN_PRINTPARTNER_INTEGRATOR_VERSION', '1.0.0');
!defined('PPI_USER_PROJECTS_TABLE') ? define('PPI_USER_PROJECTS_TABLE', $wpdb->prefix . 'ppi_user_projects') : "";
!defined('PPI_UPLOAD_DIR') ? define('PPI_UPLOAD_DIR', WP_CONTENT_DIR . '/uploads/ppi/content') : "";
!defined('PPI_IMAXEL_FILES_DIR') ? define('PPI_IMAXEL_FILES_DIR', WP_CONTENT_DIR . '/uploads/ppi/imaxelfiles') : "";
!defined('PPI_THUMBNAIL_DIR') ? define('PPI_THUMBNAIL_DIR', WP_CONTENT_DIR . '/uploads/ppi/thumbnails') : "";
!defined('PPI_LOG_DIR') ? define('PPI_LOG_DIR', WP_CONTENT_DIR . '/uploads/ppi/logs') : "";
!defined('PPI_TEXT_DOMAIN') ? define('PPI_TEXT_DOMAIN', 'peleman-printpartner-integrator') : "";

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
