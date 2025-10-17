<?php

namespace Src\Classes\Project;

class Config
{

    /** @var array<string, mixed> */
    private static array $config = [];

    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Config file not found: $path");
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new \RuntimeException("Invalid config file: $path must return an array");
        }

        self::$config = $config;
    }

    /**
     * @param string $key e.g. "paths.uploadDir.default"
     * @param string|null $default
     * @return string|null
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @param string $key e.g. "paths.uploadDir"
     * @return array<string, mixed>
     */
    public static function getGroup(string $key): array
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return [];
            }
            $value = $value[$k];
        }

        return is_array($value) ? $value : [];
    }

}
