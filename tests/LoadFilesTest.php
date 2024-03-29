<?php

namespace Tests;

use Myerscode\Config\Config;

class LoadFilesTest extends TestCase
{
    public function testCanBuildConfigFromMultipleFiles(): void
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

    public function testCanBuildConfigFromYaml(): void
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

    public function testLoadsNoneExistentFilesAsEmptyConfig(): void
    {
        $config = new Config();
        $config->loadFile('foo.bar');
        $this->assertEquals([], $config->values());
    }

    public function testWillNotLoadUnsupportedConfigFileType(): void
    {
        $config = new Config();
        $config->loadFile($this->resourceFilePath('/Resources/config.toml'));
        $this->assertEquals([], $config->values());
    }

    public function testFilesCanBeLoadedToNamespaced(): void
    {
        $config = new Config();
        $config->loadFilesWithNamespace([
            $this->resourceFilePath('/Resources/app.php'),
            $this->resourceFilePath('/Resources/db.php'),
        ]);

        $this->assertEquals(
            [
                'app' => [
                    'name' => 'myerscode',
                    'version' => '7749',
                ],
                'db' => [
                    'name' => 'myerscode_db',
                    'user' => 'myerscode_user',
                    'password' => '77xx49',

                ],
            ],
            $config->values()
        );

        $this->assertEquals('myerscode_db', $config->store()->get('db.name'));
    }

    public function testFileCanBeLoadedToNamespaced(): void
    {
        $config = new Config();
        $config->loadFileWithNamespace($this->resourceFilePath('/Resources/app.php'));

        $this->assertEquals(
            [
                'app' => [
                    'name' => 'myerscode',
                    'version' => '7749',
                ],
            ],
            $config->values()
        );

        $config->loadFileWithNamespace($this->resourceFilePath('/Resources/db.php'));

        $this->assertEquals(
            [
                'app' => [
                    'name' => 'myerscode',
                    'version' => '7749',
                ],
                'db' => [
                    'name' => 'myerscode_db',
                    'user' => 'myerscode_user',
                    'password' => '77xx49',

                ],
            ],
            $config->values()
        );

        $this->assertEquals('myerscode_db', $config->store()->get('db.name'));
    }

    public function testNamespacesFilesAreLowerCase(): void
    {
        $config = new Config();
        $config->loadFileWithNamespace($this->resourceFilePath('/Resources/Settings.php'));
        $this->assertEquals(
            [
                'settings' => [
                    'abc' => '123',
                ],
            ],
            $config->values()
        );
    }

    public function testNamespacesFilesAreSnakeCase(): void
    {
        $config = new Config();
        $config->loadFileWithNamespace($this->resourceFilePath('/Resources/AppSettings.php'));
        $this->assertEquals(
            [
                'app_settings' => [
                    'abc' => '123',
                ],
            ],
            $config->values()
        );
    }
}
