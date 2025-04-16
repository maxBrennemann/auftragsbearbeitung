<?php

namespace Classes\Project;

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
        $userId = $_SESSION['user_id'];
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

        $value = Config::get("filterOrderPosten_$userId");

        if ($value == null) {
            Config::add("filterOrderPosten_$userId", $setTo);
        } else {
            Config::set("filterOrderPosten_$userId", $setTo);
        }

        JSONResponseHandler::returnOK();
    }

    public static function createBackup()
    {
        $result = DBAccess::EXPORT_DATABASE($_ENV["HOST"], $_ENV["USERNAME"], $_ENV["PASSWORD"], $_ENV["DATABASE"]);

        $filePath = "files/generated/sql_backups/";
        $fileName = date("d-m-Y_h-i-s") . ".sql";
        file_put_contents(($filePath . $fileName), $result);

        $data =  [
            "filename" => $fileName,
            "url" => Link::getResourcesShortLink($fileName, "backup"),
            "status" => "success",
        ];

        JSONResponseHandler::sendResponse([
            "data" => $data,
        ]);
    }
}
