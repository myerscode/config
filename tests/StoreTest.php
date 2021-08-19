<?php

namespace Tests;

use Myerscode\Config\Config;
use Myerscode\Config\Store;

class StoreTest extends TestCase
{

    public function testCanGetStore()
    {
        $this->assertInstanceOf(Store::class, Config::store());
    }

    public function testStoreIsReset()
    {
        Config::make()->loadFile($this->resourceFilePath('/Resources/basic-config.php'));

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
            Config::store()->toArray()
        );

        Config::reset();

        $this->assertEquals([], Config::store()->toArray());
    }
}
