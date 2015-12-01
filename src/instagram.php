<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 11.04.14 at 15:17
 */
 namespace samson\instagram;

 use samson\core\CompressableService;

 /**
 * Class for  operations with Instagram
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @copyright 2014 SamsonOS
 * @version 0.1
 */
class Instagram extends CompressableService
{
    /** Module identifier */
    public $id = 'instagram';

    /** your application Id */
    public $appId;

    /** Your application secret code */
    public $appSecret;

    /** @var string Default access toke for tags requests */
    public $accessToken;

    /**
     * Get images by Tag
     *
     * @param string $tag
     * @param bool   $getInstaResult Get Instagram answer without conversion
     * @param string $minTagID Get posts after post with this id
     *
     * @return array list of images url
     */
    public function listByTag($tag, $getInstaResult = false, $count = 0, $maxTagID = null, $minTagID = null)
    {
        $return = array();
        $params = '';

        if ($minTagID) {
            $params .= '&min_tag_id='.$minTagID;
        }

        if ($maxTagID) {
            $params .= '&max_tag_id='.$maxTagID;
        }

        if ($count) {
            $params .= '&count='.$count;
        }

        // Create url for query
        if (isset($this->accessToken)) {
            $url = 'https://api.instagram.com/v1/tags/'.urlencode($tag).'/media/recent?access_token='.$this->accessToken.$params;
        } else {
            $url = 'https://api.instagram.com/v1/tags/'.urlencode($tag).'/media/recent?client_id='.$this->appId.$params;
        }

        // Init Curl
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2
        ));
        // Get result of query and decode
        $results = json_decode(curl_exec($ch), true);
        // lose Curl session
        curl_close($ch);

        if ($getInstaResult) return $results;

        if (sizeof($results) && isset($results['data'])&& is_array($results['data']) && sizeof($results['data'])) {
            //Now parse through the $results array
            foreach($results['data'] as $item) {
                $return[] = $item['images'];
            }
        }
        return $return;
    }

    /**
     * Creating html view
     * @param string $tag Hashtag
     * @param string $itemView View for image item
     * @param string $indexView Main container
     *
     * @return string Html view of images
     */
    public function htmlByTag($tag, $limit = 100, $itemView = 'imageslist/item.vphp', $indexView = 'imageslist/index.vphp')
    {
        $list = '';
        // Get list of images by tag
        $results = $this->listByTag($tag, true);
        // Create hmlt view list
        $count = 0;
        if (sizeof($results) && isset($results['data'])&& is_array($results['data']) && sizeof($results['data'])) {
            foreach ($results['data'] as $item) {
                $list .= $this->view($itemView)->link($item['link'])->img($item['images']['low_resolution']['url'])->output();
                $count++;
                if ($count >= $limit) break;
            }
        }

        return $this->view($indexView)->list($list)->output();
    }

    /**
     * @param $id int Media identifier
     * @param $accessToken string Auth token
     * @param string $method Type of request
     * @return mixed Request result
     */
    public function likeMedia($id, $accessToken, $method = 'POST')
    {
        $url = 'https://api.instagram.com/v1/media/'.$id.'/likes?access_token='.$accessToken.'&client_id='.$this->appId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        $results = json_decode(curl_exec($ch), true);

        return $results;
    }

    /**
     * Find info about media
     * @param $id
     * @return mixed
     */
    public function mediaById($id)
    {
        // Create url for query
        if (isset($this->accessToken)) {
            $url = 'https://api.instagram.com/v1/media/'.urlencode($id).'?access_token='.$this->accessToken;
        } else {
            $url = 'https://api.instagram.com/v1/media/'.urlencode($id).'?client_id='.$this->appId;
        }

        // Init Curl
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2
        ));

        // Get result of query and decode
        $results = json_decode(curl_exec($ch), true);
        // lose Curl session
        curl_close($ch);

        return $results;
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
        $url = 'https://api.instagram.com/v1/users/'.$user_id.'/relationship?access_token='.$access_token.'&client_id='.$this->appId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // This is POST request
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "action=".$action);

        $results = json_decode(curl_exec($ch), true);

        return $results;
    }

    /**
     * Check if current are follow relationship with another user
     * @param int $user_id Target user identifier
     * @param string $access_token Authenticated user token
     * @return bool
     */
    public function isFollowing($user_id, $access_token)
    {
        // Create url for query
        $url = 'https://api.instagram.com/v1/users/'.$user_id.'/relationship?access_token='.$access_token.'&client_id='.$this->appId;

        // Init Curl
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2
        ));

        // Get result of query and decode
        $results = json_decode(curl_exec($ch), true);
        // lose Curl session
        curl_close($ch);

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
    public function subscribe($object, $aspect, $verify_token, $callback, $object_id = '')
    {
        $url = 'https://api.instagram.com/v1/subscriptions/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $postFields = "client_id=".$this->appId.
            "&client_secret=".$this->appSecret.
            "&object=".$object.
            "&aspect=".$aspect.
            "&verify_token=".$verify_token.
            "&callback_url=".$callback;

        if (sizeof($object_id)) {
            $postFields .= '&object_id='.$object_id;
        }

        // This is POST request
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        // Make curl request and get json result
        $response = curl_exec($ch);
        $results = json_decode($response, true);

        curl_close($ch);

        return $results;
    }

    /**
     * Get list of subscribes of current instagram application
     * @return mixed
     */
    public function getSubscriptions()
    {
        // Create url for query
        $url = 'https://api.instagram.com/v1/subscriptions?client_secret='.$this->appSecret.'&client_id='.$this->appId;

        // Init Curl
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2
        ));

        // Get result of query and decode
        $results = json_decode(curl_exec($ch), true);
        // lose Curl session
        curl_close($ch);

        return $results;
    }

    /**
     * @param string $id Subscription identifier for deleting
     * @param string $object Subscription object for deleting
     * @param string $object_id Media or user identifier for deleting subscription
     * @return mixed Request result in json format
     */
    public function deleteSubscription($id = '', $object = 'all', $object_id = '')
    {
        if (sizeof($object_id)) {
            $list = $this->getSubscriptions();

            // Try to find subscription for deleting
            foreach ($list['data'] as $item) {
                if ($item['object_id'] == $object_id) {
                    $id = $item['id'];
                    break;
                }
            }
        }

        // If we have subscription for deleting
        if (sizeof($id)) {
            $url = 'https://api.instagram.com/v1/subscriptions?client_secret='.$this->appSecret.'&client_id='.$this->appId.'&id='.$id;
        } else {
            // Delete all subscriptions for selected object
            $url = 'https://api.instagram.com/v1/subscriptions?client_secret='.$this->appSecret.'&client_id='.$this->appId.'&object='.$object;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $results = json_decode(curl_exec($ch), true);

        return $results;
    }
}
