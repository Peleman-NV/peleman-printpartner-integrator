<?php

namespace PelemanPrintpartnerIntegrator\API;

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
		return wp_send_json(['godverdomme' => 'it\'s me!']);
	}
}
