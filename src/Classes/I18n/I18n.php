<?php
declare(strict_types=1);

namespace Src\Classes\I18n;

final class I18n
{
    /** @var array<string, array<string, mixed>> */
    private static array $cache = [];

    public function __construct(
        private readonly string $langDir,
        private string $locale = 'de',
        private readonly string $fallbackLocale = 'en',
    ) {}

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @param string $key
     * @param array $params
     * @return string
     */
    public function t(string $key, array $params = []): string
    {
        $value = $this->get($this->locale, $key);

        if ($value === null) {
            $value = $this->get($this->fallbackLocale, $key);
        }

        $text = is_string($value) ? $value : "";

        foreach ($params as $pKey => $pVal) {
            $text = str_replace('{' . $pKey . '}', (string)$pVal, $text);
        }

        return $text;
    }

    private function get(string $locale, string $key): mixed
    {
        $dict = $this->load($locale);

        $parts = explode('.', $key);
        $cur = $dict;

        foreach ($parts as $part) {
            if (!is_array($cur) || !array_key_exists($part, $cur)) {
                return null;
            }
            $cur = $cur[$part];
        }

        return $cur;
    }

    /** @return array<string, mixed> */
    private function load(string $locale): array
    {
        if (isset(self::$cache[$locale])) {
            return self::$cache[$locale];
        }

        $file = rtrim($this->langDir, '/\\') . DIRECTORY_SEPARATOR . $locale . '.php';

        if (!is_file($file)) {
            self::$cache[$locale] = [];
            return self::$cache[$locale];
        }

        $data = require $file;

        self::$cache[$locale] = is_array($data) ? $data : [];
        return self::$cache[$locale];
    }
}
