<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 21.01.2015
 * Time: 16:39
 */

namespace samson\instagram;

/**
 * Class for creating Instagram API requests
 * Class Request
 * @package samson\instagram
 */
class Request
{
    /**
     * @var array Curl basic options
     */
    protected $curlParams = array(
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => false
    );

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

        // Set curl url
        curl_setopt($curl, CURLOPT_URL, $url);

        // Set curl options
        curl_setopt_array($curl, $this->curlParams);

        // Add custom parameters switch to method
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->arrayToParam($params));
        } elseif ($method == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        // Get result of request
        $response = curl_exec($curl);

        // Close curl session
        curl_close($curl);

        return $response;
    }

    public function arrayToParam($array)
    {
        // Create parameters as sting
        $param = '';

        if (sizeof($array)) {
            foreach ($array as $key => $value) {
                $param .= $key.'='.$value.'&';
            }
            $param = substr($param, 0, -1);
        }

        return $param;
    }
}
