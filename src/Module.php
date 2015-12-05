<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 04.12.2015
 * Time: 16:45
 */
namespace samson\instagram;

use samson\core\CompressableService;
use samson\instagram\exception\InstagramMethodNotFound;
use samsonphp\event\Event;

/**
 * SamsonPHP Instagram API module.
 *
 * @package samson\instagram
 * @method listByTag($tag, $params = array())
 * @method likeMedia($id, $access_token, $method = 'POST')
 * @method mediaById($id)
 * @method setUserRelationship($user_id, $access_token, $action = 'follow')
 * @method isFollowing($user_id, $access_token)
 * @method subscribe($object, $aspect, $verify_token, $callback, $object_id = null)
 * @method getSubscriptions()
 * @method deleteSubscription($id = '', $object = 'all', $object_id = null)
 * @method getUserToken($code, $redirect_url)
 */
class Module extends CompressableService
{
    /** @var string Module identifier */
    protected $id = 'instagram';

    /** @var Instagram Basic instagram API instance */
    protected $instagram;

    /** @var string Your application client id */
    public $appId;

    /** @var string Your application client secret code */
    public $appSecret;

    /**
     * Module initialization.
     *
     * @param array $params
     * @return bool
     */
    public function init(array $params = array())
    {
        // If configuration for API Key is not set
        if (isset($this->appId) && isset($this->appSecret)) {
            $this->instagram = new Instagram($this->appId, $this->appSecret, new Request());
        } else { // Signal error
            Event::fire('error', array($this, 'Cannot initialize Instagram module - API keys does not exists'));
        }

        // Call parent initialization
        return parent::init($params);
    }

    /**
     * Magic method for using instagram API calls.
     *
     * @param string $method Method name
     * @param array $params Method parameters
     * @return mixed Callable result
     * @throws InstagramMethodNotFound
     */
    public function __call($method, $params)
    {
        $callable = array($this->instagram, $method);
        if (is_callable($callable)) {
            return call_user_func_array($callable, $params);
        } else {
            throw new InstagramMethodNotFound($method);
        }
    }
}
