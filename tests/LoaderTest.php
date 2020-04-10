<?php

namespace Dframe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class LoaderTest
 *
 * @package Dframe\Tests
 */
class LoaderTest extends TestCase
{

    /**
     *
     */
    public function testIsCamelCaps()
    {
        $loaderTest = new \Dframe\Loader();

        $this->assertTrue($loaderTest->isCamelCaps('Namespace'));
        $this->assertTrue($loaderTest->isCamelCaps('\Namespace'));
        $this->assertTrue($loaderTest->isCamelCaps('Namespace\SubNamespace'));
        $this->assertTrue($loaderTest->isCamelCaps('\Namespace\SubNamespace'));
        $this->assertTrue($loaderTest->isCamelCaps('Namespace\SubNamespace\SubNamespace'));

        $this->assertFalse($loaderTest->isCamelCaps('namespace'));
        $this->assertFalse($loaderTest->isCamelCaps('\namespace'));
        $this->assertFalse($loaderTest->isCamelCaps('Namespace\\'));
        $this->assertFalse($loaderTest->isCamelCaps('\namespace\SubNamespace'));
        $this->assertFalse($loaderTest->isCamelCaps('namespace\SubNamespace'));
        $this->assertFalse($loaderTest->isCamelCaps('Namespace\subNamespace'));
        $this->assertFalse($loaderTest->isCamelCaps('\Namespace\subNamespace'));
        $this->assertFalse($loaderTest->isCamelCaps('Namespace\SubNamespace\subNamespace'));
        $this->assertFalse($loaderTest->isCamelCaps('Namespace\subNamespace\SubNamespace'));
    }

    public function testLoadController()
    {
        $loaderTest = new \Dframe\Loader();

        $this->assertTrue($loaderTest->loadController('TestController', 'Dframe\Tests')->test());
        $this->assertTrue($loaderTest->loadController('Sub,SubTestController', 'Dframe\Tests')->test());
        $this->assertTrue($loaderTest->loadController('Sub\SubTestController', 'Dframe\Tests')->test());
        $this->assertTrue($loaderTest->loadController('Sub/SubTestController', 'Dframe\Tests')->test());
    }
}
