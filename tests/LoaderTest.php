<?php

namespace Dframe\Tests;

use Dframe\Config;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigTest
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

        $this->assertTrue($loaderTest->isCodeStyleNamespace('Namespace'));
        $this->assertTrue($loaderTest->isCodeStyleNamespace('\Namespace'));
        $this->assertTrue($loaderTest->isCodeStyleNamespace('Namespace\SubNamespace'));
        $this->assertTrue($loaderTest->isCodeStyleNamespace('\Namespace\SubNamespace'));
        $this->assertTrue($loaderTest->isCodeStyleNamespace('Namespace\SubNamespace\SubNamespace'));

        $this->assertFalse($loaderTest->isCodeStyleNamespace('namespace'));
        $this->assertFalse($loaderTest->isCodeStyleNamespace('\namespace'));
        $this->assertFalse($loaderTest->isCodeStyleNamespace('Namespace\\'));
        $this->assertFalse($loaderTest->isCodeStyleNamespace('\namespace\SubNamespace'));
        $this->assertFalse($loaderTest->isCodeStyleNamespace('namespace\SubNamespace'));
        $this->assertFalse($loaderTest->isCodeStyleNamespace('Namespace\subNamespace'));
        $this->assertFalse($loaderTest->isCodeStyleNamespace('\Namespace\subNamespace'));
        $this->assertFalse($loaderTest->isCodeStyleNamespace('Namespace\SubNamespace\subNamespace'));
        $this->assertFalse($loaderTest->isCodeStyleNamespace('Namespace\subNamespace\SubNamespace'));
    }

    public function testLoadController()
    {
        $loaderTest = new \Dframe\Loader();

        $this->assertTrue($loaderTest->loadController('TestController','Dframe\Tests')->test());
        $this->assertTrue($loaderTest->loadController('Sub,SubTestController','Dframe\Tests')->test());
        $this->assertTrue($loaderTest->loadController('Sub\SubTestController','Dframe\Tests')->test());
        $this->assertTrue($loaderTest->loadController('Sub/SubTestController','Dframe\Tests')->test());
    }
}
