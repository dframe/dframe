<?php

namespace Dframe\tests;

use Dframe\Router\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testResponseConstruct()
    {
        $response = new Response('Hello Word!');
        $this->assertSame('Hello Word!', $response->getBody());
    }

    public function testResponseCreate()
    {
        $response = Response::create('Hello Word!');
        $this->assertSame('Hello Word!', $response->getBody());
    }

    public function testResponseRender()
    {
        $response = Response::render('Hello Word!');
        $this->assertSame('Hello Word!', $response->getBody());
    }

    public function testResponseRenderJSON()
    {
        $response = Response::renderJSON('Hello Word!');
        $this->assertSame(json_encode('Hello Word!'), $response->getBody());
    }

    public function testResponseRenderJSONP()
    {
        $response = Response::renderJSONP('Hello Word!');
        $this->assertSame('(' . json_encode('Hello Word!') . ')', $response->getBody());
    }

    public function testResponseRedirect()
    {
        $response = Response::redirect();
        $this->assertSame(['Location' => 'http://dframeframework.com'], $response->getHeaders());
    }

    public function testResponseStatus()
    {
        $response = new Response();
        $this->assertSame(200, $response->getStatus());
        $response->status(403);
        $this->assertSame(403, $response->getStatus());
    }
}
