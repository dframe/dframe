<?php

namespace Dframe\Loader\Tests\Controller;

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
