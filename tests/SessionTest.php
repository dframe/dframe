<?php
namespace Dframe\tests;

use PHPUnit\Framework\TestCase;
use Dframe\Session;

class SessionTest extends TestCase
{
    public function setUp()
    {
        if (empty($_SESSION)) {
            $_SESSION = [];
        }
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Session::class, new Session());
        $this->assertEquals([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => false,
        ], session_get_cookie_params());
    }

    public function testRegister()
    {
        $session = new Session();
        $session->register(60);
        $session->set('sessionId', 'sessionId');

        $this->assertSame('sessionId', $_SESSION['sessionId']);
        $this->assertSame(60, $_SESSION['sessionTime']);
    }

    public function testAuthLogin()
    {
        $session = new Session();
        $session->remove('sessionId');

        $this->assertFalse($session->authLogin());
    }

    public function testAuthLoginWithSpecificSession()
    {
        $session = new Session();
        $session->set('sessionId', 'sessionId');

        $this->assertTrue($session->authLogin());
    }

    public function testKeyExists()
    {
        $session = new Session();
        $session->remove('sessionId');
        $session->set('sessionId', 'sessionId');

        $this->assertTrue($session->keyExists('sessionId'));
    }

    public function testGet()
    {
        $session = new Session();
        $session->remove('sessionId');
        $session->set('sessionId', 'sessionId');

        $this->assertSame('sessionId', $session->get('sessionId'));
    }

    public function testGetOnSpecificSessionIsNotExisted()
    {
        $session = new Session();
        $session->remove('sessionId');

        $this->assertSame('orSession', $session->get('sessionId', 'orSession'));
    }
}
