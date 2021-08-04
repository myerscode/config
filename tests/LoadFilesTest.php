<?php

namespace Tests;

use Myerscode\Config\Config;

/**
 * @covers \Myerscode\Config\Config
 */
class LoadFilesTest extends TestCase
{

    public function testCanBuildConfigFromMultipleFiles()
    {
        $config = new Config();

        $config->loadFiles([
            $this->resourceFilePath('/Resources/multi-config-1.php'),
            $this->resourceFilePath('/Resources/multi-config-2.php'),
        ]);

        $this->assertEquals(
            [
                'test' => 'value',
                'example' => 'reference from another file - value',
            ],
            $config->values()
        );
    }
}
