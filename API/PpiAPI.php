<?php

namespace PelemanPrintpartnerIntegrator\API;

use Automattic\WooCommerce\Client;
use PelemanPrintpartnerIntegrator\Services\ImaxelService;

class PpiAPI
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
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts()
	{
	}

	/**
	 * Create an API client to handle uploads
	 */
	private function apiClient()
	{
		$siteUrl = get_site_url();
		return new Client(
			$siteUrl,
			get_option('ppi-wc-key'),
			get_option('ppi-wc-secret'),
			[
				'wp_api' => true,
				'version' => 'wc/v3'
			]
		);
	}

	private function getProcessingOrders()
	{
		global $wpdb;
		$ordersWithStatusProcessing = $wpdb->get_results("SELECT p.ID as ID, pm.meta_value as date_paid from {$wpdb->prefix}posts p INNER JOIN {$wpdb->prefix}postmeta pm on p.ID = pm.post_id  WHERE post_type = 'shop_order' AND post_status = 'wc-processing' AND pm.meta_key = '_date_paid';");

		foreach ($ordersWithStatusProcessing as $order) {
			$date = new \DateTime();
			$date->setTimestamp($order->date_paid)->setTimezone(new \DateTimeZone('Europe/Brussels'));
			$order->date_paid = $date->format('Y-m-d H:i:s');
		}

		wp_send_json($ordersWithStatusProcessing);
	}

	/**	
	 * Register custom get orders endpoint
	 */
	public function registerGetOrderEndpoint()
	{
		register_rest_route('ppi/v1', '/orders(?:/(?P<order>\d+))?', array(
			'methods' => 'GET',
			'callback' => array($this, 'getOrder'),
			'args' => array('order'),
			'permission_callback' => '__return_true'
		));
	}

	/**	
	 * Register complete order endpoint
	 */
	public function registerCompleteOrderEndpoint()
	{
		register_rest_route('ppi/v1', '/complete-order(?:/(?P<order>\d+))?', array(
			'methods' => 'POST',
			'callback' => array($this, 'completeOrder'),
			'args' => array('order'),
			'permission_callback' => '__return_true'
		));
	}

	/**	
	 * Register add tracking details to order endpoint
	 */
	public function registerAddTrackingToOrderEndpoint()
	{
		register_rest_route('ppi/v1', '/order-tracking(?:/(?P<order>\d+))?', array(
			'methods' => 'POST',
			'callback' => array($this, 'addTrackingInformationToOrder'),
			'args' => array('order'),
			'permission_callback' => '__return_true'
		));
	}

	public function getOrder($request)
	{
		$orderId = $request['order'];

		if ($orderId == '') {
			$this->getProcessingOrders();
		}

		$order = wc_get_order($orderId);

		if ($order === false) wp_send_json(['error' => "No order found for id {$orderId}"], 404);
		// add language code to top level

		$orderItems = $order->get_items();

		$imaxel_files = [];
		foreach ($orderItems as $orderItem) {

			// assuming the content will have an Imaxel project ID (ie. has some customer created content).
			$imaxelProjectId = $orderItem->get_meta('_ppi_imaxel_project_id');
			if ($imaxelProjectId === '') continue;
			$fileName = "{$imaxelProjectId}/{$orderId}-{$imaxelProjectId}.zip";
			$isFileReady = is_file(realpath(PPI_IMAXEL_FILES_DIR . '/' . $fileName));

			if ($isFileReady === false) wp_send_json(['error' => "Files not ready for order id {$orderId}"], 404);

			$imaxel_files[$imaxelProjectId] = get_site_url() . "/wp-content/uploads/ppi/imaxelfiles/{$fileName}";
		}

		try {
			$api = $this->apiClient();
			$endpoint = 'orders/' . $orderId;
			$orderObject = (object) $api->get($endpoint);

			// add order language
			$order = wc_get_order($orderId);
			$orderLanguage  = $order->get_meta('wpml_language');
			$orderObject->language_code = !empty($orderLanguage) ? $orderLanguage : 'en';

			// add files and number of pages as metadata to line items of order response
			foreach ($orderObject->line_items as $lineItem) {
				foreach ($lineItem->meta_data as $meta_data) {
					if ($meta_data->key === '_ppi_imaxel_project_id') {
						$imaxelProjectId = $meta_data->value;
						$result = $this->projectHasContentUpload($imaxelProjectId);
						// if content was uploaded by user
						if ($result->content_pages !== null) {
							$lineItem->number_of_pages = $result->content_pages;
						}
						$lineItem->imaxel_files = $imaxel_files[$meta_data->value];
					}
				}
			}
			wp_send_json($orderObject, 200);
		} catch (\Throwable $th) {
			wp_send_json(['error' => "Error adding files to response for order id {$orderId}"], 404);
		}
	}

	public function completeOrder($request)
	{
		$orderId = $request['order'];
		$order = wc_get_order($orderId);
		$response['order'] = $orderId;

		if (!$order) {
			$response['status'] = 'error';
			$response['message'] = 'No order found';
			$statusCode = 400;
			wp_send_json($response, $statusCode);
			die();
		}

		if ($order->get_status() !== 'processing') {
			$response['status'] = 'error';
			$response['message'] = 'Order status is not processing';
			$response['order_status'] = $order->get_status();
			$statusCode = 400;
			wp_send_json($response, $statusCode);
			die();
		}

		try {
			$order->set_status('completed');
			$order->save();
			$response['status'] = 'success';
			$response['message'] = 'order status changed from \'processing\' to \'completed\'';
			$statusCode = 200;
			wp_send_json($response, $statusCode);
			die();
		} catch (\Throwable $th) {
			$response['status'] = 'error';
			$response['message'] = $th->getMessage();
			$statusCode = 400;
			wp_send_json($response, $statusCode);
			die();
		}
	}

	public function addTrackingInformationToOrder($request)
	{
		$orderId = $request['order'];
		$body = json_decode($request->get_body(), true);

		$order = wc_get_order($orderId);
		$response['order'] = $orderId;

		if (!$order) {
			$response['status'] = 'error';
			$response['message'] = 'No order found';
			$statusCode = 400;
			wp_send_json($response, $statusCode);
			die();
		}

		$trackingNumber = $body['f2d_tracking'];
		$currentTrackingNumbers = $order->get_meta('f2d_tracking');

		$trackingNumbersArray = explode(',', $currentTrackingNumbers);
		// if already in array, do nothing and return

		if (in_array($trackingNumber, $trackingNumbersArray)) {
			echo 'already got this one';
			$response['status'] = 'success';
			$response['message'] = 'tracking number already uploaded';
			$statusCode = 200;
			wp_send_json($response, $statusCode);
			die();
		}

		array_push($trackingNumbersArray, $trackingNumber);

		try {
			update_post_meta($orderId, 'f2d_tracking', implode(',', $trackingNumbersArray));
			$response['status'] = 'success';
			$response['message'] = 'added tracking data to order';
			$statusCode = 200;
			wp_send_json($response, $statusCode);
			die();
		} catch (\Throwable $th) {
			$response['status'] = 'error';
			$response['message'] = $th->getMessage();
			$statusCode = 400;
			wp_send_json($response, $statusCode);
			die();
		}
	}

	private function projectHasContentUpload($projectId)
	{
		global $wpdb;
		$table_name = PPI_USER_PROJECTS_TABLE;
		$result =  $wpdb->get_results("SELECT * FROM {$table_name} WHERE project_id = {$projectId}");

		if ($result[0] === null) return false;
		return $result[0];
	}
}
