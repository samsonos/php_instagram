<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 04.12.2015
 * Time: 17:11
 */
namespace samson\instagram;

/**
 * Instagram API http/https requests interface.
 *
 * @package samson\instagram
 */
interface RequestInterface
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_DELETE = 'DELETE';

    /**
     * Create http/https request to instagram API.
     *
     * @param string $url Request url
     * @param array $params Request params (for POST requests)
     * @param string $method Request method
     * @return mixed Response
     */
    public function get($url, array $params = array(), $method = self::HTTP_GET);
}
