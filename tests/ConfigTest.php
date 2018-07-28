<?php
namespace Dframe\tests;

use PHPUnit\Framework\TestCase;
use Dframe\Config;
use org\bovigo\vfs\vfsStream;

class ConfigTest extends TestCase
{
    protected function setUp()
    {
        $directory = [
            'Config' => [
                'test.php' => "<?php return ['create' => 'yes'];"
            ]
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
