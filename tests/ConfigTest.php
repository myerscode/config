<?php

namespace Tests;

use Myerscode\Config\Config;
use Myerscode\Config\Exceptions\ConfigException;

/**
 * @covers \Myerscode\Config\Config
 */
class ConfigTest extends TestCase
{

    public function testConfigParsesFile()
    {
        /**
         * @var $config Config
         */
        $config = $this->mock(Config::class)->makePartial();

        $config->loadFromFile($this->resourceFilePath('/Resources/basic-config.php'));

        $this->assertEquals(
            [
                'test' => 'value',
                'setting' => [
                    'a',
                    'b',
                    'c',
                ],
                'Config' => [
                    'foo' => 'bar',
                    'hello' => 'world',
                ],
            ],
            $config->all()
        );
    }

    public function testConfigResolvesNestedVariables()
    {
        $config = $this->mock(Config::class)->makePartial();

        $config->loadFromFile($this->resourceFilePath('/Resources/nested-config.php'));

        $this->assertEquals(
            [
                'foo' => 'bar',
                'world' => 'hello',
                'nested' => [
                    'foobar' => 'foobar',
                    'deeper' => [
                        'setting' => 'hello world',
                    ],
                ],
                'dotaccessor' => 'hello world',
            ],
            $config->all()
        );
    }

    public function testConfigLeavesUnknownReferences()
    {
        $config = $this->mock(Config::class)->makePartial();

        $config->loadFromFile($this->resourceFilePath('/Resources/unknown-ref-config.php'));

        $this->assertEquals(
            [
                'foo' => 'bar',
                'hello' => 'world',
                'ref' => '${invalid}',
            ],
            $config->all()
        );
    }

    public function testConfigFindsValues()
    {
        $config = $this->mock(Config::class)->makePartial();

        $config->loadFromFile($this->resourceFilePath('/Resources/basic-config.php'));

        $this->assertEquals('value', $config->get('test'));
        $this->assertEquals(['a', 'b', 'c'], $config->get('setting'));
        $this->assertEquals('a', $config->get('setting.0'));
        $this->assertEquals('world', $config->get('Config.hello'));
    }

    public function testConfigHandlesInfiniteRecursion()
    {
        $config = $this->mock(Config::class)->makePartial();
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("Config key hello is referencing a value which has a caused a recursion error");
        $config->loadFromFile($this->resourceFilePath('/Resources/recursion-config.php'));
    }

    public function testConfigHandlesNoneStringReference()
    {
        $config = $this->mock(Config::class)->makePartial();
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("A config value can only reference another string");
        $config->loadFromFile($this->resourceFilePath('/Resources/invalid-ref-config.php'));
    }

    public function testConfigHandlesConfigFileNotExisting()
    {
        $config = $this->mock(Config::class)->makePartial();
        $config->loadFromFile('foobar.php');
        $this->assertEquals([], $config->all());
    }
}
