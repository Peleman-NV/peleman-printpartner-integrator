<?php

namespace PelemanPrintpartnerIntegrator\Services;

use DateTime;
use DateInterval;

class ImaxelService
{
    private $private_key;
    private $public_key;
    private $shop_code;
    private $base_imaxel_api_url = "https://services.imaxel.com:443/api/v3/";
    private $logFile = PPI_LOG_DIR . '/imaxelServiceLog.txt';

    public function __construct()
    {
        $this->private_key = get_option('ppi-imaxel-private-key');
        $this->public_key = get_option('ppi-imaxel-public-key');
        $this->shop_code =  get_option('ppi-imaxel-shop-code');
    }

    /**
     * Creates a base 64 encoded policy JSON
     * 
     * @param array contextArray This is the context of the call, ie product code, project id, etc
     * @return string
     */
    private function generate_base64_encoded_policy($context_array = null)
    {
        $date = new DateTime('NOW');
        $date->add(new DateInterval('PT10M'));
        $policy_array = array();

        if ($context_array == null) {
            $policy_array = array(
                'publicKey' => $this->public_key,
                'expirationDate' => $date->format('c')
            );
        } else {
            $policy_array = array_merge($context_array, array(
                'publicKey' => $this->public_key,
                'expirationDate' => $date->format('c')
            ));
        }

        return base64_encode(json_encode($policy_array, JSON_UNESCAPED_SLASHES));
    }
    /**
     * Creates a hash signed base 64 encoded policy JSON
     * 
     * @param string 
     * @return string
     */
    private function generate_signed_policy($policy_json)
    {
        $signed_policy = hash_hmac("SHA256", $policy_json, utf8_encode($this->private_key), true);
        $hashed_signed_policy = base64_encode($signed_policy);

        return $hashed_signed_policy;
    }

    /**
     * Exectues a CURL request
     * 
     * @param string $url
     * @param string $requestType
     * @param string $jsonRequest
     * @return string
     */
    public function get_response($url, $request_type, $json_request = null)
    {
        if ($request_type === 'POST') {
            $response = wp_remote_post(
                $url,
                array(
                    'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
                    'method' => 'POST',
                    'body' => $json_request
                )
            );
        } else if ($request_type === 'GET') {
            $response = wp_safe_remote_get($url);
        }

        return $response;
    }

    /**
     * Create imaxel project
     * 
     * @param array $template_id
     * @return string
     */
    public function create_project($template_id, $variant_code)
    {
        $url = $this->base_imaxel_api_url . 'projects';
        $context_array = array('productCode' => $template_id, 'variantsCodes' => array($variant_code), 'defaultVariantCode' => $variant_code);
        $base_64_encoded_policy_json = $this->generate_base64_encoded_policy($context_array);
        $signed_policy = $this->generate_signed_policy($base_64_encoded_policy_json);
        $create_project_json = json_encode(array(
            "productCode" => $template_id,
            "variantsCodes" => array($variant_code),
            "defaultVariantCode" => $variant_code,
            "policy" => $base_64_encoded_policy_json,
            "signedPolicy" => $signed_policy
        ), JSON_UNESCAPED_SLASHES);

        return $this->get_response($url, 'POST', $create_project_json);
    }

    /**
     * Create imaxel order
     * 
     * @param array $project_id
     * @return string
     */
    public function create_order($project_id, $order_id)
    {
        $url = $this->base_imaxel_api_url . 'orders';

        $context_array = array(
            'jobs' => array(
                array(
                    'project' => array(
                        'id' => $project_id
                    ),
                    'units' => 1,
                    'allowDownload' => true
                ),
            ),
            'checkout' => array(
                'shop' => array('code' => $this->shop_code)
            ),
            'notes' => 'WC order ID: ' . $order_id
        );
        $base_64_encoded_policy_json = $this->generate_base64_encoded_policy($context_array);
        $signed_policy = $this->generate_signed_policy($base_64_encoded_policy_json);
        $create_project_json = json_encode(array_merge($context_array, array(
            "policy" => $base_64_encoded_policy_json,
            "signedPolicy" => $signed_policy
        )), JSON_UNESCAPED_SLASHES);

        $now =  new DateTime('NOW');
        error_log($now->format('c') . ": created Imaxel order for WC {$order_id}" . PHP_EOL, 3,  $this->logFile);

        return $this->get_response($url, 'POST', $create_project_json);
    }

    /**
     * Get all pending orders
     * 
     * @return string
     */
    public function get_pending_orders()
    {
        $url = $this->base_imaxel_api_url . 'receivedorders/pending';

        $base_64_encoded_policy_json = $this->generate_base64_encoded_policy();
        $signed_policy = $this->generate_signed_policy($base_64_encoded_policy_json);
        $url .= "?policy=" . rawurlencode($base_64_encoded_policy_json) . "&signedPolicy=" . rawurlencode($signed_policy);

        return $this->get_response($url, 'GET');
    }

    /**
     * Marks order as downloaded
     */
    public function mark_order_as_downloaded($orderId)
    {
        $url = $this->base_imaxel_api_url . 'receivedorders/downloaded';

        $context_array = array(
            'orderId' => $orderId
        );

        $base_64_encoded_policy_json = $this->generate_base64_encoded_policy($context_array);
        $signed_policy = $this->generate_signed_policy($base_64_encoded_policy_json);

        $mark_order_as_downloaded_json = json_encode(array_merge($context_array, array(
            "policy" => $base_64_encoded_policy_json,
            "signedPolicy" => $signed_policy
        )), JSON_UNESCAPED_SLASHES);

        return $this->get_response($url, 'POST', $mark_order_as_downloaded_json);
    }

    /**
     * Returns editor URL for specified project
     * 
     * @param string $projectId
     * @return string
     */
    public function get_editor_url($project_id, $back_url, $lang, $add_to_cart_url)
    {
        $context_array = array(
            'projectId' => strval($project_id),
            'backURL' => $back_url,
            'addToCartURL' => $add_to_cart_url,
            'redirect' => '1'
        );
        $base_64_encoded_policy_json = $this->generate_base64_encoded_policy($context_array);
        $signed_policy = $this->generate_signed_policy($base_64_encoded_policy_json);

        return $this->base_imaxel_api_url . 'projects/'
            . $project_id
            . '/editUrl?'
            . 'backURL=' . rawurlencode($context_array['backURL'])
            . '&addToCartURL=' . rawurlencode($context_array['addToCartURL'])
            . '&lang=' . $lang
            . '&redirect=' . $context_array['redirect']
            . '&policy=' . rawurlencode($base_64_encoded_policy_json)
            . '&signedPolicy=' . rawurlencode($signed_policy);
    }
}
