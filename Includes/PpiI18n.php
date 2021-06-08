<?php

namespace PelemanPrintpartnerIntegrator\Includes;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       None
 * @since      1.0.0
 *
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/includes
 * @author     Noë Baeten, Jason Goossens, Chris Schippers <None>
 */
class PpiI18n
{
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{
		$languageDir = realpath(plugin_dir_path(__FILE__) . '/../languages');

		load_plugin_textdomain(
			'peleman-printpartner-integrator',
			false,
			$languageDir
		);
	}
}
