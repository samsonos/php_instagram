<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 21.01.2015
 * Time: 16:39
 */

namespace samson\instagram;


class Request
{
    /**
     * @param string $url Request url
     * @param array $params Request params (for POST requests)
     * @param string $method Request method
     * @return mixed Response
     */
    public function get($url, $params = array(), $method = 'GET')
    {
        // Init curl
        $curl = curl_init();

        // Set curl options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false
        ));

        // Add custom parameters switch to method
        switch ($method) {
            case 'POST':
                // Set curl post option
                curl_setopt($curl, CURLOPT_POST, true);
                
                // If we have some post parameters
                if (sizeof($params)) {
                    // Create post parameters as sting
                    $postParams = '';
                    foreach ($params as $key => $value) {
                        $postParams .= $key.'='.$value.'&';
                    }
                    $postParams = substr($postParams, 0, -1);
                    
                    // Add curl option with parameters string
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $postParams);
                }
                
                break;
            case 'DELETE': curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE'); break;
            default: break;
        }

        // Get result of request
        $response = curl_exec($curl);

        // Close curl session
        curl_close($curl);

        return $response;
    }
}
