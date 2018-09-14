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
class ConfigTest extends TestCase
{
    private $file_system;

    protected function setUp()
    {
        $directory = [
            'Config' => [
                'test.php' => "<?php return ['create' => 'yes'];",
            ],
        ];

        $this->file_system = vfsStream::setup('root', 755, $directory);
    }

    public function testLoad()
    {
        $configTest = Config::load('test', $this->file_system->url() . '/Config/');
        $this->assertEquals('yes', $configTest->get('create'));
    }

    public function testLoadIfNotExist()
    {
        $configTest = Config::load('test', $this->file_system->url() . '/Config/');
        $this->assertEquals('default_value', $configTest->get('not_exist', 'default_value'));
    }
}
