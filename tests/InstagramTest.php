<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 22.04.14 at 16:04
 */
namespace samson\instagram\tests;


use samson\instagram\Request;

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

        // Create Request mock
        $this->request = $this->getMockBuilder('\samson\instagram\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->instance = \samson\core\Service::getInstance('\samson\instagram\Instagram');
        $this->instance->init();
        $this->instance->request = & $this->request;
    }

    public function testList()
    {
        $response = '{[]}';

        $this->request
            ->method('get')
            ->willReturn($response);

        $list = $this->instance->listByTag('adventure', array('count' => 10));

        // Perform test
        $this->assertEquals($list, json_decode($response));
    }

    public function testLike()
    {
        $response = '{[]}';

        $this->request
            ->method('get')
            ->willReturn($response);

        $listPost = $this->instance->likeMedia('id', 'token', 'POST');
        $listDelete = $this->instance->likeMedia('id', 'token', 'DELETE');

        // Perform test
        $this->assertEquals($listPost, json_decode($response));
        $this->assertEquals($listDelete, json_decode($response));
    }

    public function testMedia()
    {
        $response = '{[]}';

        $this->request
            ->method('get')
            ->willReturn($response);

        $list = $this->instance->mediaById('id');

        // Perform test
        $this->assertEquals($list, json_decode($response));
    }

    public function testFollow()
    {
        $response = '{[]}';

        $this->request
            ->method('get')
            ->willReturn($response);

        $list = $this->instance->setUserRelationship('user_id', 'token');

        // Perform test
        $this->assertEquals($list, json_decode($response));
    }

    public function testSubscribe()
    {
        $response = '{[]}';

        $this->request
            ->method('get')
            ->willReturn($response);

        $list = $this->instance->subscribe('object', 'aspect', 'verify_token', 'callback', 'object_id');

        // Perform test
        $this->assertEquals($list, json_decode($response));
    }

    public function testGetSubscriptions()
    {
        $response = '{[]}';

        $this->request
            ->method('get')
            ->willReturn($response);

        $list = $this->instance->getSubscriptions();

        // Perform test
        $this->assertEquals($list, json_decode($response));
    }

    public function testDeleteSubscriptions()
    {
        $response = '{[]}';

        $this->request
            ->method('get')
            ->willReturn($response);

        $list = $this->instance->deleteSubscription('id', 'object', 'object_id');

        // Perform test
        $this->assertEquals($list, json_decode($response));
    }

    public function testIsFollowing()
    {
        $response = '{
    "data": {
        "outgoing_status": "follows",
        "incoming_status": "requested_by"
    }
}';

        $this->request
            ->method('get')
            ->willReturn($response);

        $status = $this->instance->isFollowing('user_id', 'token');

        // Perform test
        $this->assertEquals(1, $status);
    }

    public function testRequestClass()
    {
        $request = new Request();
        $request->get('url');
        $request->get('url', array('action' => 'action'), 'POST');
        $request->get('url', array(), 'DELETE');
    }
}
 