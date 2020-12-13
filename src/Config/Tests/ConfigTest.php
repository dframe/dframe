<?php

namespace Dframe\Config\Tests;

use Dframe\Config\Config;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigTest
 *
 * @package Dframe\Tests
 */
class ConfigTest extends TestCase
{
    /**
     * @var vfsStream
     */
    protected $fileSystem;

    /**
     *
     */
    public function testLoad()
    {
        $configTest = Config::load('test', $this->fileSystem->url('') . '/Config/');
        $this->assertEquals('yes', $configTest->get('create'));
    }

    /**
     *
     */
    public function testLoadIfNotExist()
    {
        $configTest = Config::load('test', $this->fileSystem->url('') . '/Config/');
        $this->assertEquals('default_value', $configTest->get('not_exist', 'default_value'));
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $directory = [
            'Config' => [
                'test.php' => "<?php return ['create' => 'yes'];",
            ],
        ];

        $this->fileSystem = vfsStream::setup('root', 755, $directory);
    }
}
