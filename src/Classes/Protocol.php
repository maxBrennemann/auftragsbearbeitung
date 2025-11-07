<?php

namespace Src\Classes;

class Protocol
{
    /** @var resource|null */
    private static $file;
    private static string $filePath = ROOT . "storage/logs/protocol.txt";
    private static int $maxSize = 10 * 1024 * 1024;
    private static bool $logToConsole = false;

    public static function configure(string $filePath = ROOT . "storage/logs/protocol.txt", bool $logToConsole = false): void
    {
        self::$filePath = $filePath;
        self::$logToConsole = $logToConsole;
    }

    private static function init(): void
    {
        if (self::$file == null) {
            $filesize = filesize(self::$filePath);

            if ($filesize > self::$maxSize) {
                file_put_contents(self::$filePath, "");
            }

            $file = fopen(self::$filePath, "a");
            if ($file !== false) {
                self::$file = $file;
            }
            register_shutdown_function([self::class, "close"]);
        }
    }

    /**
     * Writes a given string to the protocol file
     *
     * @param $text string
     */
    public static function write(string $message, ?string $details = null, string $level = "INFO"): void
    {
        self::init();

        $timestamp = date("Y-m-d H:i:s");
        $logLine = "[$timestamp] [$level] $message";
        if ($details !== null) {
            $logLine .= " - $details";
        }
        $logLine .= PHP_EOL;

        fwrite(self::$file, $logLine);

        if (self::$logToConsole) {
            echo nl2br(htmlentities($logLine));
        }
    }

    /**
     * Closes the protocol file
     */
    public static function close(): void
    {
        if (self::$file !== null) {
            fclose(self::$file);
            self::$file = null;
        }
    }

    public static function delete(): void
    {
        self::close();
        if (file_exists(self::$filePath)) {
            unlink(self::$filePath);
        }
    }

    /**
     * Pretty prints a given data structure
     * @param array<mixed, mixed> $data
     */
    public static function prettyPrint(array $data): void
    {
        echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";
    }
}
