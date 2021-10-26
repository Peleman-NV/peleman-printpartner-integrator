<?php

namespace PelemanPrintpartnerIntegrator\Includes;

use PelemanPrintpartnerIntegrator\Includes\PpiLoader;
use PelemanPrintpartnerIntegrator\Includes\PpiI18n;
use PelemanPrintpartnerIntegrator\Admin\PpiAdmin;
use PelemanPrintpartnerIntegrator\PublicPage\PpiProductPage;
use PelemanPrintpartnerIntegrator\API\PpiAPI;

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

		//$this->load_dependencies();
		$this->loader = new PpiLoader();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_api_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
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
		$this->loader->add_action('wp_ajax_save_f2d_custnr', $plugin_admin, 'save_f2d_custnr', 5);

		$this->loader->add_action('admin_menu', $plugin_admin, 'ppi_add_admin_menu');
		$this->loader->add_action('admin_init', $plugin_admin, 'ppi_register_plugin_settings');
		$this->loader->add_action('woocommerce_product_options_general_product_data', $plugin_admin, 'ppi_add_custom_fields_to_parent_products', 11, 3);
		$this->loader->add_action('woocommerce_process_product_meta', $plugin_admin, 'ppi_persist_custom_parent_attributes', 11, 3);
		$this->loader->add_action('woocommerce_product_after_variable_attributes', $plugin_admin, 'ppi_add_custom_fields_to_variable_products', 11, 3);
		$this->loader->add_action('woocommerce_save_product_variation', $plugin_admin, 'ppi_persist_custom_field_variations', 11, 2);
		$this->loader->add_action('manage_edit-shop_order_columns', $plugin_admin, 'ppiAddTrackingDataColumnToOrderOverview', 11, 1);
		$this->loader->add_action('manage_edit-shop_order_columns', $plugin_admin, 'ppiAddTrackingDataColumnToOrderOverview', 11, 1);
		$this->loader->add_action('manage_shop_order_posts_custom_column', $plugin_admin, 'ppiAddTrackingDataToOrderOverview', 11, 1);

		$this->loader->add_action('woocommerce_order_item_display_meta_key', $plugin_admin, 'displayCustomMetaDataKey', 10, 3);
		$this->loader->add_action('woocommerce_order_item_display_meta_value', $plugin_admin, 'displayCustomMetaDataValue', 10, 3);

		$this->loader->add_action('woocommerce_admin_order_data_after_shipping_address', $plugin_admin, 'displayTrackingInformation');
		$this->loader->add_action('woocommerce_admin_order_data_after_billing_address', $plugin_admin, 'displayFly2DataCustomerNumberDiv', 10, 1);

		// add/edit data to admin user detail
		$this->loader->add_action('show_user_profile', $plugin_admin, 'displayCustomDataInUserDetail', 10, 1);
		$this->loader->add_action('edit_user_profile', $plugin_admin, 'displayCustomDataInUserDetail', 10, 1);
		$this->loader->add_action('personal_options_update', $plugin_admin, 'saveCustomDataInUserDetail', 10, 1);
		$this->loader->add_action('edit_user_profile_update', $plugin_admin, 'saveCustomDataInUserDetail', 10, 1);
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

		// Load styles, scripts, and custom templates
		$this->loader->add_action('wp_enqueue_scripts', $product_page, 'enqueue_styles');
		//$this->loader->add_action('wp_enqueue_scripts', $product_page, 'enqueue_scripts', 6);
		$this->loader->add_action('wp_enqueue_scripts', $product_page, 'enqueue_ajax', 5);
		$this->loader->add_action('woocommerce_locate_template', $product_page, 'ppi_override_wc_templates', 10, 3);

		$this->loader->add_action('ppi_file_upload_params_div', $product_page, 'ppi_output_file_params', 7, 1);
		$this->loader->add_action('ppi_file_upload_output_form', $product_page, 'ppi_output_form', 7, 1);
		$this->loader->add_action('ppi_variant_info_div', $product_page, 'ppi_output_variant_info', 7, 1);
		$this->loader->add_action('ppi_redirection_info_div', $product_page, 'ppi_output_redirection_info', 7, 1);
		$this->loader->add_action('woocommerce_single_variation', $product_page, 'ppi_change_add_to_cart_text_for_imaxel_products', 10);
		// Call us to order 
		$this->loader->add_action('woocommerce_get_price_html', $product_page, 'displayCallUsTextForSimpleProductsWithoutPrice', 8, 2);
		$this->loader->add_action('woocommerce_single_product_summary', $product_page, 'displayCallUsButtonForSimpleProductsWithoutPrice', 8);

		// Ajax: when a new variation is selected
		$this->loader->add_action('wp_ajax_get_product_variation_data', $product_page, 'get_product_variation_data');
		$this->loader->add_action('wp_ajax_nopriv_get_product_variation_data', $product_page, 'get_product_variation_data');
		// Ajax: when content is uploaded
		$this->loader->add_action('wp_ajax_upload_content_file', $product_page, 'upload_content_file');
		$this->loader->add_action('wp_ajax_nopriv_upload_content_file', $product_page, 'upload_content_file');
		// Ajax: unused?
		$this->loader->add_action('wp_ajax_display_variant_info', $product_page, 'display_variant_info');
		$this->loader->add_action('wp_ajax_nopriv_display_variant_info', $product_page, 'display_variant_info');
		// Ajax: when adding to cart
		$this->loader->add_action('wp_ajax_ppi_add_to_cart', $product_page, 'ppi_add_to_cart');
		$this->loader->add_action('wp_ajax_nopriv_ppi_add_to_cart', $product_page, 'ppi_add_to_cart');
		// Ajax: project page actions
		$this->loader->add_action('wp_ajax_handle_project_action', $product_page, 'handle_project_action');
		$this->loader->add_action('wp_ajax_nopriv_handle_project_action', $product_page, 'handle_project_action');

		// Price, cart, and order customizations
		$this->loader->add_action('woocommerce_add_to_cart_validation', $product_page, 'readImaxelProjectOnReturnFromEditor', 10, 5);
		$this->loader->add_action('woocommerce_add_cart_item_data', $product_page, 'addCustomDataToCartItems', 10, 3);
		$this->loader->add_action('woocommerce_checkout_create_order_line_item', $product_page, 'addCustomDataToOrderLineItem', 10, 4);
		$this->loader->add_action('woocommerce_order_status_changed', $product_page, 'onOrderProcessing', 10, 4);
		$this->loader->add_action('woocommerce_before_calculate_totals', $product_page, 'adjust_cart_item_price', 10);
		$this->loader->add_action('woocommerce_widget_cart_item_quantity', $product_page, 'adjust_mini_cart_item_price', 10, 3);
		$this->loader->add_action('woocommerce_available_variation', $product_page, 'add_unit_data_to_variation_object', 11, 3);

		// User order page: display tracking info
		$this->loader->add_action('ppi_display_order_tracking_information', $product_page, 'ppi_output_order_tracking_information', 7, 1);

		// Project page: on hold
		// $this->loader->add_action('woocommerce_account_menu_items', $product_page, 'add_projects_menu_item', 7, 1);
		// $this->loader->add_action('init', $product_page, 'register_projects_endpoint', 7, 1);
		// $this->loader->add_action('woocommerce_account_projects_endpoint', $product_page, 'projects_endpoint_content', 7, 1);
	}

	/**
	 * Register all of the hooks related to the API functionality of the plugin.
	 */
	private function define_api_hooks()
	{
		$plugin_api = new PpiAPI($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('rest_api_init', $plugin_api, 'registerCheckPendingOrdersEndpoint');
		$this->loader->add_action('rest_api_init', $plugin_api, 'registerGetOrderEndpoint');
		$this->loader->add_action('rest_api_init', $plugin_api, 'registerCompleteOrderEndpoint');
		$this->loader->add_action('rest_api_init', $plugin_api, 'registerAddTrackingToOrderEndpoint');
		$this->loader->add_action('rest_api_init', $plugin_api, 'registerAddCustomerMetaDataEndpoint');
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
