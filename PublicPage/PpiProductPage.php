<?php

namespace PelemanPrintpartnerIntegrator\PublicPage;

use PelemanPrintpartnerIntegrator\Services\ImaxelService;
use PelemanPrintpartnerIntegrator\Utils\Helper;
use setasign\Fpdi\Fpdi;

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
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts()
	{
		//	wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/product-page.js', array('jquery'), $this->version, false);
	}

	/**
	 * Localize the Ajax script to pass vars to JavaScript
	 */
	public function enqueue_ajax()
	{
		wp_enqueue_script('ppi-ajax-upload', plugins_url('js/upload-content.js', __FILE__), array('jquery'));
		wp_localize_script(
			'ppi-ajax-upload',
			'ppi_ajax_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('file_upload_nonce')
			)
		);

		wp_enqueue_script('ppi-redirect-to-imaxel-editor', plugins_url('js/redirect-to-editor.js', __FILE__), array('jquery'));
		wp_localize_script(
			'ppi-redirect-to-imaxel-editor',
			'ppi_ajax_redirect',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('editor-redirect-nonce')
			)
		);
	}

	/**
	 * Outputs a form with a file upload
	 */
	public function ppi_output_form($variant)
	{
		// grey out until a variant is chosen
		// once it's chosen, show the params div
		$form = '
        <div class="ppi-upload-form">
            <label for="file-upload">Click here to upload your PDF file</label>
            <input id="file-upload" type="file" accept="application/pdf" name="pdf_upload" style="display: none;">
        </div>
        <div class="upload-parameters hidden">
            <p>Maximum file upload size: 100MB</p>
            <p>PDF page height: 297mm</p>
            <p>PDF page width: 210mm</p>
            <p>Minumum nr of pages: 3</p>
            <p>Maximum nr of pages: 400</p>
        </div>
        <div id="file-upload-validation"></div>';
		echo $form;
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

		return $template;
	}

	/**
	 * Changes the Add to cart button text
	 */
	public function ppi_change_add_to_cart_text_for_imaxel_products()
	{
		global $product;

		$product_id = $product->get_id();
		$parent_wc_product = wc_get_product($product_id);
		if ($parent_wc_product->is_type('variable')) {
			$variants_array = $parent_wc_product->get_children();
			$first_variant = wc_get_product($variants_array[0]);
			$requires_pdf = wc_get_product($first_variant)->get_meta('pdf_upload');
			$is_imaxel_product = wc_get_product($first_variant)->get_meta('template_id');

			if ($requires_pdf != "") {
				add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'ppi_change_add_to_cart_text_for_imaxel_pdf_product'), 10);
			} else if ($is_imaxel_product != "") {
				add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'ppi_change_add_to_cart_text_for_imaxel_product'), 10);
			}
		}
	}

	/**
	 * Changes Add to cart button text for Imaxel products requiring a PDF content file
	 */
	public function ppi_change_add_to_cart_text_for_imaxel_pdf_product()
	{
		return __('Upload PDF and create project', 'woocommerce');
	}

	/**
	 * Changes Add to cart button text for Imaxel products
	 */
	public function ppi_change_add_to_cart_text_for_imaxel_product()
	{
		return __('Create project', 'woocommerce');
	}

	public function upload_content_file()
	{
		check_ajax_referer('file_upload_nonce', '_ajax_nonce');

		if ($_FILES['file']['error']) {
			$response['error'] = $_FILES['file']['error'];
			$response['message'] = "Error encountered while uploading your file.  Please try again with a different one.";
			$this->return_response($response);
		}

		$max_file_upload_size = (int)(ini_get('upload_max_filesize')) * 1024 * 1024;
		if ($_FILES['file']['size'] >= $max_file_upload_size) {
			$response['size'] = $_FILES['file']['size'];
			$response['max_size'] = $max_file_upload_size;
			$response['message'] = "Your file is too large, Please upload a file smaller than 100MB.";
			$this->return_response($response);
		}

		$filename = $_FILES['file']['name'];
		$file_type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if ($file_type != 'pdf') {
			$response['type'] = $file_type;
			$response['message'] = "Please upload a PDF file.";
			$this->return_response($response);
		};

		// TODO pages and size validation
		$pages = 5;
		$format = "A4";

		$helper = new Helper();
		$new_filename = PPI_UPLOAD_DIR . '\\' . "project_id_" . $helper->generate_guid() . '.pdf';
		move_uploaded_file($_FILES['file']['tmp_name'], $new_filename);

		$pdf = new Fpdi();
		$pages = $pdf->setSourceFile($new_filename);

		$response['file']['name'] = $filename;
		$response['file']['tmp'] = $_FILES['file']['tmp_name'];
		$response['file']['format'] = $format;
		$response['file']['pages'] = $pages;
		$response['file']['location'] = $$new_filename;
		$response['message'] = "Successfully uploaded \"" . $filename . "\" (" . $format . ", " . $pages . " pages).";

		$this->return_response($response);
	}


	private function return_response($response)
	{
		wp_send_json($response);
		wp_die();
	}

	function redirect_to_imaxel_editor()
	{
		check_ajax_referer('editor-redirect-nonce', '_ajax_nonce');

		$variant_id = $_POST['variant_id'];
		$template_id =  wc_get_product($variant_id)->get_meta('template_id');
		$variant_code = wc_get_product($variant_id)->get_meta('variant_code');

		$imaxel = new ImaxelService();
		$create_project_response = $imaxel->create_project($template_id, $variant_code);

		$encoded_response = json_decode($create_project_response['body']);
		$new_project_id = $encoded_response->id;

		$user_id = get_current_user_id();

		$this->insert_project($user_id, $new_project_id, $variant_id);

		$editor_url = $imaxel->get_editor_url($new_project_id, 'https://devshop.peleman.com', 'https://devshop.peleman.com/?add-to-cart=' . $variant_id);

		$response['url'] = $editor_url;
		$response['type'] = 'success';

		$this->return_response($response);
	}

	/**
	 * Inserts project into database
	 *
	 * @param Int $user_id
	 * @param Int $project_id
	 * @param Int $product_id
	 */
	private function insert_project($user_id, $project_id, $product_id)
	{
		global $wpdb;
		$table_name = PPI_USER_PROJECTS_TABLE;
		$wpdb->insert($table_name, array('user_id' => $user_id, 'project_id' => $project_id, 'product_id' => $product_id));
	}
}
