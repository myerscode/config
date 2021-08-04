<?php

namespace Myerscode\Config;

use Myerscode\Config\Exceptions\ConfigException;
use Myerscode\Utilities\Bags\DotUtility as Store;
use Myerscode\Utilities\Files\Utility as FileService;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class Config
{
    public const MAX_RECURSION_LOOPS = 25;

    private static ?Store $store = null;

    private Store $recursionCounter;

    public function __construct()
    {
        $this->createStore();
        $this->resetCounter();
    }

    public static function make(): Config
    {
        return new self();
    }

    public static function reset(): void
    {
        self::$store = null;
        self::make();
    }

    protected function createStore(): void
    {
        if (!isset(self::$store)) {
            self::$store = new Store([]);
        }
    }

    protected function resetCounter(): void
    {
        unset($this->recursionCounter);
        $this->recursionCounter = new Store([]);
    }

    protected function parseConfigArray($config): array
    {
        $this->resetCounter();

        return $this->resolveVariables(new Store($config));
    }

    protected function serializeArray(array $array)
    {
        return (new JsonEncode())->encode(
            $array,
            JsonEncoder::FORMAT,
            ['json_encode_Config' => 194]
        );
    }

    private function resolveVariables(Store $repo)
    {
        $configTemplate = $this->serializeArray($repo->toArray());

        $updatedTemplate = $this->resolveValues($configTemplate, $repo);

        return (new JsonEncoder())->decode($updatedTemplate, JsonEncoder::FORMAT);
    }

    private function resolveValues($template, Store $repo)
    {
        return preg_replace_callback('/\${([a-zA-Z0-9_.]+)}/', function (array $matches) use ($repo) {
            return $this->resolveConfigValue($matches[1], $repo) ?? $matches[0];
        }, $template);
    }

    private function resolveConfigValue(string $key, Store $repo)
    {
        $this->recursionCounter->set($key, $this->recursionCounter->get($key, 0) + 1);

        if ($this->recursionCounter->get($key) >= Config::MAX_RECURSION_LOOPS) {
            throw new ConfigException("Config key $key is referencing a value which has a caused a recursion error");
        }

        $value = $repo->get($key);

        if (is_null($value)) {
            return null;
        }

        if (!is_string($value)) {
            throw new ConfigException("A config value can only reference another string");
        }

        return $this->resolveValues($value, $repo);
    }

    public function loadFromFile(string $file): void
    {
        if (FileService::make($file)->exists()) {
            $settings = $this->loadFile($file);
            $config = $this->parseConfigArray($settings);
            self::$store = self::$store->mergeRecursively($config);
        }
    }

    protected function loadFile(string $filename): array
    {
        if (!FileService::make($filename)->exists()) {
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

    /**
     * @return Store
     */
    public function store(): Store
    {
        return self::$store;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->store()->toArray();
    }

    /**
     * @param  string  $key
     *
     * @return array|mixed|null
     */
    public function get(string $key)
    {
        return $this->store()->get($key);
    }
}
