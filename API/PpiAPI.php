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
		$ordersWithStatusProcessing = $wpdb->get_results('SELECT ID from ' . $wpdb->prefix . 'posts WHERE post_type = \'shop_order\' AND post_status = \'wc-processing\';');

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

	public function getOrder($request)
	{
		$orderId = $request['order'];

		if ($orderId == '') {
			$this->getProcessingOrders();
		}

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
			$orderObject = (object) $api->get($endpoint);

			// add files and number of pages as metadata to line items of order response
			foreach ($orderObject->line_items as $lineItem) {
				foreach ($lineItem->meta_data as $meta_data) {
					if ($meta_data->key === '_ppi_imaxel_project_id') {
						$imaxelProjectId = $meta_data->value;
						$result = $this->projectHasContentUpload($imaxelProjectId);

						if ($result->content_pages !== null) {
							// if content was uploaded by user
							$lineItem->number_of_pages = intval($result->content_pages);
						} else {
							// if content was downloaded from Imaxel
							// product has veriable # of pages, eg:wedding book
							// or product has faxed # of pages, eg:Photobook Human Colours (7 photosheets)
							$imaxel = new ImaxelService();
							$readProjectResponse = $imaxel->read_project($imaxelProjectId)['body'];
							$decodedResponse = json_decode($readProjectResponse);

							// get variantcode
							$wcVariation = wc_get_product($lineItem->variation_id);
							$productMetaData = ['template_id' => $wcVariation->get_meta('template_id'), 'variant_code' => $wcVariation->get_meta('variant_code')];

							// get template info from response
							$responseTemplate = array_filter($decodedResponse->product->variants, function ($e) use ($productMetaData) {
								return $e->code !== $productMetaData['template_id'];
							});
							// get variant info (that includes pages info) from response
							$responseTemplateParts = array_values(array_filter($responseTemplate[0]->parts, function ($e) {
								return $e->name === 'pages';
							}));

							// get number of sheets
							$pagesObject = $decodedResponse->design->pages;
							// filter only pages, not cover
							$numberOfPages = count(array_filter($pagesObject, function ($e) {
								return $e->partName === "pages";
							}));

							$lineItem->number_of_pages = $numberOfPages * 2;

							// check if very first and very last are discarded (ie. inside sheet of the cover) - this key is not always here...!
							if (isset($responseTemplateParts[0]->output->sheets_processor->discarded_sides) && $responseTemplateParts[0]->output->sheets_processor->discarded_sides === 'first_and_last') {
								$lineItem->number_of_pages -= 2;
							}
						}

						$lineItem->imaxel_files = $imaxel_files[$meta_data->value];
					}
				}
			}
			wp_send_json([$orderObject], 200);
		} catch (\Throwable $th) {
			wp_send_json(['error' => "Error adding files to response for order id {$orderId}"], 404);
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
