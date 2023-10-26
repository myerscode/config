<?php

namespace Tests;

use Myerscode\Config\Config;

class MixedLoadTest extends TestCase
{
    public function testCanLoadFromMixedSources(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $base = 'D:\\myerscode\\';
        } else {
            $base = '/home/myerscode/';
        }

        $src = dirname(__DIR__) . '/src';

        $data = [
            'base' => $base,
            'src' => $src,
            'cwd' => getcwd(),
            'windows' => 'C:\Windows\Important\Drive',
            'weird_path' => 'C:\Windows\Important\Drive\t\r\n\f\b',
        ];

        $config = new Config();

        $config->loadData($data);

        $config->loadFilesWithNamespace([
            __DIR__ . '/../tests/Resources/Config/App.php',
            __DIR__ . '/../tests/Resources/Config/Framework.php',
        ]);

        $this->assertEquals(
            [
                'base' => $base,
                'src' => $src,
                'cwd' => getcwd(),
                'windows' => 'C:\Windows\Important\Drive',
                'weird_path' => 'C:\Windows\Important\Drive\t\r\n\f\b',
                'app' => [
                    'dir' => [
                        'commands' => "$base/Commands",
                        'events' => "$base/Events",
                        'listeners' => "$base/Listeners",
                    ],
                    'store' => 'Myerscode\Config\Store',
                ],
                'framework' => [
                    'dir' => [
                        'commands' => "$src/Foundation/Commands",
                        'events' => "$src/Foundation/Events",
                        'listeners' => "$src/Foundation/Listeners",
                    ],
                ],
            ],
            $config->values()
        );
    }
}
