<?php

namespace Src\Classes\Project;

use Src\Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;
use ZipArchive;

class ClientSettings
{
    public static function getFilterOrderPosten(): bool
    {
        $userId = User::getCurrentUserId();
        $value = Settings::get("invoice.filterOrderItems", $userId);

        if ($value == "true") {
            return true;
        } else {
            return false;
        }
    }

    public static function setFilterOrderPosten(): void
    {
        $status = (bool) Tools::get("status");
        $userId = User::getCurrentUserId();

        Settings::set("invoice.filterOrderItems", $status, $userId);

        JSONResponseHandler::returnOK();
    }

    public static function createBackup(): void
    {
        $host = $_ENV["DB_HOST"];
        $database = $_ENV["DB_DATABASE"];
        $username = $_ENV["DB_USERNAME"];
        $password = $_ENV["DB_PASSWORD"];
        $result = DBAccess::EXPORT_DATABASE($host, $username, $password, $database, false, false, false);

        $filePath = Config::get("paths.generatedDir");
        $fileName = date("d-m-Y_h-i-s") . ".sql";
        file_put_contents($filePath . $fileName, $result);

        JSONResponseHandler::sendResponse([
            "filename" => $fileName,
            "url" => Link::getResourcesShortLink($fileName, "backup"),
            "status" => "success",
        ]);
    }

    public static function createFileBackup(): void
    {
        if (!extension_loaded("zip")) {
            JSONResponseHandler::throwError(500, "Unable to zip files.");
        }

        $filePath = Config::get("paths.uploadDir.default");
        $fileName = date("d-m-Y_h-i-s") . ".zip";
        $zip = new ZipArchive();
        if (!$zip->open($filePath . $fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            JSONResponseHandler::throwError(500, "Unable to zip files.");
        }

        $sourceDir = realpath($filePath);
        if ($sourceDir === false) {
            JSONResponseHandler::throwError(500, "Unable to process files.");
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            if ($filePath === false) {
                continue;
            }

            $relativePath = substr($filePath, strlen($sourceDir) + 1);

            $filename = $file->getFilename();
            if ($filename === ".gitkeep") {
                continue;
            }

            if (!$file->isDir()) {
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        JSONResponseHandler::sendResponse([
            "filename" => $fileName,
            "url" => Link::getResourcesShortLink($fileName, "backup"),
            "status" => "success",
        ]);
    }
}
