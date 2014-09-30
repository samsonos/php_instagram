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

    /**
     * Get images by Tag
     *
     * @param string $tag
     * @param bool   $getInstaResult Get Instagram answer without conversion
     *
     * @return array list of images url
     */
    public function listByTag($tag, $getInstaResult = false)
    {
        $return = array();
        // Create url for query
        $url = 'https://api.instagram.com/v1/tags/'.$tag.'/media/recent?client_id='.$this->appId;
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
    public function htmlByTag($tag, $itemView = 'imageslist/item.vphp', $indexView = 'imageslist/index.vphp')
    {
        $list = '';
        // Get list of images by tag
        $results = $this->listByTag($tag, true);
        // Create hmlt view list
        if (sizeof($results) && isset($results['data'])&& is_array($results['data']) && sizeof($results['data'])) {
            foreach ($results['data'] as $item) {
                $list .= $this->view($itemView)->img($item['images']['low_resolution']['url'])->output();
            }
        }

        return $this->view($indexView)->list($list)->output();
    }
}
 