<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Settings
{
    /**
     * adds a new setting value
     * @param string $setting
     * @param string $defaultValue
     * @param bool $isBool
     * @param bool $isNullable
     * @param bool $isJSON
     * @return int the id of the setting value
     */
    private static function add(
        string $setting,
        string $defaultValue,
        bool $isBool = false,
        bool $isNullable = false,
        bool $isJSON = false
    ): int
    {
        $query = "REPLACE INTO `settings` (`title`, `content`, `defaultValue`, `isBool`, `isNullable`) VALUES (:title, :content, :defaultValue, :isBool, :isNullable, :isJSON);";

        $isBool = $isBool ? 1 : 0;
        $isNullable = $isNullable ? 1 : 0;
        $isJSON = $isJSON ? 1 : 0;

        return (int) DBAccess::insertQuery($query, [
            "title" => $setting,
            "content" => $defaultValue,
            "defaultValue" => $defaultValue,
            "isBool" => $isBool,
            "isNullable" => $isNullable,
            "isJSON" => $isJSON,
        ]);
    }

    /**
     * sets the content of a setting value,
     * if the setting value does not exist, it will be created
     * @param string $setting
     * @param string $value
     */
    public static function set(string $setting, string $value): void
    {
        $query = "UPDATE `settings`
            SET
                `content` = CASE
                    WHEN `isJSON` = 0 THEN :value1
                    ELSE NULL
                END,
                `json_content` = CASE
                    WHEN `isJSON` = 1 THEN :value2
                    ELSE NULL
                END
            WHERE `title` = :setting
        ";
        DBAccess::updateQuery($query, [
            "value1" => $value,
            "value2" => $value,
            "setting" => $setting,
        ]);

        if (DBAccess::getAffectedRows() == 0) {
            self::add($setting, $value);
        }
    }

    /**
     * gets the content of a setting value,
     * if the setting value does not exist, null is returned
     * @param string $title
     * @return string|null
     */
    public static function get(string $title): ?string
    {
        $query = "SELECT `content`, `json_content`, `isJSON` 
            FROM `settings` 
            WHERE `title` = :title 
            LIMIT 1;";
        $value = DBAccess::selectQuery($query, ["title" => $title]);

        if (sizeof($value) == 0) {
            return null;
        }

        if ((bool) $value[0]["isJSON"] === true) {
            return $value[0]["json_content"];
        }

        return $value[0]["content"];
    }

    /**
     * checks if a setting value exists
     */
    public static function exists(): void {}

    /**
     * deletes a setting value
     */
    public static function delete(): void {}

    /**
     * toggles a setting value between true and false
     * @param string $title
     * @return string the new value
     */
    public static function toggle(string $title): string
    {
        $value = self::get($title);
        $value = $value == "true" ? "false" : "true";
        self::set($title, $value);
        return $value;
    }

    public static function updateConfig(): void
    {
        $config = Tools::get("configName");
        $value = Tools::get("value");

        self::set($config, $value);
        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function getUserSetting(): void
    {
        $userId = Tools::get("userId");
        $userSetting = Tools::get("userSetting");
        
        if ($userId === "self") {
            $userId = User::getCurrentUserId();
        } else {
            $userId = (int) $userId;
        }

        $data = self::get("user_" . $userId . "_" . $userSetting);

        if ($data === null) {
            JSONResponseHandler::returnNotFound();
        }

        JSONResponseHandler::sendResponse([
            "data" => $data,
        ]);
    }
}
