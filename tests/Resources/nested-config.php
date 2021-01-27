<?php

return [
    'foo' => 'bar',
    'world' => 'hello',
    'nested' => [
        'foobar' => 'foo${foo}',
        'deeper' => [
            'setting' => '${world} world'
        ]
    ],
    'dotaccessor' => '${nested.deeper.setting}'
];
