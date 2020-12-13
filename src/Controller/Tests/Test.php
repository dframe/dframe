<?php


namespace Dframe\Controller\Tests;

use Dframe\Controller;

class TestController extends Controller
{
    /**
     * @return bool
     */
    public function test()
    {
        return true;
    }

    /**
     * @return string
     */
    public function testHelloWorld()
    {
        return 'Hello World';
    }
}
