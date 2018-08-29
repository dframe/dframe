<?php

namespace Dframe\Tests;

use PHPUnit\Framework\TestCase;

ini_set('session.use_cookies', 0);

session_start();

class RunTest extends TestCase
{
    public function testCreateController()
    {
        $testController = new TestController();
        $this->assertEquals('Hello World', $testController->testHelloWorld());
    }
}

class TestController extends \Dframe\Controller
{
    public function testHelloWorld()
    {
        return 'Hello World';
    }
}
