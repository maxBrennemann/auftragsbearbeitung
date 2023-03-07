<?php

class Envs {

    /**
     * 
     */
    public static function add(String $setting, String $defaultValue = null, bool $isBool = false, bool $isNullable = false) {
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
     * 
     */
    public static function set(String $setting, String $value = null) {
        $query = "UPDATE `settings` SET `content` = CASE
                WHEN `isNullable` = 1 AND :value IS NULL THEN `defaultValue`
                ELSE :value
                END
            WHERE `title` = :setting;";
        DBAccess::updateQuery($query, [
            "value" => $value,
            "setting" => $setting,
        ]);
    }

    /**
     * 
     */
    public static function get(String $title) {
        $query = "SELECT `content` FROM `settings` WHERE `title` = :title LIMIT 1;";
        $value = DBAccess::selectQuery($query, ["title" => $title]);

        if (sizeof($value) == 0) {
            return null;
        }
            
        return $value[0]["content"];
    }

    /**
     * 
     */
    public static function exists() {

    }

    public static function delete() {

    }

    public static function toggle(String $title) {
        $value = self::get($title);
        $value = $value == "true" ? "false" : "true";
        self::set($title, $value);
    }
}

?>