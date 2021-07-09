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
		$randomNumber = rand(0, 2000); // prevent caching by adding a 'new' version number on each request
		wp_enqueue_style($this->plugin_name . 'products', plugin_dir_url(__FILE__) . 'css/product-page-style.css', array(), $randomNumber, 'all');
		wp_enqueue_style($this->plugin_name . 'projects', plugin_dir_url(__FILE__) . 'css/projects-page-style.css', array(), $randomNumber, 'all');
		wp_enqueue_style('dashicons');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts()
	{
	}

	/**
	 * Localize the Ajax script to pass vars to JavaScript
	 */
	public function enqueue_ajax()
	{
		$randomNumber = rand(0, 2000); // prevent caching by adding a 'new' version number on each request
		wp_enqueue_script('ppi-variation-information', plugins_url('js/variable-product.js', __FILE__), array('jquery'), $randomNumber);
		wp_localize_script(
			'ppi-variation-information',
			'ppi_product_variation_information_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ppi_variation_data')
			)
		);

		wp_enqueue_script('ppi-ajax-upload', plugins_url('js/upload-content.js', __FILE__), array('jquery'), $randomNumber);
		wp_localize_script(
			'ppi-ajax-upload',
			'ppi_upload_content_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('file_upload_nonce')
			)
		);

		wp_enqueue_script('ppi-ajax-add-to-cart', plugins_url('js/add-to-cart.js', __FILE__), array('jquery'), $randomNumber);
		wp_localize_script(
			'ppi-ajax-add-to-cart',
			'ppi_imaxel_redirection_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('imaxel_redirection_nonce')
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
	 * Outputs a div with information pertaining to the Imaxel redirection,
	 * more specifically errors in getting the Imaxel URL
	 */
	public function ppi_output_redirection_info()
	{
		$redirectionInfoDiv = "<div id='redirection-info'></div>";
		echo $redirectionInfoDiv;
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
		$response['buttonText'] = $this->get_add_to_cart_label($variant_id);

		$this->returnResponse($response);
	}

	/**
	 * Get the Imaxel URL and save the user project to the database
	 */
	public function get_imaxel_redirection()
	{
		check_ajax_referer('imaxel_redirection_nonce', '_ajax_nonce');

		$variant_id = $_GET['variant'];
		$content_file_id = $_GET['content'];

		// if no variant Id present, return
		if ($variant_id === null) {
			$response['status'] = "success";
			$this->returnResponse($response);
		}

		$product_variant = wc_get_product($variant_id);
		$parent_product = wc_get_product($product_variant->get_parent_id());
		$response['isCustomizable'] = $parent_product->get_meta('customizable_product');

		// if not customizable, no need to call Imaxel
		if ($parent_product->get_meta('customizable_product') === 'no') {
			$response['status'] = "success";
			$this->returnResponse($response);
		}

		$imaxel_response = $this->getImaxelData($variant_id);
		if ($imaxel_response['status'] == "error") {
			$response['status'] = 'error';
			$response['information'] = $imaxel_response['information'];
			$response['message'] = __('Something went wrong.  Please refresh the page and try again.', PPI_TEXT_DOMAIN);
			$this->returnResponse($response);
		}

		$project_id = $imaxel_response['project_id'];
		$user_id = get_current_user_id();
		$this->insertProject($user_id, $project_id, $variant_id, $content_file_id);

		$response['url'] = $imaxel_response['url'];
		$response['project-id'] = $project_id;

		$response['status'] = "success";
		$response['variant'] = $variant_id;

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
		$trackingNumbers = $order->get_meta('f2d_tracking');
		$trackingNumbersArray  = explode(',', $trackingNumbers);
		foreach ($trackingNumbersArray as $trackingNumber) {
			echo "<i>Tracking number:</i> <a style=\"text-decoration: underline;\" href=\"https://t.17track.net/en#nums=$trackingNumber\" target=\"blank\">$trackingNumber</a><br>";
		}
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

	// public function get_imaxel_url($variant_id)
	// {
	// 	$imaxel_response = $this->getImaxelData($variant_id);

	// 	if ($imaxel_response['status'] == "error") {
	// 		$response['status'] = 'error';
	// 		$response['information'] = $imaxel_response['information'];
	// 		$response['message'] = __('Something went wrong.  Please refresh the page and try again.', PPI_TEXT_DOMAIN);
	// 		$this->returnResponse($response);
	// 	}

	// 	$project_id = $imaxel_response['project_id'];
	// 	$response['buttonText'] = $this->get_add_to_cart_label($variant_id);
	// 	$response['url'] = $imaxel_response['url'];

	// 	$user_id = get_current_user_id();
	// 	$this->insertProject($user_id, $project_id, $variant_id);

	// 	$response['status'] = 'success';
	// 	return $response;
	// }

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
			return null;
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
			$response['file']['filesize'] = $_FILES['file']['size'];

			$this->returnResponse($response);
		}

		$variant_id = $_POST['variant_id'];

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
			$response['message'] = __("Your file's dimensions do not match the required dimensions.", PPI_TEXT_DOMAIN);
		}

		// send response
		if ($response['status'] == 'error') {
			$this->returnResponse($response);
		}

		$user_id = get_current_user_id();
		$contentFileId = $user_id . '_' . round(microtime(true) * 1000) . '_' . $variant_id;
		mkdir(realpath(PPI_UPLOAD_DIR) . '/' . $contentFileId);
		$newFilenameWithPath = realpath(PPI_UPLOAD_DIR) . '/' . $contentFileId . '/content.pdf';
		move_uploaded_file($_FILES['file']['tmp_name'], $newFilenameWithPath);
		$newFilenameWithPath = realpath($newFilenameWithPath);

		try {
			$imagick = new Imagick();
			$imagick->readImage($newFilenameWithPath . '[0]');
			$imagick->setImageFormat('jpg');
			$thumbnailWithPath = realpath(PPI_THUMBNAIL_DIR) . '/' . $contentFileId . '.jpg';
			$imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
			$imagick->setCompressionQuality(25);
			$imagick->scaleImage(150, 0);
			$imagick->writeImage($thumbnailWithPath);
			$response['file']['thumbnail'] = plugin_dir_url(__FILE__) . '../../../uploads/ppi/thumbnails/' . $contentFileId . '.jpg';
			$response['status'] = 'success';
			$response['message'] = sprintf(__('Successfully uploaded your file "%s" (%d pages).', PPI_TEXT_DOMAIN), $filename, $pages);
		} catch (\Throwable $th) {
			$response['message'] = sprintf(__('Successfully uploaded your file "%s" (%d pages), but we couldn\'t create a preview thumbnail.', PPI_TEXT_DOMAIN), $filename, $pages);
			$response['error'] = $th->getMessage();

			$this->returnResponse($response);
		}

		$response['file']['name'] = $filename;
		$response['file']['content_file_id'] = $contentFileId;
		$response['file']['location'] = $newFilenameWithPath;
		$response['file']['filesize'] = $_FILES['file']['size'];
		$response['file']['width'] = $dimensions['width'];
		$response['file']['height'] = $dimensions['height'];
		$response['file']['pages'] = $pages;

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
				'status' => 'error',
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
	}

	/**
	 * Add nr of pages to project line in database
	 *
	 * @param Int $project_id
	 */
	private function addPagesToProject($project_id, $pages)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'ppi_user_projects';
		// is there content, and does it have pages?  If so, do not overwrite
		$contentWithPagesExists = $wpdb->get_results("SELECT content_filename, content_pages FROM $table_name WHERE project_id = $project_id;");

		if (strpos($contentWithPagesExists[0]->content_filename, 'content')) {
			$now =  new DateTime('NOW');
			error_log($now->format('c') . ": no Imaxel page count added to project {$project_id} - content files page count is already known" . PHP_EOL, 3,  $this->logFile);
			return;
		}

		$query = array('content_pages' => $pages);
		$wpdb->update($table_name, $query, ['project_id' => $project_id]);

		$now =  new DateTime('NOW');
		error_log($now->format('c') . ": added pages to imaxel project {$project_id}" . PHP_EOL, 3,  $this->logFile);
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
	public function add_custom_data_to_order_line_item($item, $cart_item_key, $values, $order)
	{
		$imaxelProjectId = 0;
		if (isset($values['_ppi_imaxel_project_id'])) {
			$imaxelProjectId = $values['_ppi_imaxel_project_id'];
			$item->add_meta_data('_ppi_imaxel_project_id', $imaxelProjectId, true);
		}
		if ($imaxelProjectId !== 0) {
			$uploadedContent = $this->projectHasContentUpload($imaxelProjectId);
			if ($uploadedContent !== null) {
				$item->add_meta_data('_content_filename', $uploadedContent->content_filename, true);
			}
		}
		$now =  new DateTime('NOW');
		error_log($now->format('c') . ": added projectID {$imaxelProjectId} to order line item" . PHP_EOL, 3,  $this->logFile);
	}

	private function projectHasContentUpload($projectId)
	{
		global $wpdb;
		$table_name = PPI_USER_PROJECTS_TABLE;
		$result =  $wpdb->get_results("SELECT * FROM {$table_name} WHERE project_id = {$projectId}");

		if ($result[0] === null) return false;
		return $result[0];
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
			error_log($now->format('c') . ": created Imaxel order for ImaxelProjectID $imaxelProjectId - Order $order - item $orderItemId" . PHP_EOL, 3,  $this->logFile);
		}
	}

	public function adjustItemPriceForAddedPages($cart)
	{

		foreach ($cart->get_cart() as $cartItem) {
			if ($cartItem['variation_id'] === 0) return;
			$cartProduct = wc_get_product($cartItem['variation_id']);
			$baseNumberOfPages = $cartProduct->get_meta('base_number_of_pages');
			$pricePerPage = $cartProduct->get_meta('price_per_page');

			if (isset($baseNumberOfPages) && !empty($baseNumberOfPages) && isset($pricePerPage) && !empty($pricePerPage)) {
				if (empty($cartItem['_ppi_imaxel_project_id'])) return;
				$pages = $this->getNrOfContentPages($cartItem['_ppi_imaxel_project_id']);
				$supplementalPages = $pages - $baseNumberOfPages;

				if ($supplementalPages <= 0) return;

				$price = $cartItem['data']->get_price();
				$price += ($supplementalPages * $pricePerPage);
				$cartItem['data']->set_price($price);
			}
		}

		return $cart;
	}

	private function getNrOfContentPages($projectId)
	{
		global $wpdb;
		$table_name = PPI_USER_PROJECTS_TABLE;
		$result = $wpdb->get_row("SELECT content_pages FROM {$table_name} WHERE project_id = {$projectId};");
		error_log(
			__FILE__ . ': ' . __LINE__ . ' ' . print_r(
				$wpdb->last_query,
				true
			) . PHP_EOL,
			3,
			__DIR__ .
				'/Log.txt'
		);
		error_log(
			__FILE__ . ': ' . __LINE__ . ' ' . print_r(
				$result,
				true
			) . PHP_EOL,
			3,
			__DIR__ .
				'/Log.txt'
		);
		return $result->content_pages;
	}

	public function readImaxelProjectOnReturnFromEditor($passed, $product_id, $quantity, $variation_id = '', $variations = '')
	{
		if (isset($_GET['project']) && !empty($_GET['project'])) {
			$imaxelProjectId = $_GET['project'];
			$imaxel = new ImaxelService();
			$response = json_decode($imaxel->read_project($imaxelProjectId)['body'], true);

			$pages = $this->countPagesInImaxelProject($response['design']['pages']);
			// check if the first and last pages need to be ignored

			foreach ($response['product']['variants'][0]['parts'] as $part) {
				if ($part['name'] === 'pages') {
					if ($part['output']['sheets_processor']['discarded_sides'] === 'first_and_last') $pages -= 2;
				}
			}

			// if there is content, do not overwrite -> see addPagesToProject project
			$this->addPagesToProject($imaxelProjectId, $pages);
		}
		return $passed;
	}

	private function countPagesInImaxelProject($designObject)
	{
		$sheets = array_filter(
			$designObject,
			function ($e) {
				return $e['partName'] === 'pages';
			}
		);

		return count($sheets) * 2;
	}

	public function add_projects_menu_item($items)
	{
		$items['projects'] = 'Projects';
		echo 'hi';
		return $items;
	}

	public function register_projects_endpoint()
	{
		add_rewrite_endpoint('projects',  EP_ROOT | EP_PAGES);
		flush_rewrite_rules();
	}

	public function projects_endpoint_content()
	{
		wc_get_template('/myaccount/projects.php', [], '', plugin_dir_path(__FILE__) . '../Templates/woocommerce');
	}
}
