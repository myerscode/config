<?php

use Myerscode\Config\Store;

return [
    'dir' => [
        'commands' => '${base}/Commands',
        'events' => '${base}/Events',
        'listeners' => '${base}/Listeners',
    ],
    'store' => Store::class,
];
