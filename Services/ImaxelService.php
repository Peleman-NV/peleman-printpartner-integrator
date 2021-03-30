<?php

namespace PelemanPrintpartnerIntegrator\Services;

use DateTime;
use DateInterval;

class ImaxelService
{
    private $private_key;
    private $public_key;
    private $base_imaxel_api_url = "https://services.imaxel.com:443/api/v3/";


    public function __construct()
    {
        $this->private_key =  get_option('ppi-imaxel-private-key');
        $this->public_key =  get_option('ppi-imaxel-public-key');
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
        $template_id = $template_id;
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
