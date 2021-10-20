<?php

namespace PelemanPrintpartnerIntegrator\Admin;

use PelemanPrintpartnerIntegrator\Services\ImaxelService;

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
 * @author     Noë Baeten, Jason Goossens, Chris Schippers
 */
class PpiAdmin
{
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
	 * We add an random number for the version for cache busting
	 */
	public function enqueue_styles()
	{
		$randomVersionNumber = rand(0, 1000);
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/style.css', array(), $randomVersionNumber, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 * We add an random number for the version for cache busting
	 */
	public function enqueue_scripts()
	{
		$randomVersionNumber = rand(0, 1000);
		wp_enqueue_script($this->plugin_name . 'product-ui', plugin_dir_url(__FILE__) . 'js/admin-ui.js', array('jquery'), $randomVersionNumber, true);
		wp_enqueue_script($this->plugin_name . 'order-ui', plugin_dir_url(__FILE__) . 'js/order-ui.js', array('jquery'), $randomVersionNumber, true);
	}

	/**
	 * Register plugin menu item
	 */
	public function ppi_add_admin_menu()
	{
		add_menu_page('Peleman Printshop Integrator', 'Peleman Printpartner Integrator', 'manage_options', 'ppi-menu.php', array($this, 'require_admin_page'),  'dashicons-format-gallery');
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
		register_setting('ppi_custom_settings', 'ppi-wc-key');
		register_setting('ppi_custom_settings', 'ppi-wc-secret');
		register_setting('ppi_custom_settings', 'ppi-order-id-prefix');
	}

	/**
	 * Adds text inputs.  Variable products can have Imaxel templates and PDF upload information to variable products
	 * These are usually uploaded, but can be maintained via these inputs
	 * 
	 * @param int       $loop An interator to give each input field a unique name
	 * @param array     $variation_data Information about the specific variation
	 * @param WP_Post   $variation Information about the product variation
	 */
	public function ppi_add_custom_fields_to_variable_products($loop, $variation_data, $variation)
	{
		$variationId = $variation->ID;
		$wc_variation = wc_get_product($variationId);
		$parentId = $wc_variation->get_parent_id();

		echo '<div class="ppi-options-group"><h2 class="ppi-options-group-title">Fly2Data Properties</h2>';

		woocommerce_wp_text_input(array(
			'id' => 'f2d_sku_components[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-first',
			'label' => 'Fly2Data SKU data',
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('F2D components that make up a variation', 'woocommerce'),
			'value' => get_post_meta($variationId, 'f2d_sku_components', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'f2d_artcd[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-last',
			'label' => 'Fly2Data article code',
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('F2D article code', 'woocommerce'),
			'value' => get_post_meta($variationId, 'f2d_artcd', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'template_id[' . $loop . ']',
			'placeholder' => 'Imaxel template ID',
			'wrapper_class' => 'form-row form-row-first',
			'label' => '<a href="https://services.imaxel.com/peleman/admin">Template ID</a>',
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('TemplateID<br>E.g. M002<br>Leave empty for no customisation', 'woocommerce'),
			'value' => get_post_meta($variationId, 'template_id', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'variant_code[' . $loop . ']',
			'placeholder' => 'Variant code',
			'wrapper_class' => 'form-row form-row-last',
			'label' => '<a href="https://services.imaxel.com/peleman/admin">Variant code</a>',
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('Variant code<br>E.g. 00201<br>Leave empty for no customisation', 'woocommerce'),
			'value' => get_post_meta($variationId, 'variant_code', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'custom_variation_add_to_cart_label[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-full',
			'label' =>  __('Custom Add to cart label', 'woocommerce'),
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('Define a custom Add to cart label', 'woocommerce'),
			'value' => get_post_meta($variationId, 'custom_variation_add_to_cart_label', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'price_per_page[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-first',
			'label' => 'Price per additional page (piece/sheet of paper = 2 pages)',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('Price per page', 'woocommerce'),
			'value' => get_post_meta($variationId, 'price_per_page', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'base_number_of_pages[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-last',
			'label' => 'Base number of pages',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('Standard number of pages included in price', 'woocommerce'),
			'value' => get_post_meta($variationId, 'base_number_of_pages', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'cart_price[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-first',
			'label' => 'Unit purchase price',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('These items are sold as units, not individually', 'woocommerce'),
			'value' => get_post_meta($variationId, 'cart_price', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'cart_units[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-last',
			'label' => 'Unit amount',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('Number of items per unit', 'woocommerce'),
			'value' => get_post_meta($variationId, 'cart_units', true)
		));

		woocommerce_wp_text_input(array(
			'id' => 'unit_code[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-first',
			'label' => 'Unit code',
			'type' => 'text',
			'desc_tip'    => true,
			'description' => __('The unit code of this item', 'woocommerce'),
			'value' => get_post_meta($variationId, 'unit_code', true)
		));

		$call_to_order = get_post_meta($variationId, 'call_to_order', true);
		woocommerce_wp_checkbox(array(
			'id' => 'call_to_order[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-full',
			'label'       => __('Call us to order', 'woocommerce'),
			'description' => __('Remove add to cart button and display "Call us to order"', 'woocommerce'),
			'desc_tip'    => true,
			'value' => $call_to_order,
		));

		$pdf_upload_required = get_post_meta($variationId, 'pdf_upload_required', true);
		$pdf_fields_readonly = $pdf_upload_required == "no" || empty($pdf_upload_required) ? array('readonly' => 'readonly') : '';

		woocommerce_wp_checkbox(array(
			'id' => 'pdf_upload_required[' . $loop . ']',
			'class' => 'checkbox pdf_required',
			'wrapper_class' => 'form-row form-row-full',
			'label'       => __('PDF content required?', 'woocommerce'),
			'description' => __('Check to require a PDF upload', 'woocommerce'),
			'desc_tip'    => true,
			'value' => $pdf_upload_required,
		));

		woocommerce_wp_text_input(array(
			'id' => 'pdf_width_mm[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-full',
			'label' => 'Page width (mm)',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('PDF page width in MM', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'pdf_width_mm', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));

		woocommerce_wp_text_input(array(
			'id' => 'pdf_height_mm[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-full',
			'label' => 'Page height (mm)',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('PDF page height in MM', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'pdf_height_mm', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));

		woocommerce_wp_text_input(array(
			'id' => 'pdf_min_pages[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-full',
			'label' => 'Minimum number of pages',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('Minimum number of pages in the PDF content file', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'pdf_min_pages', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));

		woocommerce_wp_text_input(array(
			'id' => 'pdf_max_pages[' . $loop . ']',
			'wrapper_class' => 'form-row form-row-full',
			'label' => 'Maximum number of pages',
			'type' => 'number',
			'desc_tip'    => true,
			'description' => __('Maximum number of pages in the PDF content file', 'woocommerce'),
			'value' => $pdf_upload_required == 'yes' ? get_post_meta($variationId, 'pdf_max_pages', true) : "",
			'custom_attributes' => $pdf_fields_readonly,
		));

		echo '</div>';
	}

	/**
	 * Persists custom input fields
	 * 
	 * @param int  $loop An interator to give each input field a unique name
	 * @param int  $variation_id Id for the current variation
	 */
	public function ppi_persist_custom_field_variations($variation_id, $i)
	{
		$f2d_sku_components = $_POST['f2d_sku_components'][$i];
		$f2d_artcd = $_POST['f2d_artcd'][$i];
		$template_id = $_POST['template_id'][$i];
		$variant_code = $_POST['variant_code'][$i];
		$custom_variant_add_to_cart_label = $_POST['custom_variation_add_to_cart_label'][$i];
		$call_to_order = isset($_POST['call_to_order']) ? 'yes' : 'no';
		$pdf_upload_required = isset($_POST['pdf_upload_required']) ? 'yes' : 'no';
		$pdf_width_mm = $_POST['pdf_width_mm'][$i];
		$pdf_height_mm = $_POST['pdf_height_mm'][$i];
		$pdf_min_pages = $_POST['pdf_min_pages'][$i];
		$pdf_max_pages = $_POST['pdf_max_pages'][$i];
		$price_per_page = $_POST['price_per_page'][$i];
		$base_number_of_pages = $_POST['base_number_of_pages'][$i];
		$cart_price = $_POST['cart_price'][$i];
		$cart_units = $_POST['cart_units'][$i];
		$unit_code = $_POST['unit_code'][$i];

		if (isset($f2d_sku_components)) update_post_meta($variation_id, 'f2d_sku_components', esc_attr($f2d_sku_components));
		if (isset($f2d_artcd)) update_post_meta($variation_id, 'f2d_artcd', esc_attr($f2d_artcd));
		if (isset($template_id)) update_post_meta($variation_id, 'template_id', esc_attr($template_id));
		if (isset($variant_code)) update_post_meta($variation_id, 'variant_code', esc_attr($variant_code));
		if (isset($custom_variant_add_to_cart_label)) update_post_meta($variation_id, 'custom_variation_add_to_cart_label', esc_attr($custom_variant_add_to_cart_label));
		if (isset($call_to_order)) update_post_meta($variation_id, 'call_to_order', $call_to_order);
		if (isset($pdf_upload_required)) update_post_meta($variation_id, 'pdf_upload_required', $pdf_upload_required);
		if (isset($pdf_width_mm)) update_post_meta($variation_id, 'pdf_width_mm', esc_attr($pdf_width_mm));
		if (isset($pdf_height_mm)) update_post_meta($variation_id, 'pdf_height_mm', esc_attr($pdf_height_mm));
		if (isset($pdf_min_pages)) update_post_meta($variation_id, 'pdf_min_pages', esc_attr($pdf_min_pages));
		if (isset($pdf_max_pages)) update_post_meta($variation_id, 'pdf_max_pages', esc_attr($pdf_max_pages));
		if (isset($price_per_page)) update_post_meta($variation_id, 'price_per_page', esc_attr($price_per_page));
		if (isset($base_number_of_pages)) update_post_meta($variation_id, 'base_number_of_pages', esc_attr($base_number_of_pages));
		if (isset($cart_price)) update_post_meta($variation_id, 'cart_price', esc_attr($cart_price));
		if (isset($cart_units)) update_post_meta($variation_id, 'cart_units', esc_attr($cart_units));
		if (isset($unit_code)) update_post_meta($variation_id, 'unit_code', esc_attr($unit_code));
	}

	/**
	 * Adds text inputs for the general product attributes, and the simple products
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

		$product = wc_get_product($product_id);
		if ($product->get_type() === 'simple') {
			$call_to_order = get_post_meta($product_id, 'call_to_order', true);
			woocommerce_wp_checkbox(array(
				'id' => 'call_to_order',
				'label'       => __('Call us to order', 'woocommerce'),
				'description' => __('Remove add to cart button and display "Call us to order"', 'woocommerce'),
				'desc_tip'    => true,
				'value' => $call_to_order,
			));
		}

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

		$product = wc_get_product($product_id);
		if ($product->is_type('simple')) {
			woocommerce_wp_text_input(array(
				'id' => 'cart_price',
				'class' => 'short',
				'label' => 'Unit purchase price',
				'type' => 'number',
				'desc_tip'    => true,
				'description' => __('These items are sold as units, not individually', 'woocommerce'),
				'value' => $product_id != null ? get_post_meta($product_id, 'cart_price', true) : ""
			));

			woocommerce_wp_text_input(array(
				'id' => 'cart_units',
				'class' => 'short',
				'label' => 'Unit amount',
				'type' => 'number',
				'desc_tip'    => true,
				'description' => __('Number of items per unit', 'woocommerce'),
				'value' => $product_id != null ? get_post_meta($product_id, 'cart_units', true) : ""
			));

			woocommerce_wp_text_input(array(
				'id' => 'unit_code',
				'class' => 'short',
				'label' => 'Unit code',
				'type' => 'text',
				'desc_tip'    => true,
				'description' => __('The unit code of this item', 'woocommerce'),
				'value' => $product_id != null ? get_post_meta($product_id, 'unit_code', true) : ""
			));

			woocommerce_wp_text_input(array(
				'id' => 'f2d_artcd',
				'class' => 'short',
				'label' => 'F2D article code',
				'type' => 'text',
				'desc_tip'    => true,
				'description' => __('F2D article code', 'woocommerce'),
				'value' => $product_id != null ? get_post_meta($product_id, 'f2d_artcd', true) : ""
			));
		}
	}

	/**
	 * Persists general custom input fields
	 */
	public function ppi_persist_custom_parent_attributes($post_id)
	{
		$custom_add_to_cart_label = $_POST['custom_add_to_cart_label'];
		$customizable_product = isset($_POST['customizable_product']) ? 'yes' : 'no';
		$call_to_order = isset($_POST['call_to_order']) ? 'yes' : 'no';

		if (isset($custom_add_to_cart_label)) update_post_meta($post_id, 'custom_add_to_cart_label', esc_attr($custom_add_to_cart_label));
		if (isset($customizable_product)) update_post_meta($post_id, 'customizable_product', $customizable_product);
		if (isset($call_to_order)) update_post_meta($post_id, 'call_to_order', $call_to_order);
		if (isset($_POST['cart_price'])) update_post_meta($post_id, 'cart_price', $_POST['cart_price']);
		if (isset($_POST['cart_units'])) update_post_meta($post_id, 'cart_units', $_POST['cart_units']);
		if (isset($_POST['unit_code'])) update_post_meta($post_id, 'unit_code', $_POST['unit_code']);
		if (isset($_POST['f2d_artcd'])) update_post_meta($post_id, 'f2d_artcd', $_POST['f2d_artcd']);
	}

	public function displayCustomMetaDataKey($display_key, $meta, $item)
	{
		$currentPage = basename(get_permalink());
		if (substr($currentPage, 0, 10) === '?post_type' && $display_key === '_ppi_imaxel_project_id' && get_class($item) === 'WC_Order_Item_Product') {
			return 'Imaxel project files';
		}
		if (substr($currentPage, 0, 10) === '?post_type' && $display_key === '_content_filename' && get_class($item) === 'WC_Order_Item_Product') {
			return 'Content files';
		}

		return $display_key;
	}

	/**
	 * When orders have custom metadata (Imaxel project ID, content files, etc), this function displays it
	 *
	 * @param string $value
	 * @param object $meta
	 * @param object $item
	 * @return string
	 */
	public function displayCustomMetaDataValue($value, $meta, $item)
	{
		if ($meta->key === '_ppi_imaxel_project_id') {
			$orderId = $item->get_order_id();
			$projectId = $item->get_meta('_ppi_imaxel_project_id');
			$fileName = "{$projectId}/{$orderId}-{$projectId}.zip";
			$url = get_site_url() . "/wp-content/uploads/ppi/imaxelfiles/{$fileName}";
			$isFileReady = is_file(realpath(PPI_IMAXEL_FILES_DIR . '/' . $fileName));

			if ($isFileReady) return '<a href="' . $url . '" download>' . $fileName . ' (' . round(filesize(realpath(PPI_IMAXEL_FILES_DIR . '/' . $fileName)) / 1024, 2) . ' kB)</a>';
			return '<i style="color: red; font weight="700";>files are still syncing - please check again in 5 minutes</i>';
		}
		if ($meta->key === '_content_filename') {
			$file = $item->get_meta('_content_filename');
			$url = get_site_url() . '/wp-content/uploads/ppi/content/' . $file;
			return '<a href="' . $url . '" download>content-files (' . round(filesize(realpath(PPI_UPLOAD_DIR . '/' . $file)) / 1024, 2) . ' kB)</a>';
		}

		return $value;
	}

	/**
	 * Displays custom tracking information
	 *
	 * @param object $order
	 * @return void
	 */
	public function displayTrackingInformation($order)
	{
		$trackingData = $order->get_meta('f2d_tracking_data');
		echo '<h3>Tracking numbers</h3>';
		if (empty($trackingData)) {
			echo 'No tracking information available';
			return;
		}
		$decodedTrackingData = json_decode($trackingData, true);

		foreach ($decodedTrackingData as $trackingObject) {
			echo '<a style="text-decoration: underline;" href="' . $trackingObject['url'] . '" target="blank">' . $trackingObject['number'] . '</a><br>';
		}
	}

	/**
	 * Adds tracking data section to an order details page
	 *
	 * @param array $columns
	 * @return array
	 */
	public function ppiAddTrackingDataColumnToOrderOverview($columns)
	{
		$columns['tracking'] = 'Tracking numbers';

		return $columns;
	}

	public function ppiAddTrackingDataToOrderOverview($column)
	{
		global $post;

		$order = wc_get_order($post->ID);
		$trackingData = json_decode($order->get_meta('f2d_tracking_data'), true);

		if (empty($trackingData)) return;

		$trackingNumbers = array_map(function ($e) {
			return $e['number'];
		}, $trackingData);

		if ($column == 'tracking') {
			echo trim(implode(',', $trackingNumbers), ',');
		}
	}

	/**
	 * Displays a mini-form in the order detail page, under the billing details
	 * This allows an administrator to save a Fly2Data customer number via JavaScript/ajax
	 *
	 * @param object $order
	 * @return void
	 */
	public function displayFly2DataCustomerNumberDiv($order)
	{
		$currentF2dCustomerNumber = get_user_meta($order->get_user_id(), 'f2d_custnr', true);

		$f2dCustomerNumberField = '<p class="form-field form-field-wide"></p>'
			. '<label for="f2d_cust">F2D customer number:</label>'
			. '<div>'
			. '<input type="text" name="f2d_cust" id="f2d_cust" value="' . $currentF2dCustomerNumber . '"  style="display: inline !important;">'
			. '<div style="display: inline !important;">'
			. '<button id="save-f2d-custnr" class="button button-primary">'
			. 'Save to WooCommerce user'
			. '<span id="ppi-admin-loading" class="dashicons dashicons-update rotate ppi-hidden"></span>'
			. '</button>'
			. '</div>'
			. '<div id="f2d-error" class="ppi-hidden"></div>'
			. '</div>';

		echo $f2dCustomerNumberField;
	}

	/**
	 * The function that "order-ui.js" calls via ajax
	 */
	public function save_f2d_custnr()
	{
		$orderNumber = $_POST['orderNumber'];
		$fly2DataCustomerNumber = $_POST['fly2DataCustomerNumber'];

		$order = wc_get_order($orderNumber);
		if (!$order) {
			$response['status'] = 'error';
			$response['message'] = 'No order found for ' . $orderNumber;
			wp_send_json($response);
			wp_die();
		}

		$orderUserId = $order->get_user_id();

		try {
			update_user_meta($orderUserId, 'f2d_custnr', $fly2DataCustomerNumber);
		} catch (\Throwable $th) {
			$response['status'] = 'error';
			$response['message'] = "Error saving {$fly2DataCustomerNumber} to user {$orderUserId}";
			$response['error'] = $th->getMessage();
			wp_send_json($response);
			wp_die();
		}

		$response['status'] = 'success';
		$response['user'] = $orderUserId;
		wp_send_json($response);
		wp_die();
	}

	/**
	 * Display custom user data in admin > user profile page
	 *
	 * @param object $user
	 * @return void
	 */
	public function displayCustomDataInUserDetail($user)
	{
		$f2dCustomerNumber = get_user_meta($user->get('ID'), 'f2d_custnr', true);
		$output = '<h2>Fly2Data</h2>'
			. '<table class="form-table" id="fieldset-billing">'
			. '<tbody><tr>'
			. '<th>'
			. '<label for="f2d_custnr">F2D customer number</label>'
			. '</th>'
			. '<td>'
			. '<input type="text" name="f2d_custnr" id="f2d_custnr" value="' . $f2dCustomerNumber . '" class="regular-text">'
			. '</tbody></table>';

		echo $output;
	}

	/**
	 * Save custom user data in admin > user profile page
	 *
	 * @param int $userId
	 * @return void
	 */
	public function saveCustomDataInUserDetail($userId)
	{
		if (isset($_POST['f2d_custnr'])) {
			update_user_meta($userId, 'f2d_custnr', $_POST['f2d_custnr']);
		}
	}
}
