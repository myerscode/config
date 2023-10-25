<?php

return [
    'root' => '${dir}/root',
    'build' => [
        'directoryName' => 'Corgi',
        'directory' => '${directories.root}/"directory"/${directories.build.directoryName}',
    ],
    'template' => [
        'dir' => 'template',
        'location' => '${directories.root}/\'template\'${directories.build.directory}/${directories.template.dir}',
        'file' => '${directories.template.location}/app.yml',
    ],
    'test' => [
        'path' => '${directories.root}/test${directories.template.location}/path',
    ],
];
