<?php
namespace Dframe\tests;

use PHPUnit\Framework\TestCase;
use Dframe\Router;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') and class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class RouterTest extends \PHPUnit\Framework\TestCase
{

    public function setUp() {
        $this->router = new Router();
    }

    public function testRouterIsActive()
    {
        $this->assertSame(true, $this->router->isActive(''));
        $this->assertSame(false, $this->router->isActive('this-is-not-page-that-you-looking-for'));
    }

}