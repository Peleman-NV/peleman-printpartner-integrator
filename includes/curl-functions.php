<?php

class Curl
{
    private $private_key;
    private $public_key;

    public function _construct()
    {
        $private_key =  get_option('ppi-imaxel-private-key');
        $public_key =  get_option('ppi-imaxel-public-key');
    }

    /**
     * Exectues a CURL request
     * 
     * @param string $url
     * @param string $requestType
     * @param string $jsonRequest
     * @return string
     */

    public function get_response($url, $requestType, $jsonRequest = null)
    {
        $curl = curl_init();

        if ($requestType === 'POST') {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $requestType,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
                CURLOPT_POSTFIELDS => $jsonRequest
            ));
        } else if ($requestType === 'GET') { {
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                    CURLOPT_CUSTOMREQUEST => $requestType
                ));
            }

            $response = curl_exec($curl);
            curl_close($curl);

            return $response;
        }
    }
}
