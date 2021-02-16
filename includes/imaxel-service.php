<?php

class Imaxel_Service
{
    private $private_key;
    private $public_key;
    private $base_imaxel_api_url = "https://services.imaxel.com:443/api/v3/";


    public function _construct()
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
        $curl = curl_init();

        if ($request_type === 'POST') {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $request_type,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
                CURLOPT_POSTFIELDS => $json_request
            ));
        } else if ($request_type === 'GET') { {
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                    CURLOPT_CUSTOMREQUEST => $request_type
                ));
            }

            $response = curl_exec($curl);
            curl_close($curl);

            return $response;
        }
    }

    /**
     * Create imaxel project
     * 
     * @param array $bookInfo 
     * @return string
     */
    public function create_project($bookInfo)
    {
        $url = $this->base_imaxel_api_url . 'projects/';
        $template_id = 0; // product get template_id;
        $context_array = array('contextArray' => $template_id);
        $base_64_encoded_policy_json = $this->generate_base64_encoded_policy($context_array);
        $signed_policy = $this->generate_signed_policy($base_64_encoded_policy_json);
        $create_project_json = json_encode(array(
            "productCode" => $template_id,
            "policy" => $base_64_encoded_policy_json,
            "signedPolicy" => $signed_policy
        ));

        return $this->get_response($url, 'POST', $create_project_json);
    }

    /**
     * Returns editor URL for specified project
     * 
     * @param string $projectId
     * @return string
     */
    public function get_editor_url($project_id, $back_url, $add_to_cart_url)
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
            . '&redirect=' . $context_array['redirect']
            . '&policy=' . rawurlencode($base_64_encoded_policy_json)
            . '&signedPolicy=' . rawurlencode($signed_policy);
    }
}
