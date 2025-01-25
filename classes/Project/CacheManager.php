<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class CacheManager
{

    public function recache()
    {
        $cacheFile = "cache/cache_" . md5($_SERVER['REQUEST_URI']) . ".txt";
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

    public static function deleteCache()
    {
        $path = "cache/";
        $files = scandir($path);
        $files = array_diff(scandir($path), [
            ".",
            "..",
            "index.php",
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
        $status = (string) Tools::get("status");
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

    public static function toggleMinify()
    {
        $status = (string) Tools::get("status");

        if ($status == "off" || $status == "on") {
            DBAccess::updateQuery("UPDATE settings SET content = :status 
            WHERE title = 'minifyStatus'", [
                "status" => $status
            ]);
            JSONResponseHandler::sendResponse([
                "status" => "success",
            ]);
        } else {
            JSONResponseHandler::throwError(400, "Unsupported status type");
        }
    }
}
