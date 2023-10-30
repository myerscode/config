<?php

namespace Tests;

use Myerscode\Config\Config;
use Myerscode\Config\Exceptions\ConfigException;
use Myerscode\Config\Exceptions\InvalidConfigValueException;
use Myerscode\Config\Exceptions\ResolveVariablesDecodeException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ConfigTest extends TestCase
{
    public function testConfigParsesFile(): void
    {
        /**
         * @var $config Config
         */
        $config = $this->mock(Config::class)->makePartial();

        $config->loadFile($this->resourceFilePath('/Resources/basic-config.php'));

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
            $config->values()
        );
    }

    public function testConfigResolvesNestedVariables(): void
    {
        $config = $this->mock(Config::class)->makePartial();

        $config->loadFile($this->resourceFilePath('/Resources/nested-config.php'));

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
            $config->values()
        );
    }

    public function testConfigLeavesUnknownReferences(): void
    {
        $config = $this->mock(Config::class)->makePartial();

        $config->loadFile($this->resourceFilePath('/Resources/unknown-ref-config.php'));

        $this->assertEquals(
            [
                'foo' => 'bar',
                'hello' => 'world',
                'ref' => '${invalid}',
            ],
            $config->values()
        );
    }

    public function testConfigFindsValues(): void
    {
        $config = $this->mock(Config::class)->makePartial();

        $config->loadFile($this->resourceFilePath('/Resources/basic-config.php'));

        $this->assertEquals('value', $config->value('test'));
        $this->assertEquals(['a', 'b', 'c'], $config->value('setting'));
        $this->assertEquals('a', $config->value('setting.0'));
        $this->assertEquals('world', $config->value('Config.hello'));
    }

    public function testConfigHandlesInfiniteRecursion(): void
    {
        $config = $this->mock(Config::class)->makePartial();
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("Config key hello is referencing a value which has a caused a recursion error");
        $config->loadFile($this->resourceFilePath('/Resources/recursion-config.php'));
    }

    public function testConfigHandlesNoneStringReference(): void
    {
        $config = $this->mock(Config::class)->makePartial();
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("A config value can only reference another string");
        $config->loadFile($this->resourceFilePath('/Resources/invalid-ref-config.php'));
    }

    public function testConfigHandlesConfigFileNotExisting(): void
    {
        $config = $this->mock(Config::class)->makePartial();
        $config->loadFile('foobar.php');
        $this->assertEquals([], $config->values());
    }

    public function testThrowsExceptionIfCannotEncodeResolvedConfig(): void
    {
        $config = $this->mock(Config::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $config->shouldReceive('deserialize')->andThrow(new NotEncodableValueException());
        $this->expectException(ResolveVariablesDecodeException::class);
        $config->loadFile($this->resourceFilePath('/Resources/basic-config.php'));
    }

    public function testValueOrThrow(): void
    {
        $config = new Config();

        $config->loadData([
            'foo' => 'bar',
            'bar' => ['foo' => 'bar', 'hello' => 'world'],
        ]);

        $this->assertEquals('bar', $config->valueOrThrow('foo'));
    }

    public function testValueOrThrowCanThrowExceptionIfAccessorNotSet(): void
    {
        $this->expectException(InvalidConfigValueException::class);
        $this->expectExceptionMessage("There is no config value set for key: hello");

        $config = new Config();

        $config->loadData([
            'foo' => 'bar',
            'bar' => ['foo' => 'bar', 'hello' => 'world'],
        ]);

        $config->valueOrThrow('hello');
    }
}
