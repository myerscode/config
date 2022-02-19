<?php

namespace Myerscode\Config;

use Myerscode\Config\Exceptions\ConfigException;
use Myerscode\Config\Exceptions\ResolveVariablesDecodeException;
use Myerscode\Utilities\Files\Utility as FileService;
use Myerscode\Utilities\Strings\Utility as StringService;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    public const MAX_RECURSION_LOOPS = 25;

    private ?Store $store;

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

    public function reset(): void
    {
        $this->store = null;
        $this->createStore();
    }

    protected function createStore(): void
    {
        if (!isset(self::$store)) {
            $this->store = new Store([]);
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

    protected function serialize(array $array): string
    {
        return (new JsonEncode())->encode(
            $array,
            JsonEncoder::FORMAT,
            ['json_encode_options' => JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS ]
        );
    }

    protected function deserialize(string $config): array
    {
        return (new JsonEncoder())->decode(
            $config,
            JsonEncoder::FORMAT,
            ['json_decode_options' => JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS ]
        );
    }

    private function resolveVariables(Store $repo)
    {
        $configTemplate = $this->serialize($repo->toArray());

        $updatedTemplate = $this->resolveValues($configTemplate, $repo);

        try {
            return $this->deserialize($updatedTemplate);
        } catch (NotEncodableValueException $e) {
            throw new ResolveVariablesDecodeException(
                $repo->toArray(),
                $configTemplate,
                $updatedTemplate,
                $e
            );
        }
    }

    private function escapeJsonString(string $value): string
    {
        $escape = ["\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c"];
        $with = ["\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b"];

        return str_replace($escape, $with, $value);
    }

    private function resolveValues($template, Store $repo)
    {
        return preg_replace_callback('/\${([a-zA-Z0-9_.]+)}/', function (array $matches) use ($repo) {
            $configValue = $this->resolveConfigValue($matches[1], $repo);

            return !is_null($configValue) ? $this->escapeJsonString($configValue) : $matches[0];
        }, $template);
    }

    private function resolveConfigValue(string $key, Store $repo)
    {
        $this->recursionCounter->set($key, $this->recursionCounter->get($key, 0) + 1);

        if ($this->recursionCounter->get($key) >= Config::MAX_RECURSION_LOOPS) {
            throw new ConfigException("Config key $key is referencing a value which has a caused a recursion error");
        }

        $value = $repo->get($key);

        if (is_null($value) && is_null($value = $this->store()->get($key))) {
            return null;
        }

        if (!is_string($value)) {
            throw new ConfigException("A config value can only reference another string");
        }

        return $this->resolveValues($value, $repo);
    }

    protected function getFileNameSpace(string $file): string
    {
        return (new StringService(pathinfo($file, PATHINFO_FILENAME)))->toSnakeCase()->value();
    }

    public function loadFiles(array $files): void
    {
        $settings = [];
        foreach ($files as $file) {
            $settings = array_merge($settings, $this->readFile($file));
        }

        $this->updateConfig($this->parseConfigArray($settings));
    }

    public function loadFilesWithNamespace(array $files): void
    {
        $settings = [];
        foreach ($files as $file) {
            $settingNamespace = $this->getFileNameSpace($file);
            $settings = array_merge($settings, [$settingNamespace => $this->readFile($file)]);
        }

        $this->updateConfig($this->parseConfigArray($settings));
    }

    public function loadFile(string $file): self
    {
        $config = $this->getConfigFromFile($file);

        $this->updateConfig($config);

        return $this;
    }
    public function loadFileWithNameSpace(string $file): self
    {
        $settingNamespace = $this->getFileNameSpace($file);

        $config = $this->getConfigFromFile($file);

        $this->updateConfig([$settingNamespace => $config]);

        return $this;
    }

    /**
     * Load in data from an existing Config or array
     *
     * @param $data
     */
    public function loadData($data): void
    {
        if ($data instanceof Config) {
            $configData = $data->values();
        } elseif ($data instanceof Store) {
            $configData = $data->toArray();
        } else {
            $configData = $data;
        }

        $config = $this->parseConfigArray($configData);

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
        $this->store = $this->store->mergeRecursively($config);
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
    public function store(): Store
    {
        return $this->store;
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
     * @param  null  $default
     *
     * @return mixed
     */
    public function value(string $key, $default = null)
    {
        return $this->store()->get($key, $default);
    }
}
