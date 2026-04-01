<?php

namespace Myerscode\Config\Exceptions;

use Throwable;
use UnexpectedValueException;

class ResolveVariablesDecodeException extends UnexpectedValueException
{
    public function __construct(string $updatedTemplate, Throwable $previous)
    {
        parent::__construct('Error decoding config: ' . $updatedTemplate, 0, $previous);
    }
}
