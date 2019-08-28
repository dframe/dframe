<?php
namespace Dframe\Tests;

use Exception;
use Bootstrap;
use Dframe\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testConstructor()
    {
        $bootstrap = new Bootstrap();

        $this->assertInstanceOf(Token::class, $bootstrap->token);
    }

    public function testConstructorThrowsExceptionOnInvalidDriver()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This class Require instance Of Psr\SimpleCache\CacheInterface');

        new Token('invalid_driver');
    }

    public function testClear()
    {
        $bootstrap = new Bootstrap();
        $expectedToken = $bootstrap->token->get('token');
        $expectedTimeToken = $bootstrap->token->get('timeToken');
        $bootstrap->token->clear();

        $this->assertNotEquals($expectedToken, $bootstrap->token->get('token'));
        $this->assertNotEquals($expectedTimeToken, $bootstrap->token->get('timeToken'));
    }

    public function testGetMultiple()
    {
        $bootstrap = new Bootstrap();
        $expectedToken = $bootstrap->token->get('token');
        $expectedTimeToken = $bootstrap->token->get('timeToken');

        $keys = [
            'token',
            'timeToken',
        ];
        $result = $bootstrap->token->getMultiple($keys);

        $this->assertEquals($expectedToken, $result['token']);
        $this->assertEquals($expectedTimeToken, $result['timeToken']);
    }

    public function testSetMultiple()
    {
        $bootstrap = new Bootstrap();

        $token = [
            'key' => 'token_key',
            'value' => 'token_value',
        ];
        $values = [
            $token,
        ];
        $bootstrap->token->setMultiple($values);

        $this->assertNotEquals('token_key', $bootstrap->token->get('key'));
        $this->assertNotEquals('token_value', $bootstrap->token->get('value'));
    }

    public function testDeleteMultiple()
    {
        $bootstrap = new Bootstrap();
        $originalToken = $bootstrap->token->get('token');
        $originalTimeToken = $bootstrap->token->get('timeToken');

        $bootstrap->token->set('token', 'token_value');
        $bootstrap->token->setTime('timeToken', 'time_token_value');

        $keys = [
            'token',
            'timeToken',
        ];

        $bootstrap->token->deleteMultiple($keys);

        $this->assertNotEquals($originalToken, $bootstrap->token->get('token'));
        $this->assertNotEquals($originalTimeToken, $bootstrap->token->get('timeToken'));
    }

    public function testHas()
    {
        $bootstrap = new Bootstrap();

        $this->assertFalse($bootstrap->token->has('token'));
        $this->assertFalse($bootstrap->token->has('timeToken'));

        $bootstrap->token->get('token');
        $bootstrap->token->get('timeToken');

        $bootstrap->token->setTime('token', time() + 68400);
        $bootstrap->token->setTime('timeToken', time() + 68400);

        $this->assertTrue($bootstrap->token->has('token'));
        $this->assertTrue($bootstrap->token->has('timeToken'));
    }

    public function testIsValidOnInvalidKey()
    {
        $bootstrap = new Bootstrap();

        $this->assertFalse($bootstrap->token->isValid('token', 'token'));
        $this->assertFalse($bootstrap->token->isValid('timeToken', 'timeToken'));
    }

    public function testIsValidOnValidKey()
    {
        $bootstrap = new Bootstrap();

        $token = $bootstrap->token->get('token');
        $timeToken = $bootstrap->token->get('timeToken');

        $this->assertTrue($bootstrap->token->isValid('token', $token));
        $this->assertTrue($bootstrap->token->isValid('timeToken', $timeToken));
    }

    public function testIsValidOnValidKeyThenResetToken()
    {
        $bootstrap = new Bootstrap();

        $token = $bootstrap->token->get('token');
        $timeToken = $bootstrap->token->get('timeToken');

        $this->assertTrue($bootstrap->token->isValid('token', $token, true));
        $this->assertTrue($bootstrap->token->isValid('timeToken', $timeToken, true));
        $this->assertNotEquals($token, $bootstrap->token->get('token'));
        $this->assertNotEquals($timeToken, $bootstrap->token->get('timeToken'));
    }
}
