<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase as PhpUnit;

class TestCase extends PhpUnit
{
    public function mock($class, $constructorArgs = [])
    {
        return Mockery::mock($class, $constructorArgs);
    }

    public function stub($class)
    {
        return Mockery::mock($class);
    }

    public function resourceFilePath(string $fileName = ''): string
    {
        return __DIR__ . $fileName;
    }

    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }
}
