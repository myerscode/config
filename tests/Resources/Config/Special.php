<?php

return [
    'references' => [
        'slash_t' => '\Tests\Mocks\DemoApp',
        'slash_T' => '\tests\mock\demoapp',
    ],
    'escaped_characters' => [
        'newline' => "string\nwith a newline character.",
        'tab' => "string\twith a tab character.",
        'backslash' => "string with a backslash: \\.",
        'double_quotes' => "string with double quotes: \"Hello, World!\"",
        'single_quotes' => 'string with single quotes: \'Hello, World!\'',
        'unicode_escape' => "string with a Unicode encode: \\u00A9 (\u00A9)",
        'Windows Filepath' => "D:\a\acorn-framework\acorn-framework\Tests\Mocks\DemoApp",
        'Reference \T' => 'D:\a\acorn-framework\acorn-framework${special.references.slash_t}',
        'Reference \t' => 'D:\a\acorn-framework\acorn-framework${special.references.slash_T}'
    ],
];
