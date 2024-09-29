<?php

namespace Classes\Project;

use Classes\DBAccess;

class Config
{

    /**
     * adds a new setting value
     * @param String $title
     * @param String $defaultValue
     * @param bool $isBool
     * @param bool $isNullable
     * @return int the id of the setting value
     */
    public static function add(String $setting, String $defaultValue = null, bool $isBool = false, bool $isNullable = false)
    {
        $query = "REPLACE INTO `settings` (`title`, `content`, `defaultValue`, `isBool`, `isNullable`) VALUES (:title, :content, :defaultValue, :isBool, :isNullable)";

        $isBool = $isBool ? 1 : 0;
        $isNullable = $isNullable ? 1 : 0;

        $settingId = DBAccess::insertQuery($query, [
            "title" => $setting,
            "content" => $defaultValue,
            "defaultValue" => $defaultValue,
            "isBool" => $isBool,
            "isNullable" => $isNullable,
        ]);

        return $settingId;
    }


    /**
     * sets the content of a setting value,
     * if the setting value does not exist, it will be created
     * @param String $title
     * @param String $value
     */
    public static function set(String $setting, String $value = null)
    {
        $query = "UPDATE `settings` SET `content` = CASE
                WHEN `isNullable` = 1 AND :value IS NULL THEN `defaultValue`
                ELSE :value
                END
            WHERE `title` = :setting;";
        DBAccess::updateQuery($query, [
            "value" => $value,
            "setting" => $setting,
        ]);

        if (DBAccess::getAffectedRows() == 0) {
            self::add($setting, $value);
        }
    }

    /**
     * gets the content of a setting value,
     * if the setting value does not exist, null is returned
     * @param String $title
     * @return String|null
     */
    public static function get(String $title): ?String
    {
        $query = "SELECT `content` FROM `settings` WHERE `title` = :title LIMIT 1;";
        $value = DBAccess::selectQuery($query, ["title" => $title]);

        if (sizeof($value) == 0) {
            return null;
        }

        return $value[0]["content"];
    }

    /**
     * checks if a setting value exists
     */
    public static function exists() {}

    /**
     * deletes a setting value
     */
    public static function delete() {}

    /**
     * toggles a setting value between true and false
     * @param String $title
     * @return String the new value
     */
    public static function toggle(String $title): String
    {
        $value = self::get($title);
        $value = $value == "true" ? "false" : "true";
        self::set($title, $value);
        return $value;
    }
}
