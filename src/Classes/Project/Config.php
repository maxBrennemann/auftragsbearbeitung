<?php

namespace Src\Classes\Project;

use Src\Classes\I18n\I18n;

class Config
{

    /** @var array<string, mixed> */
    private static array $config = [];
    /** @var array<string, array{scope: string, type: string, default: mixed}> */
    private static array $settings = [];

    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Config file not found: $path");
        }

        $config = require $path;

        /* settings are used for defining user variables that are stored in the database */
        if (isset($config["settings"])) {
            self::$settings = $config["settings"];
            unset($config["settings"]);
        }

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

    /**
     * @return array<string, array{scope: string, type: string, default: mixed}>
     */
    public static function getSettings(): array
    {
        return self::$settings;
    }

    public static function loadLanguages(): void
    {
        $_ENV["i18n"] = new I18n(ROOT . 'src/Classes/I18n/lang', "de", "de");

        if (self::get("locale")) {
            $_ENV["i18n"]->setLocale(self::get("locale"));
        }
    }
}
