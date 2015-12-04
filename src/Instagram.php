<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 11.04.14 at 15:17
 */
namespace samson\instagram;

use samson\core\CompressableService;
use samsonphp\event\Event;

/**
 * Class for  operations with Instagram
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @copyright 2014 SamsonOS
 * @version 0.1
 */
class Instagram extends CompressableService
{
    /** @var string Instagram API url string */
    protected $url = 'https://api.instagram.com/v1';

    /** Module identifier */
    public $id = 'instagram';

    /** Your application client id */
    public $appId;

    /** Your application client secret code */
    public $appSecret;

    /** @var string Default access toke for tags requests */
    public $accessToken;

    /** @var Request Object for creating request on Instagram API */
    public $request;

    /**
     * Find subscription identifier by media id
     * @param string $object_id Media id
     * @return string Subscription id
     */
    protected function findObjectFromSubscription($object_id)
    {
        /** @var string $id Subscription identifier */
        $id = '';

        $list = $this->getSubscriptions();

        // Try to find subscription for deleting
        foreach ($list['data'] as $item) {
            if ($item['object_id'] == $object_id) {
                $id = $item['id'];
                break;
            }
        }

        return $id;
    }

    /**
     * Append application or access token to url
     * @param $url
     */
    protected function appendToken(& $url)
    {
        if (isset($this->accessToken)) {
            $url .= '?access_token='.$this->accessToken;
        } else {
            $url .= '?client_id='.$this->appId;
        }
    }

    /**
     * Create url params
     * @param $params
     * @return string
     */
    protected function paramsFromArray($params)
    {
        $paramsUrl = '';
        foreach ($params as $key => $value) {
            $paramsUrl .= '&' . $key . '=' . $value;
        }

        return $paramsUrl;
    }

    /**
     * Module initialization
     * @param array $params
     * @return bool
     */
    public function init(array $params = array())
    {
        // Create default or users Request object
        $this->request = (!isset($this->request)) ? new Request() : new $this->request;

        // If configuration for API Key is not set
        if (!isset($this->appId) || !isset($this->appSecret)) {
            // Signal error
            Event::fire('error', array($this, 'Cannot initialize Instagram module - API keys does not exists'));
        }

        // Call parent initialization
        return parent::init($params);
    }

    /**
     * Get list of instagram posts by tag
     * @param string $tag Tag for searching
     * @param array $params Collection or request parameters
     * @return mixed Posts collection
     */
    public function listByTag($tag, $params = array())
    {
        $endpoint = '/tags/'.urlencode($tag).'/media/recent';
        $url = $this->url.$endpoint;

        // Create url parameters string
        $paramsUrl = $this->paramsFromArray($params);

        // Create url for query
        if (isset($this->accessToken)) {
            $params['access_token'] = $this->accessToken;
        }

        $this->appendToken($url);

        // Create signature
        $signature = $this->generateSig($endpoint, $params);

        // Get API response
        $response = $this->request->get($url.$paramsUrl.'&sig='.$signature);

        // Return response decoded to associative array
        return json_decode($response, true);
    }

    /**
     * @param $id int Media identifier
     * @param $access_token string Auth token
     * @param string $method Type of request
     * @return mixed Request result
     */
    public function likeMedia($id, $access_token, $method = 'POST')
    {
        $endpoint = '/media/'.$id.'/likes';
        $url = $this->url.$endpoint;

        $sigParams = array('access_token' => $access_token);
        $signature = $this->generateSig($endpoint, $sigParams);

        $url .= '?access_token='.$access_token.'&sig='.$signature;

        // Get API response
        $response = $this->request->get($url, array(), $method);

        // Return response decoded to associative array
        return json_decode($response, true);
    }

    /**
     * Find info about media
     * @param $id
     * @return mixed
     */
    public function mediaById($id)
    {
        $endpoint = '/media/'.urlencode($id);
        $url = $this->url.$endpoint;

        $sigParams = array();
        // Create url for query
        if (isset($this->accessToken)) {
            $sigParams['access_token'] = $this->accessToken;
        }

        $this->appendToken($url);
        $signature = $this->generateSig($endpoint, $sigParams);

        $url .= '&sig='.$signature;

        // Get API response
        $response = $this->request->get($url);

        // Return response decoded to associative array
        return json_decode($response, true);
    }

