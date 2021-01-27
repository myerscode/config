<?php

namespace Myerscode\Config;

use Myerscode\Config\Exceptions\ConfigException;
use Myerscode\Utilities\Bags\DotUtility;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class Config
{
    const MAX_RECURSION_LOOPS = 100;

    private FileService $fileService;

    private DotUtility $dot;

    private DotUtility $recursionCounter;

    public function __construct()
    {
        $this->fileService = new FileService();
        $this->dot = new DotUtility([]);
        $this->resetCounter();
    }

    protected function resetCounter(): void
    {
        unset($this->recursionCounter);
        $this->recursionCounter = new DotUtility([]);
    }

    protected function parseConfigArray($config): array
    {
        $this->resetCounter();

        return $this->resolveVariables(new DotUtility($config));
    }

    protected function serializeArray(array $array)
    {
        return (new JsonEncode)->encode(
            $array,
            JsonEncoder::FORMAT,
            ['json_encode_Config' => 194]
        );
    }

    private function resolveVariables(DotUtility $repo)
    {
        $configTemplate = $this->serializeArray($repo->toArray());

        $updatedTemplate = $this->resolveValues($configTemplate, $repo);

        return (new JsonEncoder())->decode($updatedTemplate, JsonEncoder::FORMAT);
    }

    private function resolveValues($template, DotUtility $repo)
    {
        return preg_replace_callback('/\${([a-zA-Z0-9_.]+)}/', function (array $matches) use ($repo) {
            return $this->resolveConfigValue($matches[1], $repo) ?? $matches[0];
        }, $template);
    }

    private function resolveConfigValue(string $key, DotUtility $repo)
    {
        $this->recursionCounter->set($key, $this->recursionCounter->get($key, 0) + 1);

        if ($this->recursionCounter->get($key) >= Config::MAX_RECURSION_LOOPS) {
            throw new ConfigException("Config key {$key} is referencing a value which has a caused a recursion error");
        }

        $value = $repo->get($key, null);

        if (is_null($value)) {
            return null;
        }
        if (!is_string($value)) {
            throw new ConfigException("A config value can only reference another string");
        }

        return $this->resolveValues($value, $repo);
    }

    public function loadFromFile(string $file)
    {
        if ($this->fileService->does($file)->exists()) {
            $settings = $this->loadFile($file);
            $config = $this->parseConfigArray($settings);
            $this->dot = $this->dot->mergeRecursively($config);
        }
    }

    protected function loadFile(string $filename): array
    {
        if (!$this->fileService->does($filename)->exists()) {
            return [];
        }
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if ($extension === 'php') {
            $settings = include $filename;
            if (is_array($settings)) {
                return $settings;
            }
        }

        return [];
    }

    public function all(): array
    {
        return $this->dot->toArray();
    }

    public function get(string $key)
    {
        return $this->dot->get($key);
    }
}
