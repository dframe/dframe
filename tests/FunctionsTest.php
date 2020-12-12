<?php

namespace Dframe\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;

class FunctionsTest extends TestCase
{
    public function testPathFile()
    {
        $this->assertSame(['./this/is/a/', 'file'], pathFile('./this/is/a/file'));
    }

    public function testGenerateRandomString()
    {
        $randomString = generateRandomString();
        $this->assertMatchesRegularExpression('/(\d+|\w){10,10}/', $randomString);

        $randomString = generateRandomString(5);
        $this->assertMatchesRegularExpression('/(\d+|\w){5,5}/', $randomString);
    }

    public function testObjectToArray()
    {
        $expected = ['key1' => 'value1', 'key2' => 'value2'];

        $stdClass = new stdClass();
        $stdClass->key1 = 'value1';
        $stdClass->key2 = 'value2';
        $this->assertSame($expected, object_to_array($stdClass));

        $this->assertSame($expected, object_to_array($expected));
    }

    /**
     * @return array
     */
    public function stringMatchWithWildcardProvider()
    {
        return [
            ['geeks', 'g*ks', 1],
            ['g*k', 'gee', 0],
            ['*pqrs', 'pqrst', 0],
        ];
    }

    /**
     * @dataProvider stringMatchWithWildcardProvider
     *
     * @param $source
     * @param $pattern
     * @param $expected
     */
    public function testStringMatchWithWildcard($source, $pattern, $expected)
    {
        $this->assertSame($expected, stringMatchWithWildcard($source, $pattern));
    }
}