    /**
     * Set authenticated user relation with another user by id
     * @param int $user_id Target user identifier
     * @param string $access_token Authenticated user token
     * @param string $action Relationship action. Can be follow | unfollow | approve | ignore
     * @return mixed Request result
     */
    public function setUserRelationship($user_id, $access_token, $action = 'follow')
    {
        // Set url options
        $endpoint = '/users/'.$user_id.'/relationship';
        $url = $this->url.$endpoint;
        $signature = $this->generateSig($endpoint, array('access_token' => $access_token, 'action' => $action));
        $url .= '?access_token='.$access_token.'&sig='.$signature;

        // Get API response
        $response = $this->request->get($url, array('action' => $action), 'POST');

        // Return response decoded to associative array
        return json_decode($response, true);
    }

    /**
     * Check if current are follow relationship with another user
     * @param int $user_id Target user identifier
     * @param string $access_token Authenticated user token
     * @return bool
     */
    public function isFollowing($user_id, $access_token)
    {
        // Set url options
        $endpoint = '/users/'.$user_id.'/relationship';
        $url = $this->url.$endpoint;
        $signature = $this->generateSig($endpoint, array('access_token' => $access_token));
        $url .= '?access_token='.$access_token.'&sig='.$signature;

        // Get API response
        $response = $this->request->get($url);

        // Decode response to array
        $results = json_decode($response, true);
        return (isset($results['data']['outgoing_status']) && $results['data']['outgoing_status'] == 'follows');
    }

    /**
     * Create instagram subscription
     * @param $object string Subscribe object
     * @param $aspect string Subscribe aspect
     * @param $verify_token string Verify token
     * @param $callback string Callback url
     * @param $object_id string Subscribing object identifier
     * @return mixed
     */
    public function subscribe($object, $aspect, $verify_token, $callback, $object_id = null)
    {
        $url = $this->url.'/subscriptions';
        $post = array(
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'object' => $object,
            'aspect' => $aspect,
            'verify_token' => $verify_token,
            'callback_url' => $callback,
        );
        if (sizeof($object_id)) {
            $post['object_id'] = $object_id;
        }

        // Get API response
        $response = $this->request->get($url, $post, 'POST');

        // Return response decoded to associative array
        return json_decode($response, true);
    }

    /**
     * Get list of subscribes of current instagram application
     * @return mixed
     */
    public function getSubscriptions()
    {
        $url = $this->url.'/subscriptions?client_secret='.$this->appSecret.'&client_id='.$this->appId;

        /// Get API response
        $response = $this->request->get($url);

        // Return response decoded to associative array
        return json_decode($response, true);
    }

    /**
     * Delete instagram subscription
     * @param string $id Subscription identifier for deleting
     * @param string $object Subscription object for deleting
     * @param string $object_id Media or user identifier for deleting subscription
     * @return mixed Request result in json format
     */
    public function deleteSubscription($id = '', $object = 'all', $object_id = null)
    {
        $url = $this->url.'/subscriptions?client_secret='.$this->appSecret.'&client_id='.$this->appId;

        // Try to find subscription id by media id
        if (sizeof($object_id)) {
            $id = $this->findObjectFromSubscription($object_id);
        }

        // Delete all subscriptions for selected parameter
        $url .= sizeof($id) ? ('&id='.$id) : ('&object='.$object);

        // Get API response
        $response = $this->request->get($url, array(), 'DELETE');

        // Return response decoded to associative array
        return json_decode($response, true);
    }

    /**
     * Authorize user on instagram.
     *
     * @param string $code Authentication code
     * @param string $redirect_url Authentication redirect
     * @return mixed
     */
    public function getUserToken($code, $redirect_url)
    {
        $url = 'https://api.instagram.com/oauth/access_token';

        $params = array(
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_url,
            'code' => $code
        );

        $response = $this->request->get($url, $params, 'POST');

        // Return response decoded to associative array
        return json_decode($response, true);
    }
    
    /**
     * @param string $endpoint Method name
     * @param array $params Method params
     * @return string Generated signature
     */
    public function generateSig($endpoint, $params = array()) {
        $sig = $endpoint;
        ksort($params);
        foreach ($params as $key => $val) {
            $sig .= "|$key=$val";
        }
        return hash_hmac('sha256', $sig, $this->appSecret, false);
    }
}
