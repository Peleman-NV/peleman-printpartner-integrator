<?php

namespace PelemanPrintpartnerIntegrator\API;

use Automattic\WooCommerce\Client;
use PelemanPrintpartnerIntegrator\Services\ImaxelService;
use DateTime;
use ZipArchive;

class PpiAPI
{
	private $logFile = PPI_LOG_DIR . '/imaxelFileDownloader.txt';
	private $shop;

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
		$this->shop = get_option('ppi-imaxel-shop-code');
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

	/**	
	 * Register check pending orders endpoint
	 * This API route is called by the cronjob every 1-3 minutes to:
	 * - check where any Imaxel orders are pending
	 * - if so, download the files and mark the order as downloaded
	 * In dev this route has to be called manually
	 */
	public function registerCheckPendingOrdersEndpoint()
	{
		register_rest_route('ppi/v1', '/pendingorders', array(
			'methods' => 'GET',
			'callback' => array($this, 'checkPendingOrders'),
			'permission_callback' => '__return_true'
		));
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

	/**	
	 * Register add Fly2Data customer number to WP user meta
	 */
	public function registerAddCustomerMetaDataEndpoint()
	{
		register_rest_route('ppi/v1', '/user-meta', array(
			'methods' => 'POST',
			'callback' => array($this, 'addCustomerMetaData'),
			'args' => array('order'),
			'permission_callback' => '__return_true'
		));
	}

	public function checkPendingOrders()
	{
		$imaxel = new ImaxelService();
		$response = $imaxel->get_pending_orders();
		$pendingOrders = json_decode($response['body']);

		if (count($pendingOrders) === 0) {
			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': No pending orders' . PHP_EOL, 3,  $this->logFile);
			wp_send_json(array('response' => 'No pending orders'), 200);
		}

		$response = [];
		$projects = [];

		foreach ($pendingOrders as $order) {
			$orderId = $order->id;
			$shop = $order->checkout->shop->code;
			$projectId = $order->jobs[0]->project->id;
			$product = $order->jobs[0]->product->variants[0]->name->default;
			$wooCommerceOrderId = str_replace('WC order ID: ', '', $order->notes);
			// extract file URLs from files key
			$filesCollection = array_map(function ($files) {
				return $files->url;
			}, $order->files);

			if ($shop !== $this->shop) {
				$projects[$projectId] = [
					'Shop' => $shop,
					'Project ID' => $projectId,
					'Imaxel order ID' => $orderId,
					'WooCommerce order ID' => $wooCommerceOrderId,
					'Processed' => 'no'
				];
				$now =  new DateTime('NOW');
				error_log($now->format('c') . ': order ' . $orderId . ' (project ' . $projectId . ') not for this shop' . PHP_EOL, 3,  $this->logFile);
				continue;
			}

			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': downloading ' . count($filesCollection) . ' files for project ' . $projectId . PHP_EOL, 3,  $this->logFile);

			$this->downloadFiles($filesCollection, $projectId, $wooCommerceOrderId);

			$projects[$projectId] = [
				'Shop' => $shop,
				'Product' => $product,
				'Project ID' => $projectId,
				'Imaxel order ID' => $orderId,
				'WooCommerce order ID' => $wooCommerceOrderId,
				'files' => count($filesCollection),
				'Processed' => 'yes'
			];

			$imaxel->mark_order_as_downloaded($orderId);
			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': marked WC order ' . $wooCommerceOrderId . ' (Imaxel order: ' . $orderId . ') as downloaded.' . PHP_EOL, 3,  $this->logFile);
		}

		$response['result'] = 'Processed ' . count($pendingOrders) . ' orders';
		$response['orderData'] = $projects;
		wp_send_json($response, 200);
	}

	private function downloadFiles($files, $projectId, $orderId)
	{
		$downloadFolder = PPI_IMAXEL_FILES_DIR . "/{$projectId}";
		$this->createOrderFolder($downloadFolder);

		$fileName = 0;
		$downloadedFiles = [];
		foreach ($files as $file) {
			$ext = substr($file, strrpos($file, '.'));
			$fileName = str_pad($fileName, 5, '0', STR_PAD_LEFT);
			$fullPath = "{$downloadFolder}/{$fileName}{$ext}";
			$downloadedFiles[] = $fullPath;
			file_put_contents($fullPath, file_get_contents($file));

			$now =  new DateTime('NOW');
			error_log($now->format('c') . ': downloaded file ' . $file . PHP_EOL, 3,  $this->logFile);

			$fileName++;
		}
		$this->zipAllFiles($projectId, $orderId, $downloadFolder, $downloadedFiles);

		return true;
	}

	private function zipAllFiles($projectId, $orderId, $downloadFolder, $files)
	{
		$zip = new ZipArchive();
		$zip->open("{$downloadFolder}/{$orderId}-{$projectId}.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);

		foreach ($files as $file) {
			$zip->addFile($file, basename($file));
		}

		// check for content files
		if ($this->projectHasContentUpload($projectId !== false)) {
			$contentFiles = $this->getContentFile($projectId);
			foreach ($contentFiles as $file) {
				if (!$zip->addFile($file, basename($file))) {
				};
			}
		}
		$zip->close();

		// delete all files after they've been zipped
		foreach ($files as $file) {
			unlink($file);
		}
	}

	private function projectHasContentUpload($projectId)
	{
		global $wpdb;
		$table_name = PPI_USER_PROJECTS_TABLE;
		$result =  $wpdb->get_results("SELECT content_filename FROM {$table_name} WHERE project_id = {$projectId}");

		if ($result === null) return false;
		return $result[0]->content_filename;
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

			return [
				'status' => 'success',
				'message' => 'folder exists - cleared files'
			];
		} else {
			if (mkdir($downloadFolder, 0777)) {
				$now =  new DateTime('NOW');
				error_log($now->format('c') . ': created folder' . PHP_EOL, 3,  $this->logFile);

				return [
					'status' => 'success',
					'message' => 'created folder'
				];
			} else {
				$now =  new DateTime('NOW');
				error_log($now->format('c') . ': folder not created' . PHP_EOL, 3,  $this->logFile);

				wp_send_json([
					'status' => 'error',
					'message' => 'folder not created'
				]);
			}
		}
	}

	private function getContentFile($projectId)
	{
		$files = array_slice(scandir(PPI_UPLOAD_DIR . '/' . $projectId), 2);

		$fullPathFiles = [];
		foreach ($files as $file) {
			$fullPathFiles[] = PPI_UPLOAD_DIR . '/' . $projectId . '/' . $file;
		}

		return $fullPathFiles;
	}

	private function getProcessingOrders()
	{
		global $wpdb;
		$ordersWithStatusProcessing = $wpdb->get_results("SELECT p.ID as ID from {$wpdb->prefix}posts p INNER JOIN {$wpdb->prefix}postmeta pm on p.ID = pm.post_id  WHERE post_type = 'shop_order' AND post_status = 'wc-processing' GROUP BY ID");

		if (empty($ordersWithStatusProcessing)) {
			wp_send_json(['message' => 'No order with status "processing".'], 200);
			die();
		}

		$ordersResponse = array_map(function ($e) {
			return $this->getOrderData($e->ID);
		}, $ordersWithStatusProcessing);

		wp_send_json($ordersResponse, 200);
		die();
	}

	private function getOrderData($orderId)
	{
		$order = wc_get_order($orderId);

		return [
			'ID' => $orderId,
			'order_created' => $order->get_date_created()->date("Y-m-d H:i:s"),
			'order_total' => $order->get_total(),
			'billing_company' => $order->get_billing_company(),
			'billing_customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
		];
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
			$imaxel_files[$imaxelProjectId] = [
				'fileLink' => get_site_url() . "/wp-content/uploads/ppi/imaxelfiles/{$fileName}",
				'fileName' => $fileName
			];
		}

		try {
			$api = $this->apiClient();
			$endpoint = 'orders/' . $orderId;
			$orderObject = (object) $api->get($endpoint);

			// add F2D customer number
			$orderObject->f2d_customer_number = intval(get_user_meta($order->get_user_id(), 'f2d_custnr', true));

			// add order language
			$order = wc_get_order($orderId);
			$orderLanguage  = $order->get_meta('wpml_language');
			$orderObject->language_code = !empty($orderLanguage) ? $orderLanguage : 'en';

			// add files and number of pages as metadata to line items of order response
			// add f2d data to line item
			foreach ($orderObject->line_items as $lineItem) {
				$variationId = $lineItem->variation_id;
				$f2dData =  get_post_meta($variationId, 'f2d_sku_components', true);
				if (!empty($f2dData)) $lineItem->f2d_sku_components = $f2dData;

				$lineItem->files = [];

				foreach ($lineItem->meta_data as $meta_data) {
					if ($meta_data->key === '_ppi_imaxel_project_id') {
						$imaxelProjectId = $meta_data->value;
						$result = $this->projectHasContentUpload($imaxelProjectId);
						// if content was uploaded by user
						if ($result->content_pages !== null && !empty($result->content_pages) && $result->content_pages > 0) {
							$lineItem->number_of_pages = $result->content_pages;
						}
						if ($result->content_filename !== null && !empty($result->content_filename)) {
							$lineItem->files[] = [
								'file_name' => get_site_url() . '/wp-content/uploads/ppi/content/' . $result->content_filename . '/content.pdf',
								'file_size_in_bytes' => filesize(realpath(PPI_UPLOAD_DIR . '/' . $result->content_filename)),
								'type' => 'Content'
							];
						}
						$lineItem->files[] = [
							'file_name' => $imaxel_files[$meta_data->value]['fileLink'],
							'file_size_in_bytes' => filesize(realpath(PPI_IMAXEL_FILES_DIR . '/' . $imaxel_files[$meta_data->value]['fileName'])),
							'type' => 'Imaxel'
						];
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
		$now =  new \DateTime('NOW', new \DateTimeZone('Europe/Brussels'));
		error_log($now->format('c') . ' ' . print_r('POST param: ' . $request['order'], true) . PHP_EOL, 3, __DIR__ . '/completeOrderLog.txt');

		$orderId = $request['order'];
		$order = wc_get_order($orderId);
		$response['order'] = $orderId;

		$now =  new \DateTime('NOW', new \DateTimeZone('Europe/Brussels'));
		error_log($now->format('c') . ' ' . print_r('Woocommerce order ID: ' . $order->get_id(), true) . PHP_EOL, 3, __DIR__ . '/completeOrderLog.txt');

		if (!$order) {
			$now =  new \DateTime('NOW', new \DateTimeZone('Europe/Brussels'));
			error_log($now->format('c') . ' ' . print_r('Order ' . $orderId . ' not found - sending 400', true) . PHP_EOL, 3, __DIR__ . '/completeOrderLog.txt');
			$response['status'] = 'error';
			$response['message'] = 'No order found';
			$statusCode = 400;
			wp_send_json($response, $statusCode);
			die();
		}
		$wcOrderId = $order->get_id();

		if ($order->get_status() !== 'processing') {
			$now =  new \DateTime('NOW', new \DateTimeZone('Europe/Brussels'));
			error_log($now->format('c') . ' ' . print_r('409 - Status order ' . $wcOrderId . ' not \'processing\' (currently \'' . $order->get_status() . '\')', true) . PHP_EOL, 3, __DIR__ . '/completeOrderLog.txt');
			$response['status'] = 'error';
			$response['message'] = 'Order status is not processing';
			$response['current_order_status'] = $order->get_status();
			$response['order_id'] = $orderId;
			$statusCode = 409;
			wp_send_json($response, $statusCode);
			die();
		}

		if ($order->update_status('completed', 'Updated by F2D', '') === false) {
			$now =  new \DateTime('NOW', new \DateTimeZone('Europe/Brussels'));
			error_log($now->format('c') . ' ' . print_r('500 - set order ' . $wcOrderId . ' status to completed failed', true) . PHP_EOL, 3, __DIR__ . '/completeOrderLog.txt');
			$response['message'] = 'set order ' . $wcOrderId . ' status to \'completed\' failed';
			$response['status'] = 'error';
			$statusCode = 500;
			wp_send_json($response, $statusCode);
			die();
		} else {
			$now =  new \DateTime('NOW', new \DateTimeZone('Europe/Brussels'));
			error_log($now->format('c') . ' ' . print_r('200 - set order ' . $wcOrderId . ' status to completed is successful', true) . PHP_EOL, 3, __DIR__ . '/completeOrderLog.txt');
			$response['status'] = 'success';
			$response['message'] = 'order status changed from \'processing\' to \'completed\'';
			$statusCode = 200;
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

		$trackingDataObject = [
			'number' => $body['f2d_tracking_data'],
			'url' => $body['f2d_tracking_url'],
			'date' => $body['f2d_tracking_date']
		];

		$currentTrackingData = $order->get_meta('f2d_tracking_data');
		$decodedTrackingData = json_decode($currentTrackingData, true);

		// if it exists already, update, else add
		$trackingDataIdentifier = array_column($decodedTrackingData, 'number');
		if (in_array($trackingDataObject['number'], $trackingDataIdentifier)) {
			$response['message'] = "updated existing tracking data for order $orderId";
			$flippedTrackingDataIdentifier = array_flip($trackingDataIdentifier);
			$keyToUpdate = $flippedTrackingDataIdentifier[$trackingDataObject['number']];
			$decodedTrackingData[$keyToUpdate] = $trackingDataObject;
		} else {
			$response['message'] = "added new tracking data to order $orderId";
			$decodedTrackingData[] = $trackingDataObject;
		}

		$encodedTrackingData = json_encode($decodedTrackingData);

		try {
			update_post_meta($orderId, 'f2d_tracking_data', $encodedTrackingData);
			$response['status'] = 'success';
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

	public function addCustomerMetaData($request)
	{
		$customerId = $request['customer_id'];
		$metaDataKey = $request['usermeta_key'];
		$metaDataValue = $request['usermeta_value'];
		$user = get_user_by('id', $customerId);

		if (gettype($metaDataValue) !== "integer") {
			$response['status'] = 'error';
			$response['message'] = "Value is not an int";
			$statusCode = 400;
			wp_send_json($response, $statusCode);
			die();
		} else if (gettype($user) !== 'object') {
			$response['status'] = 'error';
			$response['message'] = "Cannot find user with WordPress user with ID {$customerId}";
			$statusCode = 409;
			wp_send_json($response, $statusCode);
			die();
		}

		try {
			update_user_meta($customerId, $metaDataKey, $metaDataValue);
			$response['status'] = 'success';
			$response['message'] = "Update metadata key {$metaDataKey} with value {$metaDataValue}";
			$statusCode = 200;
			wp_send_json($response, $statusCode);
			die();
		} catch (\Throwable $th) {
			$response['status'] = 'error';
			$response['message'] = $th->getMessage();
			$statusCode = 500;
			wp_send_json($response, $statusCode);
			die();
		}
	}
}
