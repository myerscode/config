<?php

declare(strict_types=1);

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase as PhpUnit;

class TestCase extends PhpUnit
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }
    public function mock($class, $constructorArgs = [])
    {
        return Mockery::mock($class, $constructorArgs);
    }

    public function resourceFilePath(string $fileName = ''): string
    {
        return __DIR__ . $fileName;
    }

    public function stub($class)
    {
        return Mockery::mock($class);
    }
}
