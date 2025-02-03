<?php

namespace Tests;

use Myerscode\Config\Config;
use Myerscode\Config\Store;

class LoadDataTest extends TestCase
{
    public function testCanLoadDataFromArray(): void
    {
        $config = new Config();

        $config->loadData([
            'foo' => 'bar',
            'bar' => ['foo' => 'bar', 'hello' => 'world'],
            'setting' => 'foo bar',
            'array' => ['a', 'b', 'c'],
        ]);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'bar' => ['foo' => 'bar', 'hello' => 'world'],
                'setting' => 'foo bar',
                'array' => ['a', 'b', 'c'],
            ],
            $config->values()
        );
    }

    public function testCanLoadDataFromExistingStore(): void
    {
        $store = new Store([
            'foo' => 'bar',
            'bar' => ['foo' => 'bar', 'hello' => 'world'],
        ]);

        $config = new Config();

        $config->loadData($store);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'bar' => ['foo' => 'bar', 'hello' => 'world'],
            ],
            $config->values()
        );
    }

    public function testCanLoadDataFromExistingConfig(): void
    {
        $configOne = new Config();
        $configTwo = new Config();
        $configOne->loadData([
            'foo' => 'bar',
            'bar' => ['foo' => 'bar'],
        ]);
        $configTwo->loadData([
            'bar' => ['hello' => 'world'],
        ]);

        $configTwo->loadData($configOne);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'bar' => ['foo' => 'bar', 'hello' => 'world'],
            ],
            $configTwo->values()
        );
    }

    public function testCanLoadDataWithEscapedCharacters(): void
    {
        $config = new Config();

        $config->loadData([
            'dir' => '/home/user/corgi',
        ]);

        $config->loadFilesWithNamespace([
            $this->resourceFilePath('/Resources/Config/Special.php'),
        ]);

        $escapedCharacters = [
            'newline' => "string\nwith a newline character.",
            'tab' => "string\twith a tab character.",
            'backslash' => "string with a backslash: \\.",
            'double_quotes' => 'string with double quotes: "Hello, World!"',
            'single_quotes' => "string with single quotes: 'Hello, World!'",
            'unicode_escape' => "string with a Unicode encode: \\u00A9 (\u00A9)",
            'Windows Filepath' => "D:\a\acorn-framework\acorn-framework\Tests\Mocks\DemoApp",
            'Reference \T' => "D:\a\acorn-framework\acorn-framework\Tests\Mocks\DemoApp",
            'Reference \t' => "D:\a\acorn-framework\acorn-framework\\tests\mock\demoapp",
        ];

        foreach ($escapedCharacters as $escapedCharacter => $value) {
            $this->assertEquals($value, $config->value('special.escaped_characters.' . $escapedCharacter),);
        }
    }

    public function testDoesNotReEncodeNestedValues(): void
    {
        $config = new Config();

        $config->loadData([
            'dir' => '/home/user/corgi',
        ]);

        $config->loadFilesWithNamespace([
            $this->resourceFilePath('/Resources/Config/Directories.php'),
        ]);

        $this->assertEquals(
            '/home/user/corgi/root/test/home/user/corgi/root/\'template\'/home/user/corgi/root/"directory"/Corgi/template/path',
            $config->value('directories.test.path'),
        );
    }
}
