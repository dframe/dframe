<?php
namespace Dframe\tests;
ini_set('session.use_cookies', 0);

session_start();

define('APP_DIR', '');
define('SALT', 'RaNdOmTeSt');

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') AND class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class RunTest extends \PHPUnit\Framework\TestCase
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
