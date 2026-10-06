<?php

namespace Src\Classes\Project;

class Crypto
{
    public static function encrypt(string $plaintext): string
    {
        $key = self::getKey();
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);

        return base64_encode($nonce . $ciphertext);
    }

    public static function decrypt(string $encoded): string
    {
        $key = self::getKey();
        $decoded = base64_decode($encoded, true);

        if ($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new \RuntimeException("Invalid encrypted value");
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($plaintext === false) {
            throw new \RuntimeException("Failed to decrypt value - wrong key or corrupted data");
        }

        return $plaintext;
    }

    private static function getKey(): string
    {
        $encoded = $_ENV["SETTINGS_ENCRYPTION_KEY"] ?? "";
        if ($encoded === "") {
            throw new \RuntimeException("SETTINGS_ENCRYPTION_KEY is not configured");
        }

        $key = base64_decode($encoded, true);
        if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \RuntimeException("SETTINGS_ENCRYPTION_KEY must be a base64-encoded " . SODIUM_CRYPTO_SECRETBOX_KEYBYTES . "-byte key");
        }

        return $key;
    }
}
