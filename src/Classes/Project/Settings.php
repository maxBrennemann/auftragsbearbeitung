<?php

namespace Src\Classes\Project;

use InvalidArgumentException;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Settings
{

    /**
     * @param string $setting
     * @return false|array{default: mixed, scope: string, type: string}
     */
    private static function validateSetting(string $setting): false|array
    {
        $settings = Config::getSettings();
        if (array_key_exists($setting, $settings)) {
            return $settings[$setting];
        }

        return false;
    }

    /**
     * sets the content of a setting value,
     * if the setting value does not exist, it will be created
     * @param string $setting
     * @param mixed $value
     * @param int $userId
     */
    public static function set(string $setting, mixed $value, int $userId = 0): void
    {
        $settings = self::validateSetting($setting);

        if ($settings == false) {
            throw new InvalidArgumentException("Setting $setting is not valid");
        }

        if ($settings["scope"] == "user" && $userId === 0){
            throw new InvalidArgumentException("User setting requires userId");
        }

        $type = $settings["type"];
        $column = "content";

        switch ($type) {
            case "string":
                $value = (string) $value;
                break;
            case "bool":
                $value = $value ? 1 : 0;
                $column = "numberContent";
                break;
            case "number":
                $value = (float) $value;
                $column = "numberContent";
                break;
            case "json":
                json_encode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException("Invalid JSON value");
                }
                $column = "jsonContent";
        }

        $user = ($settings["scope"] === "user") ? $userId : 0;
        $values = [
            "content" => null,
            "numbeRcontent" => null,
            "jsonContent" => null,
        ];

        $values[$column] = $value;

        DBAccess::updateQuery("INSERT INTO `config_settings` (`name`, `userId`, content, numberContent, jsonContent) 
            VALUES (:name, :user, :content, :numberContent, :jsonContent)
            ON DUPLICATE KEY UPDATE
                content = VALUES(content),
                numberContent = VALUES(numberContent),
                jsonContent = VALUES(jsonContent);", [
            "name" => $setting,
            "user" => $user,
            "content" => $values["content"],
            "numberContent" => $values["numberContent"],
            "jsonContent" => $values["jsonContent"],
        ]);
    }

    /**
     * gets the content of a setting value,
     * if the setting value does not exist, null is returned
     * @param string $setting
     * @return mixed
     */
    public static function get(string $setting, int $userId = 0): mixed
    {
        $settings = self::validateSetting($setting);

        if ($settings == false) {
            throw new InvalidArgumentException("Setting is not available");
        }

        if ($settings["scope"] == "global") {
            $userId = 0;
        }

        $column = "content";

        switch ($settings["type"]) {
            case "bool":
                $column = "numberContent";
                break;
            case "number":
                $column = "numberContent";
                break;
            case "json":
                $column = "jsonContent";
        }

        $query = "SELECT $column FROM config_settings WHERE `name` = :name AND userId = :user;";
        $data = DBAccess::selectQuery($query, [
            "name" => $setting,
            "user" => $userId,
        ]);

        if (empty($data)) {
            return $settings["default"];
        }

        $currentValue = $data[0][$column];
        switch ($settings["type"]) {
            case "bool":
                return (bool) $currentValue;
            case "number":
                return (float) $currentValue;
            case "json":
                return json_decode($currentValue, true);
        }

        return $currentValue;
    }

    /**
     * toggles a setting value between true and false
     * @param string $title
     * @return bool
     */
    public static function toggle(string $title, int $userId = 0): bool
    {
        $value = self::get($title, $userId);
        $value = !$value;

        self::set($title, $value, $userId);

        return $value;
    }

    public static function updateConfig(): void
    {
        $config = (string) Tools::get("configName");
        $value = Tools::get("value");
        $userId = User::getCurrentUserId();

        self::set($config, $value, $userId);

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

        $data = self::get($userSetting, $userId);

        if ($data === null) {
            JSONResponseHandler::returnNotFound();
        }

        JSONResponseHandler::sendResponse([
            "data" => $data,
        ]);
    }
}
