<?php

namespace PelemanPrintpartnerIntegrator\PublicPage;

use PelemanPrintpartnerIntegrator\Services\ImaxelService;
use setasign\Fpdi\Fpdi;
use \Imagick;
use DateTime;

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/public
 * @author     Noë Baeten, Jason Goossens, Chris Schippers
 */
class PpiProductPage
{
	private $logFile = PPI_LOG_DIR . '/orderProcessing.txt';

	/**
	 * The ID of this plugin.
	 *
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/product-page-style.css', array(), $this->version, 'all');
		wp_enqueue_style('dashicons');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/variable-product.js', array('jquery'));
	}

	/**
	 * Localize the Ajax script to pass vars to JavaScript
	 */
	public function enqueue_ajax()
	{
		// definite ajax call
		wp_enqueue_script('ppi-variation-information', plugins_url('js/variable-product.js', __FILE__), array('jquery'));
		wp_localize_script(
			'ppi-variation-information',
			'ppi_product_variation_information_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ppi_variation_data')
			)
		);

		wp_enqueue_script('ppi-ajax-upload', plugins_url('js/upload-content.js', __FILE__), array('jquery'));
		wp_localize_script(
			'ppi-ajax-upload',
			'ppi_upload_content_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('file_upload_nonce')
			)
		);
	}

	/**
	 * Outputs the params div
	 */
	public function ppi_output_file_params()
	{
		$maxUploadFileSizeLabel = __('Maximum file upload size', PPI_TEXT_DOMAIN);
		$pDFPageWidth = __('PDF page width', PPI_TEXT_DOMAIN);
		$pDFPageHeight = __('PDF page height', PPI_TEXT_DOMAIN);
		$minimumNumberOfPages = __('Minimum nr of pages', PPI_TEXT_DOMAIN);
		$maximumNumberOfPages = __('Maximum nr of pages', PPI_TEXT_DOMAIN);
		$pricePerPage = __('Price per page', PPI_TEXT_DOMAIN);
		$maxUploadFileSize = "100MB";

		$paramsDiv = "
		<div class='ppi-upload-parameters'>
				<div class='thumbnail-container'>
					<img id='ppi-thumbnail' />
				</div>
				<table>
					<tbody>
						<tr>
							<td>$maxUploadFileSizeLabel</td>
							<td>$maxUploadFileSize</td>
						</tr>
						<tr>
							<td>$pDFPageWidth</td>
							<td class='param-value' id='content-width'></td>
						</tr>
						<tr>
							<td>$pDFPageHeight</td>
							<td class='param-value' id='content-height'></td>
						</tr>
						<tr>
							<td>$minimumNumberOfPages</td>
							<td class='param-value' id='content-min-pages'></td>
						</tr>
						<tr>
							<td>$maximumNumberOfPages</td>
							<td class='param-value' id='content-max-pages'></td>
						</tr>
						<tr>
							<td>$pricePerPage</td>
							<td class='param-value' id='content-price-per-page'></td>
						</tr>
					<tbody>						
				</table>
				";
		echo $paramsDiv;
	}

	/**
	 * Outputs a form with a file upload
	 */
	public function ppi_output_form($variant)
	{
		$uploadButtonLabel = __('Click here to upload your PDF file', PPI_TEXT_DOMAIN);
		$uploadDiv = "
        <div class='ppi-upload-form ppi-hidden'>
            <label class='upload-label upload-disabled' for='file-upload'>{$uploadButtonLabel}</label>
            <input id='file-upload' type='file' accept='application/pdf' name='pdf_upload' style='display: none;'>
        </div>
		<div id='upload-info'></div>";
		echo $uploadDiv;
	}

	/**
	 * Outputs a div with information pertaining to the variant,
	 * more specifically API errors in getting the Imaxel URL
	 */
	public function ppi_output_variant_info()
	{
		$variantInfoDiv = "<div id='variant-info'></div>";
		echo $variantInfoDiv;
	}

	/**
	 * Returns content parameters for a chosen variant
	 */
	private function getVariantContentParameters($variant_id)
	{
		return array(
			'variant' => $variant_id,
			'width' => get_post_meta($variant_id, 'pdf_width_mm', true),
			'height' => get_post_meta($variant_id, 'pdf_height_mm', true),
			'min_pages' => get_post_meta($variant_id, 'pdf_min_pages', true),
			'max_pages' => get_post_meta($variant_id, 'pdf_max_pages', true),
			'price_per_page' => get_post_meta($variant_id, 'price_per_page', true)
		);
	}

	public function get_product_variation_data()
	{
		check_ajax_referer('ppi_variation_data', '_ajax_nonce');

		$variant_id = $_GET['variant'];
		$product_variant = wc_get_product($variant_id);
		$parent_product = wc_get_product($product_variant->get_parent_id());

		$response =	$this->getVariantContentParameters($variant_id);
		$response['status'] = "success";
		$response['variant'] = $variant_id;
		$response['isCustomizable'] = $parent_product->get_meta('customizable_product');
		$response['requiresPDFUpload'] = $product_variant->get_meta('pdf_upload_required');

		// isCustomizable is redundant - the presence of a template_id would be enough
		if ($response['isCustomizable'] === 'no' || $product_variant->get_meta('template_id') === '') {
			$response['customButton'] = false;
		} else {
			$response['customButton'] = true;
			$response['imaxelData'] = $this->get_imaxel_url($variant_id);
		}

		$this->returnResponse($response);
	}

	/**
	 * Override the Woocommerce templates with the plugin templates
	 *
	 * @param string $template      Default template file path.
	 * @param string $template_name Template file slug.
	 * @param string $template_path Template file name.
	 *
	 * @return string The new Template file path.
	 */
	public function ppi_override_wc_templates($template, $template_name, $template_path)
	{
		if ('variation.php' === basename($template)) {
			$template = trailingslashit(plugin_dir_path(__FILE__)) . '../Templates/woocommerce/single-product/add-to-cart/variation.php';
		}
		if ('variation-add-to-cart-button.php' === basename($template)) {
			$template = trailingslashit(plugin_dir_path(__FILE__)) . '../Templates/woocommerce/single-product/add-to-cart/variation-add-to-cart-button.php';
		}
		if ('order-details-customer.php' === basename($template)) {
			$template = trailingslashit(plugin_dir_path(__FILE__)) . '../Templates/woocommerce/order/order-details-customer.php';
		}
		return $template;
	}

	/**
	 * Output tracking information
	 */
	public function ppi_output_order_tracking_information($order)
	{
		$trackingInformation = $order->get_meta('f2d_tracking');
		echo "Tracking number: <a style=\"text-decoration: underline;\" href=\"https://t.17track.net/en#nums=$trackingInformation\" target=\"blank\">$trackingInformation</a>";
	}

	/**
	 * Changes the Add to cart button text
	 */
	public function ppi_change_add_to_cart_text_for_imaxel_products()
	{
		global $product;
		$product_id = $product->get_id();
		$wc_product = wc_get_product($product_id);

		if ($wc_product->get_meta('customizable_product') == 'yes') {
			add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'ppi_change_add_to_cart_text_for_peleman_product'), 10, 2);
		}
	}

	/**
	 * Changes Add to cart button text for Imaxel products requiring a PDF content file
	 */
	public function ppi_change_add_to_cart_text_for_peleman_product($defaultText, $product)
	{
		$product_id = $product->get_id();
		$wc_product = wc_get_product($product_id);

		if ($wc_product->get_meta('custom_add_to_cart_label') != '') {
			$customText = $wc_product->get_meta('custom_add_to_cart_label');
			return __($customText, 'woocommerce');
		}
		return __("Design product", PPI_TEXT_DOMAIN);
	}

	public function get_imaxel_url($variant_id)
	{
		$imaxel_response = $this->getImaxelData($variant_id);

		if ($imaxel_response['status'] == "error") {
			$response['status'] = 'error';
			$response['information'] = $imaxel_response['information'];
			$response['message'] = __('Something went wrong.  Please refresh the page and try again.', PPI_TEXT_DOMAIN);
			$this->returnResponse($response);
		}

		$project_id = $imaxel_response['project_id'];
		$response['buttonText'] = $this->get_add_to_cart_label($variant_id);
		$response['url'] = $imaxel_response['url'];

		$user_id = get_current_user_id();
		$this->insertProject($user_id, $project_id, $variant_id);

		$response['status'] = 'success';
		return $response;
	}

	public function get_add_to_cart_label($variant_id)
	{
		$wc_product = wc_get_product($variant_id);
		$parent_product = wc_get_product($wc_product->get_parent_id());
		if ($wc_product->get_meta('custom_variation_add_to_cart_label') != '') {
			return $wc_product->get_meta('custom_variation_add_to_cart_label');
		} else if ($parent_product->get_meta('custom_add_to_cart_label') != '') {
			return $parent_product->get_meta('custom_add_to_cart_label');
		} else if (get_option('ppi-custom-add-to-cart-label') != '') {
			return get_option('ppi-custom-add-to-cart-label');
		} else {
			return __('Design Product', PPI_TEXT_DOMAIN);
		}
	}

	public function upload_content_file()
	{
		check_ajax_referer('file_upload_nonce', '_ajax_nonce');

		if ($_FILES['file']['error']) {
			$response['status'] = 'error';
			$response['message'] = __('Error encountered while uploading your file.  Please try again with a different one.', PPI_TEXT_DOMAIN);
			$response['error'] = $_FILES['file']['error'];
		}

		$max_file_upload_size = (int)(ini_get('upload_max_filesize')) * 1024 * 1024;
		if ($_FILES['file']['size'] >= $max_file_upload_size) {
			$response['status'] = 'error';
			$response['message'] = __('Your file is too large, Please upload a file smaller than the maximum file upload size.', PPI_TEXT_DOMAIN);
			$response['filesize'] = $_FILES['file']['size'];
			$response['max_size'] = $max_file_upload_size;
		}

		$filename = $_FILES['file']['name'];
		$file_type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if ($file_type != 'pdf') {
			$response['status'] = 'error';
			$response['message'] = __('Please upload a PDF file.', PPI_TEXT_DOMAIN);
			$response['type'] = $file_type;
		};

		$variant_id = $_POST['variant_id'];

		$imaxel_response = $this->getImaxelData($variant_id);
		if ($imaxel_response['status'] == "error") {
			$response['status'] = 'error';
			$response['information'] = $imaxel_response['information'];
			$response['message'] = __('Something went wrong.  Please refresh the page and try again.', PPI_TEXT_DOMAIN);
		}
		$project_id = $imaxel_response['project_id'];
		$response['url'] = $imaxel_response['url'];

		mkdir(realpath(PPI_UPLOAD_DIR) . '/' . $project_id);
		$newFilenameWithPath = realpath(PPI_UPLOAD_DIR) . '/' . $project_id . '/content.pdf';

		try {
			$pdf = new Fpdi();
			$pages = $pdf->setSourceFile($_FILES['file']['tmp_name']);
			$importedPage = $pdf->importPage(1);
			$dimensions = $pdf->getTemplateSize($importedPage);
		} catch (\Throwable $th) {
			$response['status'] = 'error';
			$response['error'] = $th->getMessage();
			$response['message'] = __("We couldn't process your file (possibly due to encryption).  Please use a different PDF file.", PPI_TEXT_DOMAIN);
			$response['file']['name'] = $filename;
			$response['file']['tmp'] = $_FILES['file']['tmp_name'];
			$response['file']['filesize'] = $_FILES['file']['size'];

			$this->returnResponse($response);
		}

		// page & dimension validation
		$variant = $this->getVariantContentParameters($variant_id);
		if ($variant['min_pages'] != "" && $pages < $variant['min_pages']) {
			$response['status'] = 'error';
			$response['file']['pages'] = $pages;
			$response['message'] = __("Your file has too few pages.", PPI_TEXT_DOMAIN);
		}
		if ($variant['max_pages'] != "" && $pages > $variant['max_pages']) {
			$response['status'] = 'error';
			$response['file']['pages'] = $pages;
			$response['message'] = __("Your file has too many pages.", PPI_TEXT_DOMAIN);
		}
		// precision of 1mm

		$precision = 0.5;
		if (($variant['width'] != "" && !$this->roundedNumberInRange($dimensions['width'], $variant['width'], $precision))
			|| ($variant['height'] != "" && !$this->roundedNumberInRange($dimensions['height'], $variant['height'], $precision))
		) {
			$response['status'] = 'error';
			$response['file']['width'] = $dimensions['width'];
			$response['file']['height'] = $dimensions['height'];
			$displayWidth = round($dimensions['width'], 1);
			$displayHeight = round($dimensions['height'], 1);
			$response['message'] = __("Your file's dimensions do not match the required dimensions.", PPI_TEXT_DOMAIN);
		}

		// send response
		if ($response['status'] == 'error') {
			$this->returnResponse($response);
		}

		move_uploaded_file($_FILES['file']['tmp_name'], $newFilenameWithPath);

		$newFilenameWithPath = realpath($newFilenameWithPath);

		try {
			$imagick = new Imagick();
			$imagick->readImage($newFilenameWithPath . '[0]');
			$imagick->setImageFormat('jpg');
			$thumbnailWithPath = realpath(PPI_THUMBNAIL_DIR) . '/' . $project_id . '.jpg';
			$imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
			$imagick->setCompressionQuality(25);
			$imagick->scaleImage(150, 0);
			$imagick->writeImage($thumbnailWithPath);
			$response['file']['thumbnail'] = plugin_dir_url(__FILE__) . '../../../uploads/ppi/thumbnails/' . $project_id . '.jpg';
			$response['status'] = 'success';
			$response['message'] = sprintf(__('Successfully uploaded your file "%s" (%d pages).', PPI_TEXT_DOMAIN), $filename, $pages);
		} catch (\Throwable $th) {
			$response['message'] = sprintf(__('Successfully uploaded your file "%s" (%d pages), but we couldn\'t create a preview thumbnail.', PPI_TEXT_DOMAIN), $filename, $pages);
			$response['error'] = $th->getMessage();

			$this->returnResponse($response);
		}

		$response['file']['name'] = $filename;
		$response['file']['tmp'] = $_FILES['file']['tmp_name'];
		$response['file']['location'] = $newFilenameWithPath;
		$response['file']['filesize'] = $_FILES['file']['size'];
		$response['file']['width'] = $dimensions['width'];
		$response['file']['height'] = $dimensions['height'];
		$response['file']['pages'] = $pages;

		$user_id = get_current_user_id();
		$this->insertProject($user_id, $project_id, $variant_id, $newFilenameWithPath, $pages);

		$this->returnResponse($response);
	}

	/**
	 * send a JSON response - used for AJAX calls
	 * 
	 */
	private function returnResponse($response)
	{
		wp_send_json($response);
		wp_die();
	}

	/**
	 * Generate a project ID and Imaxel URL
	 */
	private function getImaxelData($variant_id)
	{
		$variant_id = $_POST['variant_id'] ?? $variant_id;
		$template_id =  wc_get_product($variant_id)->get_meta('template_id');
		$variant_code = wc_get_product($variant_id)->get_meta('variant_code');

		if (empty($template_id) || empty($variant_code)) {
			return array(
				'status' => 'success',
				'url' => 'no_editor_url'
			);
		}

		$imaxel = new ImaxelService();
		$create_project_response = $imaxel->create_project($template_id, $variant_code);

		if ($create_project_response['response']['code'] == 200) {
			$status = 'success';
		} else {
			$status = 'error';
			$information = $create_project_response['body'];
		}
		$backUrl =  explode("?", get_permalink($variant_id), 2)[0];
		$encoded_response = json_decode($create_project_response['body']);
		$project_id = $encoded_response->id;
		$lang = isset($_COOKIE['wp-wpml_current_language']) && $_COOKIE['wp-wpml_current_language'] ? $_COOKIE['wp-wpml_current_language'] : 'en';
		$siteUrl = get_site_url() . '/' . $lang;

		$editorUrl = $imaxel->get_editor_url($project_id, $backUrl, $lang, $siteUrl . '/?add-to-cart=' . $variant_id . '&project=' . $project_id);

		return array(
			'status' => $status,
			'project_id' => $project_id,
			'information' => $information ?? '',
			'url' => $editorUrl
		);
	}

	/**
	 * Inserts project into database
	 *
	 * @param Int $user_id
	 * @param Int $project_id
	 * @param Int $product_id
	 */
	private function insertProject($user_id, $project_id, $product_id, $content_filename = NULL, $pages = NULL)
	{
		global $wpdb;
		$table_name = PPI_USER_PROJECTS_TABLE;
		if ($content_filename != null) {
			$query = array('user_id' => $user_id, 'project_id' => $project_id, 'product_id' => $product_id, 'content_filename' => $content_filename, 'content_pages' => $pages);
		} else {
			$query = array('user_id' => $user_id, 'project_id' => $project_id, 'product_id' => $product_id);
		}

		$wpdb->insert($table_name, $query);

		$now =  new DateTime('NOW');
		error_log($now->format('c') . ": persisted project {$project_id} for user {$user_id} / product {$product_id}" . PHP_EOL, 3,  $this->logFile);
	}

	private function roundedNumberInRange($number, $baseRange, $precision)
	{
		if (round(floatval($number), 2) >= floatval($baseRange) - floatval($precision) && round(floatval($number), 2) <= floatval($baseRange) + floatval($precision)) {
			return true;
		}
		return false;
	}

	/**
	 * Add project ID to cart data
	 */
	public function add_custom_data_to_cart_items($cart_item_data, $product_id)
	{
		if (!isset($_GET['project'])) return $cart_item_data;

		$projectId = esc_attr($_GET['project']);
		$cart_item_data["_ppi_imaxel_project_id"] = $projectId;

		$now =  new DateTime('NOW');
		error_log($now->format('c') . ": added projectID {$projectId} to cart" . PHP_EOL, 3,  $this->logFile);

		return $cart_item_data;
	}

	/**
	 * Add project ID to order line item 
	 */
	public function add_project_to_order_line_item($item, $cart_item_key, $values, $order)
	{
		if (isset($values['_ppi_imaxel_project_id'])) {
			$imaxelProjectId = $values['_ppi_imaxel_project_id'];
			$item->add_meta_data('_ppi_imaxel_project_id', $imaxelProjectId, true);

			$now =  new DateTime('NOW');
			error_log($now->format('c') . ": added projectID {$imaxelProjectId} to order line item" . PHP_EOL, 3,  $this->logFile);
		}
	}

	/**
	 * Get imaxel project files
	 */
	public function createImaxelOrder($orderId, $currentStatus, $newStatus, $order)
	{
		if ($newStatus !== 'processing') return;
		$now =  new DateTime('NOW');
		error_log($now->format('c') . ": order {$orderId} status changed from {$currentStatus} to {$newStatus}" . PHP_EOL, 3,  $this->logFile);

		$wc_order = wc_get_order($orderId);
		$orderItems = $wc_order->get_items();
		if (empty($orderItems)) return;

		foreach ($orderItems as $orderItemId => $orderItem) {
			$imaxelProjectId = wc_get_order_item_meta($orderItemId, '_ppi_imaxel_project_id');
			if (empty($imaxelProjectId)) continue;

			$imaxel = new ImaxelService();
			$createOrderResponse = $imaxel->create_order($imaxelProjectId, $orderId)['body'];

			$now =  new DateTime('NOW');
			error_log($now->format('c') . ": created Imaxel order for ImaxelProjectID {$imaxelProjectId} - WC order item {$orderItemId}" . PHP_EOL, 3,  $this->logFile);
		}
	}

	public function adjustItemPriceForAddedPages($cart)
	{
		foreach ($cart->get_cart() as $cartItem) {
			// price adjustment for items with content uploads
			if (isset($cartItem['variation']['attribute_pa_content-format']) && $cartItem['variation']['attribute_pa_content-format'] != '') {
				$price = $cartItem['data']->get_price();
				$productMetaData = get_post_meta($cartItem['variation_id']);
				$pricePerPage = $productMetaData['price_per_page'][0];
				$pages = $this->getNrOfContentPages($cartItem['_ppi_imaxel_project_id']);

				$price += ($pages * $pricePerPage);
				$cartItem['data']->set_price($price);
			}
		}
	}

	private function getNrOfContentPages($projectId)
	{
		global $wpdb;
		$table_name = PPI_USER_PROJECTS_TABLE;
		$result = $wpdb->get_row("SELECT content_pages FROM {$table_name} WHERE project_id = {$projectId};");

		return $result->content_pages;
	}
}
