<?php

namespace Src\Classes\Project;

class Config
{

    /** @var array<string, string> */
    private static array $config = [];

    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Config file not found: $path");
        }
        self::$config = require $path;
    }

    public static function get(string $key, $default = null): string
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

}
