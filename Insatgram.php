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
     * @param array string list of images
     */
    protected function getImagesByTag($tag)
    {
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
        //Now parse through the $results array to display your results...
        foreach($results['data'] as $item) {
            $image_link = $item['images']['low_resolution']['url'];
            echo '<img src="' . $image_link . '" />';
        }
    }
}
 