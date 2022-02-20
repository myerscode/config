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
}
