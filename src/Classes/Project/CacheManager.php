<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class CacheManager
{
    private const CACHE_PREFIX = "cache_";
    private static string $status = "off";
    private static string $cacheDir = "";

    private static function cacheDir(): string
    {
        if (self::$cacheDir === "") {
            self::$cacheDir = Config::get("paths.cacheDir");
        }

        return self::$cacheDir;
    }

    public function recache(): void
    {
        $cacheFile = self::cacheDir() . self::CACHE_PREFIX . md5($_SERVER["REQUEST_URI"]) . ".txt";
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    public static function cacheOff(): bool
    {
        Settings::set("cacheStatus", false);
        return true;
    }

    public static function cacheOn(): bool
    {
        return true;
        // Cache is temporarily disabled
        //Settings::set("cacheStatus", true);
    }

    public static function getCacheStatus(): string
    {
        $status = Settings::get("cacheStatus");

        self::$status = $status ? "on" : "off";
        return self::$status;
    }

    public static function writeCache(): void
    {
        $cacheFile = self::cacheDir() . self::CACHE_PREFIX . md5($_SERVER["REQUEST_URI"]) . ".txt";
        file_put_contents($cacheFile, ob_get_contents());
    }

    /**
     * simple caching from:
     * https://www.a-coding-project.de/ratgeber/php/simples-caching
     * added a time stamp check and added triggers to recreate page
     */
    public static function loadCacheIfExists(): void
    {
        if (self::$status == "off") {
            return;
        }

        self::cacheHandler();

        if (count($_GET) > 0 || count($_POST) > 0) {
            return;
        }

        $cacheFile = self::cacheDir() . self::CACHE_PREFIX . md5($_SERVER["REQUEST_URI"]) . ".txt";
        if (file_exists($cacheFile)) {
            header("X-Cache: HIT");
            $content = file_get_contents_utf8($cacheFile);

            if ($content === false) {
                exit;
            }

            global $start;
            $duration = microtime(true) - $start;

            $content = str_replace('{{LOAD_TIME}}', "<data value='$duration' id='loadtime'></data>", $content);

            echo $content;
            exit;
        }
    }

    public static function cacheHandler(): void
    {
        ob_start();
        register_shutdown_function(function () {
            if (self::$status == "on") {
                CacheManager::writeCache();
            }
            ob_end_flush();
        });
    }

    public static function deleteCache(): void
    {
        $files = scandir(self::cacheDir());
        $files = array_diff(scandir(self::cacheDir()), [
            ".",
            "..",
            ".gitkeep",
            "modules",
        ]);

        foreach ($files as $file) {
            if (is_file(self::cacheDir() . $file)) {
                unlink(self::cacheDir() . $file);
            }
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function toggleCache(): void
    {
        $status = strtolower(trim((string) Tools::get("status")));
        if (!in_array($status, ["on", "off"])) {
            JSONResponseHandler::throwError(400, "Unsupported status type");
        }

        $response = "failure";

        switch ($status) {
            case "on":
                if (self::cacheOn() == true) {
                    $response = "success";
                }
                break;
            case "off":
                if (self::cacheOff() == true) {
                    $response = "success";
                }
                break;
            default:
                JSONResponseHandler::throwError(400, "Unsupported status type");
        }

        JSONResponseHandler::sendResponse([
            "status" => $response,
        ]);
    }
}
