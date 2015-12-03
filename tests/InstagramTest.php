<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 22.04.14 at 16:04
 */
namespace samson\instagram\tests;

define('__VENDOR_PATH', __DIR__.'/../../../../');
require_once(__VENDOR_PATH.'samsonos/php/core/samson.php');
require_once(__DIR__.'/../Instagram1.php');

class InstagramTest extends \PHPUnit_Framework_TestCase
{
    public function testGetImg()
    {
        $this->assertEquals(0, 0);
    }
}
 