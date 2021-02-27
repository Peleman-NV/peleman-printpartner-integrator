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
		wp_enqueue_script('ppi-ajax-upload', plugins_url('js/content-upload.js', __FILE__), array('jquery'));
		wp_localize_script(
			'ppi-ajax-upload',
			'ppi_content_upload_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('file_upload_nonce')
			)
		);
		wp_enqueue_script('ppi-imaxel-url', plugins_url('js/no-content-upload.js', __FILE__), array('jquery'));
		wp_localize_script(
			'ppi-imaxel-url',
			'ppi_url_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('imaxel_url_nonce')
			)
		);
	}

	/**
	 * Outputs a form with a file upload
	 */
	public function ppi_output_form($variant)
	{
		// TODO adda preview thumbnail for the PDF's first page
		$form = '
        <div class="ppi-upload-form">
            <label class="upload-label upload-disabled" for="file-upload">Click here to upload your PDF file</label>
            <input id="file-upload" type="file" accept="application/pdf" name="pdf_upload" style="display: none;">
        </div>
        <div id="file-upload-validation"></div>';
		echo $form;
	}

	/**
	 * Outputs the params div
	 */
	public function ppi_output_file_params($variant)
	{
		$paramsDiv = '
			<div class="ppi-upload-parameters">
				<div class="param-line">
					<div class="param-name">
						Maximum file upload size
					</div>
					<div class="param-value">
						100MB
					</div>
				</div>
				<div class="param-line">
					<div class="param-name">
						PDF page height
					</div>
					<div class="param-value">
						297mm
					</div>
				</div>
				<div class="param-line">
					<div class="param-name">
						PDF page width
					</div>
					<div class="param-value">
						210mm
					</div>
				</div>
				<div class="param-line">
					<div class="param-name">
						Maximum nr of pages
					</div>
					<div class="param-value">
						400
					</div>
				</div>
				<div class="param-line">
					<div class="param-name">
						Minimum nr of pages
					</div>
					<div class="param-value">
						3
					</div>
				</div>
			</div>';
		echo $paramsDiv;
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

	public function get_imaxel_url()
	{
		check_ajax_referer('imaxel_url_nonce', '_ajax_nonce');

		$variant_id = $_POST['variant_id'];

		$imaxel_response = $this->getImaxelData($variant_id);
		$project_id = $imaxel_response['project_id'];
		$response['url'] = $imaxel_response['url'];

		$user_id = get_current_user_id();
		$this->insert_project($user_id, $project_id, $variant_id);

		$response['status'] = 'success';
		$this->return_response($response);
	}

	public function upload_content_file()
	{
		check_ajax_referer('file_upload_nonce', '_ajax_nonce');

		if ($_FILES['file']['error']) {
			$response['status'] = 'error';
			$response['message'] = "Error encountered while uploading your file.  Please try again with a different one.";
			$response['error'] = $_FILES['file']['error'];
			$this->return_response($response);
		}

		$max_file_upload_size = (int)(ini_get('upload_max_filesize')) * 1024 * 1024;
		if ($_FILES['file']['size'] >= $max_file_upload_size) {
			$response['status'] = 'error';
			$response['message'] = "Your file is too large, Please upload a file smaller than 100MB.";
			$response['size'] = $_FILES['file']['size'];
			$response['max_size'] = $max_file_upload_size;
			$this->return_response($response);
		}

		$filename = $_FILES['file']['name'];
		$file_type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if ($file_type != 'pdf') {
			$response['status'] = 'error';
			$response['message'] = "Please upload a PDF file.";
			$response['type'] = $file_type;
			$this->return_response($response);
		};

		$variant_id = $_POST['variant_id'];

		$imaxel_response = $this->getImaxelData($variant_id);
		$project_id = $imaxel_response['project_id'];
		$response['url'] = $imaxel_response['url'];

		// TODO pages and size validation
		$pages = 5;
		$format = "A4";

		$helper = new Helper();
		$new_filename = $project_id . '_' . $helper->generate_guid() . '.pdf';
		move_uploaded_file($_FILES['file']['tmp_name'], PPI_UPLOAD_DIR . '/' . $new_filename);

		$pdf = new Fpdi();
		try {
			$pages = $pdf->setSourceFile(PPI_UPLOAD_DIR . '/' . $new_filename);
		} catch (\Throwable $th) {
			$response['status'] = 'error';
			$response['message'] = "File \"" . $filename . "\" uploaded, but we weren't able to check it.<br>Please use a different PDF file.";
			$response['file']['name'] = $filename;
			$response['file']['tmp'] = $_FILES['file']['tmp_name'];
			$response['file']['location'] = $new_filename;
			$response['size'] = $_FILES['file']['size'];
			unlink($new_filename);

			$this->return_response($response);
		}

		$response['status'] = 'success';
		$response['message'] = "Successfully uploaded \"" . $filename . "\" (" . $pages . " pages).";
		$response['file']['name'] = $filename;
		$response['file']['tmp'] = $_FILES['file']['tmp_name'];
		$response['file']['location'] = $new_filename;
		$response['file']['size'] = $_FILES['file']['size'];
		$response['file']['format'] = $format;
		$response['file']['pages'] = $pages;

		$user_id = get_current_user_id();
		$this->insert_project($user_id, $project_id, $variant_id, $new_filename);

		$this->return_response($response);
	}

	/**
	 * send a JSON response - used for AJAX calls
	 * 
	 */
	private function return_response($response)
	{
		wp_send_json($response);
		wp_die();
	}

	/**
	 * Generate a project ID and Imaxel URL
	 */
	private function getImaxelData($variant_id)
	{
		$variant_id = $_POST['variant_id'];
		$template_id =  wc_get_product($variant_id)->get_meta('template_id');
		$variant_code = wc_get_product($variant_id)->get_meta('variant_code');

		$response['template'] = $template_id;
		$response['variant'] = $variant_code;

		$imaxel = new ImaxelService();
		$create_project_response = $imaxel->create_project($template_id, $variant_code);

		$encoded_response = json_decode($create_project_response['body']);
		$project_id = $encoded_response->id;

		return array(
			'project_id' => $project_id,
			'url' => $imaxel->get_editor_url($project_id, 'https://devshop.peleman.com', 'https://devshop.peleman.com/?add-to-cart=' . $variant_id)
		);
	}

	/**
	 * Inserts project into database
	 *
	 * @param Int $user_id
	 * @param Int $project_id
	 * @param Int $product_id
	 */
	private function insert_project($user_id, $project_id, $product_id, $content_filename = NULL)
	{
		global $wpdb;
		$table_name = PPI_USER_PROJECTS_TABLE;
		if ($content_filename != null) {
			$query = array('user_id' => $user_id, 'project_id' => $project_id, 'product_id' => $product_id, 'content_filename' => $content_filename);
		} else {
			$query = array('user_id' => $user_id, 'project_id' => $project_id, 'product_id' => $product_id);
		}
		$wpdb->insert($table_name, $query);
	}
}
