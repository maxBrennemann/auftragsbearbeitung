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
        $value = Settings::get("filterOrderPosten_$userId");

        if ($value == "true") {
            return true;
        } else {
            return false;
        }
    }

    public static function setFilterOrderPosten(): void
    {
        $setTo = Tools::get("value");
        $userId = $_SESSION["user_id"];

        Settings::set("filterOrderPosten_$userId", $setTo);

        JSONResponseHandler::returnOK();
    }

    public static function createBackup(): void
    {
        $host = $_ENV["DB_HOST"];
        $database = $_ENV["DB_DATABASE"];
        $username = $_ENV["DB_USERNAME"];
        $password = $_ENV["DB_PASSWORD"];
        $result = DBAccess::EXPORT_DATABASE($host, $username, $password, $database, false, false, false);

        $filePath = "storage/generated/";
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

    public static function getLogo(): string
    {
        $query = "SELECT dateiname FROM dateien WHERE id = :id";
        $data = DBAccess::selectQuery($query, [
            "id" => Settings::get("companyLogo"),
        ]);

        return $data[0]["dateiname"] ?? "";
    }

    public static function addLogo(): void
    {
        $uploadHandler = new UploadHandler("default", [
            "image/png",
            "image/jpg",
            "image/jpeg",
        ], 25000000, 1);
        $fileData = $uploadHandler->uploadMultiple();

        if (count($fileData) == 0) {
            JSONResponseHandler::throwError(422, "unsupported file type");
        }

        Settings::set("companyLogo", $fileData[0]["id"]);

        JSONResponseHandler::sendResponse([
            "logoId" => $fileData[0]["id"],
            "file" => Link::getResourcesShortLink($fileData[0]["saved_name"], "upload"),
        ]);
    }
}
