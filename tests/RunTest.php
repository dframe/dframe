<?php
namespace Dframe\tests;
ini_set('session.use_cookies', 0);

define('APP_DIR', '');
define('SALT', 'RaNdOmTeSt');

class RunTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateController(){

        $testController = new TestController();
        $this->assertEquals('Hello World', $testController->testHelloWorld());
    }

}

class TestController extends \Dframe\Controller {

	public function testHelloWorld(){
		return 'Hello World';
	}
}