<?php

namespace Myerscode\Config;

use Myerscode\Utilities\Files\Utility;

class FileService
{

    public function does(string $file): Utility
    {
        return (new Utility($file));
    }
}
