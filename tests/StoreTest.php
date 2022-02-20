<?php

namespace Tests;

use Myerscode\Config\Config;
use Myerscode\Config\Store;

class StoreTest extends TestCase
{
    public function testCanGetStore(): void
    {
        $this->assertInstanceOf(Store::class, (new Config())->store());
    }

    public function testStoreIsReset(): void
    {
        $config = Config::make()->loadFile($this->resourceFilePath('/Resources/basic-config.php'));

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
            $config->store()->toArray()
        );

        $config->reset();

        $this->assertEquals([], $config->store()->toArray());
    }
}
