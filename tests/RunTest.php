<?php

namespace Dframe\Tests;

use PHPUnit\Framework\TestCase;

ini_set('session.use_cookies', 0);

session_start();

/**
 * Class RunTest
 *
 * @package Dframe\Tests
 */
class RunTest extends TestCase
{
    public function testCreateController()
    {
        $testController = new TestController();
        $this->assertEquals('Hello World', $testController->testHelloWorld());
    }
}

/**
 * Class TestController
 *
 * @package Dframe\Tests
 */
class TestController extends \Dframe\Controller
{
    /**
     * @return string
     */
    public function testHelloWorld()
    {
        return 'Hello World';
    }
}
