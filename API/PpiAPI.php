<?php

namespace PelemanPrintpartnerIntegrator\API;

use Automattic\WooCommerce\Client;

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

	/**	
	 * Register get processing orders endpoint
	 */
	public function registerGetProcessingOrdersEndpoint()
	{
		register_rest_route('ppi/v1', '/ordersprocessing', array(
			'methods' => 'GET',
			'callback' => array($this, 'getProcessingOrders'),
			'permission_callback' => '__return_true'
		));
	}

	public function getProcessingOrders()
	{
		global $wpdb;
		$ordersWithStatusProcessing = $wpdb->get_results('SELECT ID from ' . $wpdb->prefix . 'posts WHERE post_type = \'shop_order\' AND post_status = \'wc-processing\';');

		wp_send_json($ordersWithStatusProcessing);
	}

	/**	
	 * Register custom get orders endpoint
	 */
	public function registerGetOrderEndpoint()
	{
		register_rest_route('ppi/v1', '/order(?:/(?P<order>\d+))?', array(
			'methods' => 'GET',
			'callback' => array($this, 'getOrder'),
			'args' => array('order'),
			'permission_callback' => '__return_true'
		));
	}

	public function getOrder($request)
	{
		$orderId = $request['order'];

		$order = wc_get_order($orderId);

		if ($order === false) wp_send_json(['error' => "No order found for id {$orderId}"], 404);

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
			$orderObject = $api->get($endpoint);

			foreach ($orderObject->line_items as $lineItem) {
				foreach ($lineItem->meta_data as $meta_data) {
					if ($meta_data->key === '_ppi_imaxel_project_id') {
						$lineItem->imaxel_files = $imaxel_files[$meta_data->value];
					}
				}
			}
			wp_send_json([$orderObject], 200);
		} catch (\Throwable $th) {
			wp_send_json(['error' => "Error adding files to response for order id {$orderId}"], 404);
		}
	}
}
