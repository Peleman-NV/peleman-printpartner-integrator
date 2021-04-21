<?php

namespace PelemanPrintpartnerIntegrator\Admin;

use PelemanPrintpartnerIntegrator\Services\ImaxelService;
use DateTime;

/**
 * The admin-specific functionality of the plugin.
 * 
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/admin
 * @author     Noë Baeten, Jason Goossens, Chris Schippers <None>
 */
class PpiAdmin
{
	private $logFile = PPI_LOG_DIR . '/imaxelFileDownloader.txt';
	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/style.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/admin-ui.js', array('jquery'), $this->version, true);
	}

	/**
	 * Register plugin menu item
	 */
	public function ppi_add_admin_menu()
	{
		add_menu_page('Peleman Printshop Integrator', 'Peleman', 'manage_options', 'ppi-menu.php', array($this, 'require_admin_page'),  'dashicons-format-gallery');
	}

	/**
	 * Register plugin admin page
	 */
	public function require_admin_page()
	{
		require_once 'partials/ppi-menu.php';
	}

	/**
	 * Register plugin settings
	 */
	public function ppi_register_plugin_settings()
	{
		register_setting('ppi_custom_settings', 'ppi-imaxel-private-key');
		register_setting('ppi_custom_settings', 'ppi-imaxel-public-key');
		register_setting('ppi_custom_settings', 'ppi-imaxel-shop-code');
	}

	/**
	 * Adds text inputs for the Imaxel template ID and PDF upload information to variable products
	 * 
	 * @param Int       $loop An interator to give each input field a unique name
	 * @param Array     $variation_data Information about the specific variation
	 * @param WP_Post   $variation Information about the product variation
	 */
	public function ppi_add_custom_fields_to_variable_products($loop, $variation_data, $variation)
	{
		$variationId = $variation->ID;
		$wc_variation = wc_get_product($variationId);
		$parentId = $wc_variation->get_parent_id();

		echo '<div class="ppi-options-group"><h2 class="ppi-options-group-title">Fly2Data Properties</h2>';

		woocommerce_wp_text_input(array(
			'id' => 'template_id[' . $loop . ']',
			'placeholder' => 'Imaxel template ID',
			'class' => 'short',
			'label' => '<a href="https://services.imaxel.com/peleman/admin">Template ID</a>',
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('TemplateID<br>E.g. M002<br>Leave empty for no customisation', 'woocommerce'),
			'value' => get_post_meta($variationId, 'template_id', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'variant_code[' . $loop . ']',
			'placeholder' => 'Variant code',
			'class' => 'short',
			'label' => '<a href="https://services.imaxel.com/peleman/admin">Variant code</a>',
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('Variant code<br>E.g. 00201<br>Leave empty for no customisation', 'woocommerce'),
			'value' => get_post_meta($variationId, 'variant_code', true)
		));

		$pdf_upload_required = get_post_meta($variationId, 'pdf_upload_required', true);
		//$pdf_upload_required = get_post_meta($parentId, 'pdf_upload_required', true);
		$pdf_fields_readonly = $pdf_upload_required == "no" || empty($pdf_upload_required) ? array('readonly' => 'readonly') : '';

		woocommerce_wp_checkbox(array(
			'id' => 'pdf_upload_required[' . $loop . ']',
			'label'       => __('PDF content required?', 'woocommerce'),
			'description' => __('Check to require a PDF upload', 'woocommerce'),
			'desc_tip'    => true,
			'value' => $pdf_upload_required,
		));

		woocommerce_wp_text_input(array(
			'id' => 'pdf_width_mm[' . $loop . ']',
			'class' => 'short',
			'label' => 'Page width (mm)',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('PDF page width in MM', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'pdf_width_mm', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));

		woocommerce_wp_text_input(array(
			'id' => 'pdf_height_mm[' . $loop . ']',
			'class' => 'short',
			'label' => 'Page height (mm)',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('PDF page height in MM', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'pdf_height_mm', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));

		woocommerce_wp_text_input(array(
			'id' => 'pdf_min_pages[' . $loop . ']',
			'class' => 'short',
			'label' => 'Minimum number of pages',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('Minimum number of pages in the PDF content file', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'pdf_min_pages', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));

		woocommerce_wp_text_input(array(
			'id' => 'pdf_max_pages[' . $loop . ']',
			'class' => 'short',
			'label' => 'Maximum number of pages',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('Maximum number of pages in the PDF content file', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'pdf_max_pages', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));

		woocommerce_wp_text_input(array(
			'id' => 'price_per_page[' . $loop . ']',
			'class' => 'short',
			'label' => 'Price per page',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('Price per page', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'price_per_page', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));
		echo '</div>';
	}

	/**
	 * Persists custom input fields
	 * 
	 * @param Int  $loop An interator to give each input field a unique name
	 * @param Int  $variation_id Id for the current variation
	 */
	public function ppi_persist_custom_field_variations($variation_id, $i)
	{
		$template_id = $_POST['template_id'][$i];
		$variant_code = $_POST['variant_code'][$i];
		$pdf_upload_required = isset($_POST['pdf_upload_required']) ? 'yes' : 'no';
		$pdf_width_mm = $_POST['pdf_width_mm'][$i];
		$pdf_height_mm = $_POST['pdf_height_mm'][$i];
		$pdf_min_pages = $_POST['pdf_min_pages'][$i];
		$pdf_max_pages = $_POST['pdf_max_pages'][$i];
		$price_per_page = $_POST['price_per_page'][$i];

		if (isset($template_id)) update_post_meta($variation_id, 'template_id', esc_attr($template_id));
		if (isset($variant_code)) update_post_meta($variation_id, 'variant_code', esc_attr($variant_code));
		if (isset($pdf_upload_required)) update_post_meta($variation_id, 'pdf_upload_required', $pdf_upload_required);
		if (isset($pdf_width_mm)) update_post_meta($variation_id, 'pdf_width_mm', esc_attr($pdf_width_mm));
		if (isset($pdf_height_mm)) update_post_meta($variation_id, 'pdf_height_mm', esc_attr($pdf_height_mm));
		if (isset($pdf_min_pages)) update_post_meta($variation_id, 'pdf_min_pages', esc_attr($pdf_min_pages));
		if (isset($pdf_max_pages)) update_post_meta($variation_id, 'pdf_max_pages', esc_attr($pdf_max_pages));
		if (isset($price_per_page)) update_post_meta($variation_id, 'price_per_page', esc_attr($price_per_page));
	}

	/**
	 * Adds text inputs for the general product attributes
	 */
	public function ppi_add_custom_fields_to_parent_products()
	{
		$product_id = (isset($_GET['post']) && $_GET['post'] != '') ? $_GET['post'] : '';
		$customizable_product = get_post_meta($product_id, 'customizable_product', true);

		woocommerce_wp_checkbox(array(
			'id' => 'customizable_product',
			'label'       => __('Customizable product?', 'woocommerce'),
			'description' => __('Check if this product can be personalized with the editor', 'woocommerce'),
			'desc_tip'    => true,
			'value' => $customizable_product,
		));

		woocommerce_wp_text_input(array(
			'id' => 'custom_add_to_cart_label',
			'placeholder' => 'eg: Design project',
			'class' => 'short',
			'label' =>  __('Custom Add to cart label', 'woocommerce'),
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('Define a custom Add to cart label', 'woocommerce'),
			'value' => $product_id != null ? get_post_meta($product_id, 'custom_add_to_cart_label', true) : ""
		));
	}

	/**
	 * Persists custom input fields on parent product
	 */
	public function ppi_persist_custom_parent_attributes($post_id)
	{
		$custom_add_to_cart_label = $_POST['custom_add_to_cart_label'];
		$customizable_product = isset($_POST['customizable_product']) ? 'yes' : 'no';

		if (isset($custom_add_to_cart_label)) update_post_meta($post_id, 'custom_add_to_cart_label', esc_attr($custom_add_to_cart_label));
		if (isset($customizable_product)) update_post_meta($post_id, 'customizable_product', $customizable_product);
	}

	/**	
	 * Register check pending orders endpoint
	 */
	public function registerCheckPendingOrdersEndpoint()
	{
		register_rest_route('ppi/v1', '/pendingorders', array(
			'methods' => 'GET',
			'callback' => array($this, 'checkPendingOrders'),
			'permission_callback' => '__return_true'
		));
	}

	public function checkPendingOrders()
	{

		$imaxel = new ImaxelService();
		$response = $imaxel->get_pending_orders();
		$pendingOrders = json_decode($response['body']);
		//wp_send_json($pendingOrders);
		if (count($pendingOrders) === 0) {
			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': No pending orders' . PHP_EOL, 3,  $this->logFile);
			wp_send_json(array('response' => 'No pending orders'), 200);
		}

		$response = [];
		$orders = [];
		foreach ($pendingOrders as $order) {
			$orderId = $order->id;
			$projectId = $order->jobs[0]->project->id;
			$product = $order->jobs[0]->product->variants[0]->name->default;
			$wooCommerceOrderId = str_replace('WC order ID: ', '', $order->notes);
			// extract file URLs from files key
			$filesCollection = array_map(function ($files) {
				return $files->url;
			}, $order->files);

			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': downloading ' . count($filesCollection) . ' files for order ' . $wooCommerceOrderId . PHP_EOL, 3,  $this->logFile);

			$this->downloadFiles($filesCollection, $wooCommerceOrderId);
			$orders[] = [
				'Product' => $product,
				'Project ID' => $projectId,
				'Imaxel order ID' => $orderId,
				'WooCommerce order ID' => $wooCommerceOrderId,
				'files' => count($filesCollection)
			];

			$imaxel->mark_order_as_downloaded($orderId);

			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': marked WC order ' . $wooCommerceOrderId . ' (Imaxel order: ' . $orderId . ') as downloaded.' . PHP_EOL, 3,  $this->logFile);
		}

		$response['result'] = 'Processed ' . count($pendingOrders) . ' orders';
		$response['orderData'] = $orders;
		wp_send_json($response, 200);
	}

	private function downloadFiles($files, $orderId)
	{
		$downloadFolder = PPI_IMAXEL_FILES_DIR . "/{$orderId}";
		$this->createOrderFolder($downloadFolder);

		$fileName = 0;
		foreach ($files as $file) {
			$ext = substr($file, strrpos($file, '.'));
			$fileName = str_pad($fileName, 5, '0', STR_PAD_LEFT);
			$fullPath = "{$downloadFolder}/{$fileName}{$ext}";
			file_put_contents($fullPath, file_get_contents($file));

			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': downloaded file ' . $file . PHP_EOL, 3,  $this->logFile);

			$fileName++;
		}
	}

	private function createOrderFolder($downloadFolder)
	{
		// if folder exist, clear all files, else create folder
		if (file_exists($downloadFolder)) {
			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': folder exists' . PHP_EOL, 3,  $this->logFile);
			$files = glob($downloadFolder . '\*');

			if (!empty($files)) {
				$now =  new DateTime('NOW');
				error_log($now->format('c') . ': deleted files' . PHP_EOL, 3,  $this->logFile);
				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					}
				}
			}
		} else {
			mkdir($downloadFolder, 0777);
			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': created folder' . PHP_EOL, 3,  $this->logFile);
		}
	}
}
