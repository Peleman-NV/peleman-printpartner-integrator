<?php

namespace PelemanPrintpartnerIntegrator\Includes;

use PelemanPrintpartnerIntegrator\Includes\PpiLoader;
use PelemanPrintpartnerIntegrator\Includes\PpiI18n;
use PelemanPrintpartnerIntegrator\Admin\PpiAdmin;
use PelemanPrintpartnerIntegrator\PublicPage\PpiProductPage;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 * 
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/includes
 * @author     Noë Baeten, Jason Goossens, Chris Schippers <None>
 */
class Plugin
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct()
	{
		if (defined('PELEMAN_PRINTPARTNER_INTEGRATOR_VERSION')) {
			$this->version = PELEMAN_PRINTPARTNER_INTEGRATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'peleman-printpartner-integrator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 * 
	 * - Peleman_Printpartner_Integrator_Loader. Orchestrates the hooks of the plugin.
	 * - Peleman_Printpartner_Integrator_i18n. Defines internationalization functionality.
	 * - Peleman_Printpartner_Integrator_Admin. Defines all hooks for the admin area.
	 * - Peleman_Printpartner_Integrator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		//require_once plugin_dir_path(dirname(__FILE__)) . 'includes/ppi-loader.php';
		//require_once plugin_dir_path(dirname(__FILE__)) . 'includes/ppi-i18n.php';
		//require_once plugin_dir_path(dirname(__FILE__)) . 'admin/ppi-admin.php';
		//require_once plugin_dir_path(dirname(__FILE__)) . 'public/ppi-product-page.php';
		//require_once plugin_dir_path(dirname(__FILE__)) . 'services/imaxel-service.php';
		//require_once plugin_dir_path(dirname(__FILE__)) . 'services/helpers.php';

		$this->loader = new PpiLoader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale()
	{

		$plugin_i18n = new PpiI18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new PpiAdmin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 5);

		$this->loader->add_action('admin_menu', $plugin_admin, 'ppi_add_admin_menu');
		$this->loader->add_action('admin_init', $plugin_admin, 'ppi_register_plugin_settings');
		$this->loader->add_action('woocommerce_product_after_variable_attributes', $plugin_admin, 'ppi_add_custom_fields_to_variable_products', 11, 3);
		$this->loader->add_action('woocommerce_save_product_variation', $plugin_admin, 'ppi_persist_custom_field_variations', 11, 2);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		$product_page = new PpiProductPage($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $product_page, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $product_page, 'enqueue_scripts');

		$this->loader->add_action('ppi_file_upload_output_form', $product_page, 'ppi_output_form', 7, 1);
		$this->loader->add_action('woocommerce_locate_template', $product_page, 'ppi_override_wc_templates', 10, 3);
		$this->loader->add_action('woocommerce_single_variation', $product_page, 'ppi_change_add_to_cart_text_for_imaxel_products', 10);

		$this->loader->add_action('wp_enqueue_scripts', $product_page, 'enqueue_ajax', 5);
		$this->loader->add_action('wp_ajax_upload_content_file', $product_page, 'upload_content_file');
		$this->loader->add_action('wp_ajax_nopriv_upload_content_file', $product_page, 'upload_content_file');
		$this->loader->add_action('wp_ajax_redirect_to_imaxel_editor', $product_page, 'redirect_to_imaxel_editor', 1);
		$this->loader->add_action('wp_ajax_nopriv_redirect_to_imaxel_editor', $product_page, 'redirect_to_imaxel_editor', 1);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    PPI_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}