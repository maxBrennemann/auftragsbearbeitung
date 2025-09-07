<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class CacheManager
{
    private const CACHE_DIR = "cache/";
    private const CACHE_PREFIX = "cache_";

    public function recache()
    {
        $cacheFile = self::CACHE_DIR . self::CACHE_PREFIX . md5($_SERVER["REQUEST_URI"]) . ".txt";
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    public static function cacheOff()
    {
        return DBAccess::updateQuery("UPDATE settings SET content = 'off' WHERE title = 'cacheStatus'");
    }

    public static function cacheOn()
    {
        return DBAccess::updateQuery("UPDATE settings SET content = 'on' WHERE title = 'cacheStatus'");
    }

    public static function getCacheStatus(): string
    {
        $query = "SELECT content FROM settings WHERE `title` = 'cacheStatus'";
        $status = DBAccess::selectQuery($query);

        if (count($status) == 0) {
            return "off";
        }

        $status = $status[0]['content'];
        return (string) $status;
    }

    public static function writeCache()
    {
        $cacheFile = self::CACHE_DIR . self::CACHE_PREFIX . md5($_SERVER["REQUEST_URI"]) . ".txt";
        file_put_contents($cacheFile, ob_get_contents());
    }

    /**
     * simple caching from:
     * https://www.a-coding-project.de/ratgeber/php/simples-caching
     * added a time stamp check and added triggers to recreate page
     */
    public static function loadCacheIfExists()
    {
        if (defined(CACHE_STATUS) && CACHE_STATUS == "off") {
            return;
        }

        self::cacheHandler();

        if (count($_GET) > 0 || count($_POST) > 0) {
            return;
        }

        $cacheFile = self::CACHE_DIR . self::CACHE_PREFIX . md5($_SERVER["REQUEST_URI"]) . ".txt";
        if (file_exists($cacheFile)) {
            header("X-Cache: HIT");
            $content = file_get_contents_utf8($cacheFile);

            global $start;
            $duration = microtime(true) - $start;

            $content = str_replace('{{LOAD_TIME}}', "<script>console.log('Page loaded in {$duration} seconds');</script>", $content);

            echo $content;
            exit;
        }
    }

    public static function cacheHandler()
    {
        ob_start();
        register_shutdown_function(function () {
            if (defined(CACHE_STATUS) && CACHE_STATUS == "on") {
                CacheManager::writeCache();
            }
            ob_end_flush();
        });
    }

    public static function deleteCache()
    {
        $path = "cache/";
        $files = scandir($path);
        $files = array_diff(scandir($path), [
            ".",
            "..",
            ".gitkeep",
            "modules",
        ]);

        foreach ($files as $file) {
            if (is_file($path . $file)) {
                unlink($path . $file);
            }
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function toggleCache()
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
                break;
        }

        JSONResponseHandler::sendResponse([
            "status" => $response,
        ]);
    }
}
