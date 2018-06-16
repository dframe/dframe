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

    public function setUp()
    {

        $this->router = new Router();

        $this->router->addRoute(array(
            'page/:page' => array(
                'page/[page]/',
                'task=page&action=[page]'
            )
        ));

        $this->router->addRoute(array(
            'error/:code' => array(
                'error/[code]/',
                'task=page&action=error&type=[code]',
                'args' => array(
                    'code' => '[code]'
                ),
            )
        ));
        $this->router->addRoute(array(
            'default' => array(
                '[task]/[action]/[params]',
                'task=[task]&action=[action]',
                'params' => '(.*)',
                '_params' => array(
                    '[name]/[value]/',
                    '[name]=[value]'
                )
            )
        ));
    }

    public function testRouterIsActive()
    {
        $_SERVER['REQUEST_URI'] = '';
        $this->assertSame(true, $this->router->isActive(''));
        $this->assertSame(false, $this->router->isActive('this-is-not-page-that-you-looking-for'));
    }

    public function testPublicWeb()
    {
        $this->assertSame('http://dframeframework.com/css/example.css', $this->router->publicWeb('css/example.css'));
        $this->assertSame('http://dframeframework.com/deep/css/example.css', $this->router->publicWeb('css/example.css', 'deep/'));
    }

    // public function testMakeUrl()
    // {
    //     $this->assertSame('http://dframeframework.com/page/index', $this->router->makeUrl('page/:page?page=index'));
    //     $this->assertSame('http://test.com/page/index', $this->router->domain('test.com')->makeUrl('page/:page?page=index'));
    //     $this->assertSame('http://test.dframeframework.com/page/index', $this->router->subdomain('test')->makeUrl('page/:page?page=index'));
    //     $this->assertSame('https://dframeframework.com/page/index', $this->router->setHttps(true)->makeUrl('page/:page?page=index'));
    //     $this->assertSame('http://dframeframework.com/page/index', $this->router->makeUrl('page/:page?page=index'));
    // }

}