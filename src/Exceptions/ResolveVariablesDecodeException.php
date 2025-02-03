<?php

namespace Myerscode\Config\Exceptions;

use Throwable;
use UnexpectedValueException;

class ResolveVariablesDecodeException extends UnexpectedValueException
{
    public function __construct(array $configMeta, string $configTemplate, string $updatedTemplate, Throwable $previous)
    {
        parent::__construct("Error decoding config", 0, $previous);
    }
}
