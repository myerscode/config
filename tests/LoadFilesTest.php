<?php

namespace Tests;

use Myerscode\Config\Config;

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

    public function testCanBuildConfigFromYaml()
    {
        $config = new Config();

        $config->loadFiles([
            $this->resourceFilePath('/Resources/config.yml'),
            $this->resourceFilePath('/Resources/settings.yaml'),
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

    public function testLoadsNoneExistentFilesAsEmptyConfig()
    {
        $config = new Config();
        $config->loadFile('foo.bar');
        $this->assertEquals([], $config->values());
    }

    public function testWillNotLoadUnsupportedConfigFileType()
    {
        $config = new Config();
        $config->loadFile($this->resourceFilePath('/Resources/config.toml'));
        $this->assertEquals([], $config->values());
    }
}
