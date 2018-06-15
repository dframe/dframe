<?php
namespace Dframe\tests;

use PHPUnit\Framework\TestCase;
use Dframe\Router\Response;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') and class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class RouterTest extends \PHPUnit\Framework\TestCase
{
 
    public function testResponseJson()
    {
        $response = new Response();
        $response->json(array('foo' => 'bar'));
        $this->assertSame('{"foo":"bar"}', $response->getBody());
    }

}
