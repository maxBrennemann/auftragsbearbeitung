<?php

namespace Classes\Project;

use ZipArchive;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

use Classes\Link;

class ClientSettings
{

    public static function setGrayScale()
    {
        $color = Tools::get("color");
        $type = Tools::get("type");

        $userId = User::getCurrentUserId();
        DBAccess::updateQuery("UPDATE color_settings SET color = '$color' WHERE userid = $userId AND type = $type");

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function getColorConfiguration()
    {
        $color_table = "d8d8d8";
        $color_def = "eff0f1";
        $color_innerDef = "b1b1b1";

        if (User::getCurrentUserId() !== -1) {
            $userId = User::getCurrentUserId();
            $data = DBAccess::selectQuery("SELECT color, `type` FROM color_settings WHERE userid = $userId");

            foreach ($data as $d) {
                switch ($d['type']) {
                    case "1":
                        if ($d['color'] != "")
                            $color_table = $d['color'];
                        break;
                    case "2":
                        if ($d['color'] != "")
                            $color_def = $d['color'];
                        break;
                    case "3":
                        if ($d['color'] != "")
                            $color_innerDef = $d['color'];
                        break;
                }
            }
        }

        $colorCSS = ":root {
            --main-table-color: #$color_table;
            --main-def-color: #$color_def;
            --main-inner-def-color: #$color_innerDef;
        }";

        echo $colorCSS;
    }

    public static function getFilterOrderPosten(): bool
    {
        $userId = User::getCurrentUserId();
        $value = Config::get("filterOrderPosten_$userId");

        if ($value == "true") {
            return true;
        } else {
            return false;
        }
    }

    public static function setFilterOrderPosten()
    {
        $setTo = Tools::get("value");
        $userId = $_SESSION["user_id"];

        Config::set("filterOrderPosten_$userId", $setTo);

        JSONResponseHandler::returnOK();
    }

    public static function createBackup()
    {
        $host = $_ENV["DB_HOST"];
        $database = $_ENV["DB_DATABASE"];
        $username = $_ENV["DB_USERNAME"];
        $password = $_ENV["DB_PASSWORD"];
        $result = DBAccess::EXPORT_DATABASE($host, $username, $password, $database, false, false, false);

        $filePath = "generated/";
        $fileName = date("d-m-Y_h-i-s") . ".sql";
        file_put_contents($filePath . $fileName, $result);

        JSONResponseHandler::sendResponse([
            "filename" => $fileName,
            "url" => Link::getResourcesShortLink($fileName, "backup"),
            "status" => "success",
        ]);
    }

    public static function createFileBackup()
    {
        if (!extension_loaded("zip")) {
            JSONResponseHandler::throwError(500, "Unable to zip files.");
            return;
        }

        $filePath = "generated/";
        $fileName = date("d-m-Y_h-i-s") . ".zip";
        $zip = new ZipArchive();
        if (!$zip->open($filePath . $fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            JSONResponseHandler::throwError(500, "Unable to zip files.");
            return;
        }

        $sourceDir = realpath("upload/");
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($sourceDir) + 1);

            $filename = $file->getFilename();
            if ($filename === '.gitkeep') {
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
            "id" => Config::get("companyLogo"),
        ]);

        return $data[0]["dateiname"] ?? "";
    }

    public static function addLogo()
    {
        $uploadHandler = new UploadHandler("upload", [
            "image/png",
            "image/jpg",
            "image/jpeg",
        ], 25000000, 1);
        $fileData = $uploadHandler->uploadMultiple();

        if (count($fileData) == 0) {
            JSONResponseHandler::throwError(422, "unsupported file type");
            return;
        }

        Config::set("companyLogo", $fileData[0]["id"]);

        JSONResponseHandler::sendResponse([
            "logoId" => $fileData[0]["id"],
            "file" => Link::getResourcesShortLink($fileData[0]["saved_name"], "upload"),
        ]);
    }
}
