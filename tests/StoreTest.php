<?php

declare(strict_types=1);

namespace Tests;

use Myerscode\Config\Config;
use Myerscode\Config\Store;

final class StoreTest extends TestCase
{
    public function testCanGetStore(): void
    {
        $this->assertInstanceOf(Store::class, new Config()->store());
    }

    public function testStoreIsReset(): void
    {
        $config = Config::make()->loadFile($this->resourceFilePath('/Resources/basic-config.php'));

        $this->assertSame(
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
            $config->store()->toArray(),
        );

        $config->reset();

        $this->assertSame([], $config->store()->toArray());
    }
}
