<?php

namespace Myerscode\Config;

use Myerscode\Config\Exceptions\ConfigException;
use Myerscode\Utilities\Files\Utility as FileService;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Yaml\Yaml;

class Config
{
    public const MAX_RECURSION_LOOPS = 25;

    private static ?Store $store = null;

    private Store $recursionCounter;

    public function __construct()
    {
        self::createStore();
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

    protected static function createStore(): void
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

    public function loadFiles(array $files): void
    {
        $settings = [];
        foreach ($files as $file) {
            $settings = array_merge($settings, $this->readFile($file));
        }

        $this->updateConfig($this->parseConfigArray($settings));
    }

    public function loadFile(string $file): void
    {
        $config = $this->getConfigFromFile($file);

        $this->updateConfig($config);
    }

    /**
     * Read config from a file and parse it into a usable structure
     *
     * @param  string  $file  Filepath of config file
     *
     * @return array
     */
    protected function getConfigFromFile(string $file): array
    {
        $settings = $this->readFile($file);

        return $this->parseConfigArray($settings);
    }

    /**
     * Update the config data store with new values
     *
     * @param  array  $config
     */
    protected function updateConfig(array $config): void
    {
        self::$store = self::$store->mergeRecursively($config);
    }

    protected function readFile(string $filename): array
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
        if ($extension === 'yaml' || $extension === 'yml') {
            $settings = Yaml::parseFile($filename);
            if (is_array($settings)) {
                return $settings;
            }
        }
        return [];
    }

    /**
     * @return Store
     */
    public static function store(): Store
    {
        self::createStore();
        return self::$store;
    }

    /**
     * @return array
     */
    public function values(): array
    {
        return $this->store()->toArray();
    }

    /**
     * @param  string  $key
     *
     * @return array|mixed|null
     */
    public function value(string $key): mixed
    {
        return $this->store()->get($key);
    }
}
