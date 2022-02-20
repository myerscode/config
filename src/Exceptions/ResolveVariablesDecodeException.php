<?php

namespace Myerscode\Config\Exceptions;

use Throwable;
use UnexpectedValueException;

class ResolveVariablesDecodeException extends UnexpectedValueException
{
    private string $configTemplate;
    private array $configMeta;
    private string $updatedTemplate;

    public function __construct(array $configMeta, string $configTemplate, string $updatedTemplate, Throwable $previous)
    {
        $this->configMeta = $configMeta;
        $this->configTemplate = $configTemplate;
        $this->updatedTemplate = $updatedTemplate;
        parent::__construct("Error decoding config", 0, $previous);
    }
}
