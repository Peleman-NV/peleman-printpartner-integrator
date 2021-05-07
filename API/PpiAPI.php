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
	public function registerGetProcessingOrderEndpoint()
	{
		register_rest_route('ppi/v1', '/ordersprocessing', array(
			'methods' => 'GET',
			'callback' => array($this, 'getProcessingOrders'),
			'args' => array('page'),
			'permission_callback' => '__return_true'
		));
	}

	public function getProcessingOrders()
	{
		global $wpdb;
		$orderStatusProcessing = $wpdb->get_results('SELECT ID from ' . $wpdb->prefix . 'posts WHERE post_type = \'shop_order\' AND post_status = \'wc-processing\';');

		wp_send_json($orderStatusProcessing);
	}
}
