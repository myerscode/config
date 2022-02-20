<?php

namespace Tests;

use Myerscode\Config\Config;

class MixedLoadTest extends TestCase
{
    public function testCanLoadFromMixedSources(): void
    {
        $base = $this->resourceFilePath('/Resources/App');
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
                        'commands' => sprintf('%s/Commands', $base),
                        'events' => sprintf('%s/Events', $base),
                        'listeners' => sprintf('%s/Listeners', $base),
                    ],
                    'store' => 'Myerscode\Config\Store',
                ],
                'framework' => [
                    'dir' => [
                        'commands' => sprintf('%s/Foundation/Commands', $src),
                        'events' => sprintf('%s/Foundation/Events', $src),
                        'listeners' => sprintf('%s/Foundation/Listeners', $src),
                    ],
                ],
            ],
            $config->values()
        );
    }
}
