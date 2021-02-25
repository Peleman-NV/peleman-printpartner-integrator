<?php

namespace PelemanPrintpartnerIntegrator;

use PelemanPrintpartnerIntegrator\Includes\PpiActivator;
use PelemanPrintpartnerIntegrator\Includes\PpiDeactivator;
use PelemanPrintpartnerIntegrator\Includes\Plugin;

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
if (!defined('WPINC')) {
	die;
}

define('PELEMAN_PRINTPARTNER_INTEGRATOR_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 */
function activate_peleman_printpartner_integrator()
{
	require_once plugin_dir_path(__FILE__) . 'includes/peleman-printpartner-integrator-activator.php';
	PpiActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_peleman_printpartner_integrator()
{
	require_once plugin_dir_path(__FILE__) . 'includes/peleman-printpartner-integrator-deactivator.php';
	PpiDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_peleman_printpartner_integrator');
register_deactivation_hook(__FILE__, 'deactivate_peleman_printpartner_integrator');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/plugin.php';

/**
 * Begins execution of the plugin.
 */
function run_peleman_printpartner_integrator()
{
	require 'vendor/autoload.php';
	$plugin = new Plugin();
	$plugin->run();
}
run_peleman_printpartner_integrator();
