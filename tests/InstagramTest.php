<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 22.04.14 at 16:04
 */
namespace samson\instagram\tests;


class InstagramTest extends \PHPUnit_Framework_TestCase
{
    /** @var \samson\instagram\Instagram */
    public $instance;

    /** @var \samson\instagram\Request */
    public $request;

    /**
     * Test initialization
     */
    public function setUp()
    {
        \samson\core\Error::$OUTPUT = false;

        // Create S3 mock
        $this->request = $this->getMockBuilder('\samson\instagram\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->instance = \samson\core\Service::getInstance('\samson\instagram\Instagram');
    }

    public function testList()
    {
        $this->instance->init();

        $this->instance->request = & $this->request;

        $response = '
            {
    "data": [{
        "type": "image",
        "users_in_photo": [],
        "filter": "Earlybird",
        "tags": ["adventure"],
        "comments": {
            "count": 3
        },
        "caption": {
            "created_time": "1296703540",
            "text": "#adventure",
            "from": {
                "username": "emohatch",
                "id": "1242695"
            },
            "id": "26589964"
        },
        "likes": {
            "count": 1
        },
        "link": "http://instagr.am/p/BWl6P/",
        "user": {
            "username": "emohatch",
            "profile_picture": "http://distillery.s3.amazonaws.com/profiles/profile_1242695_75sq_1293915800.jpg",
            "id": "1242695",
            "full_name": "Dave"
        },
        "created_time": "1296703536",
        "images": {
            "low_resolution": {
                "url": "http://distillery.s3.amazonaws.com/media/2011/02/02/f9443f3443484c40b4792fa7c76214d5_6.jpg",
                "width": 306,
                "height": 306
            },
            "thumbnail": {
                "url": "http://distillery.s3.amazonaws.com/media/2011/02/02/f9443f3443484c40b4792fa7c76214d5_5.jpg",
                "width": 150,
                "height": 150
            },
            "standard_resolution": {
                "url": "http://distillery.s3.amazonaws.com/media/2011/02/02/f9443f3443484c40b4792fa7c76214d5_7.jpg",
                "width": 612,
                "height": 612
            }
        },
        "id": "22699663",
        "location": null
    }
}
        ';
        $this->request
            ->expects($this->once())
            ->method('get')
            ->willReturn($response);

        $list = $this->instance->listByTag('adventure', array('count' => 10));

        // Perform test
        $this->assertEquals($list, json_decode($response));
    }
}
 