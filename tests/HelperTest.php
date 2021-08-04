<?php

namespace Tests;

use Myerscode\Config\Config;

/**
 * @covers \Myerscode\Config\Config
 */
class HelperTest extends TestCase
{

    public function testHelperReturnsAllWithoutKey()
    {
        $config = new Config();

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
            config()
        );
    }

    public function testHelperReturnsValueWithKey()
    {
        $config = new Config();

        $config->loadFile($this->resourceFilePath('/Resources/basic-config.php'));

        $this->assertEquals(
            [
                'a',
                'b',
                'c',
            ],
            config('setting')
        );
    }
}
