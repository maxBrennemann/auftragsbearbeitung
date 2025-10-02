<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Config
{
    /**
     * adds a new setting value
     * @param string $setting
     * @param string $defaultValue
     * @param bool $isBool
     * @param bool $isNullable
     * @return int the id of the setting value
     */
    private static function add(string $setting, string $defaultValue, bool $isBool = false, bool $isNullable = false): int
    {
        $query = "REPLACE INTO `settings` (`title`, `content`, `defaultValue`, `isBool`, `isNullable`) VALUES (:title, :content, :defaultValue, :isBool, :isNullable)";

        $isBool = $isBool ? 1 : 0;
        $isNullable = $isNullable ? 1 : 0;

        $settingId = (int) DBAccess::insertQuery($query, [
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
     * @param string $setting
     * @param string $value
     */
    public static function set(string $setting, string $value): void
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
     * @param string $title
     * @return string|null
     */
    public static function get(string $title): ?string
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

    /**
     * Summary of getCompanyDetails
     * @return array{companyAddress: string|null, companyBank: string|null, companyBic: string|null, companyCity: string|null, companyCountry: string|null, companyEmail: string|null, companyIban: string|null, companyImprint: string|null, companyName: string|null, companyPhone: string|null, companyUstIdNr: string|null, companyWebsite: string|null, companyZip: string|null}
     */
    public static function getCompanyDetails(): array
    {
        $companyDetails = [
            "companyName" => self::get("companyName"),
            "companyAddress" => self::get("companyAddress"),
            "companyZip" => self::get("companyZip"),
            "companyCity" => self::get("companyCity"),
            "companyCountry" => self::get("companyCountry"),
            "companyPhone" => self::get("companyPhone"),
            "companyEmail" => self::get("companyEmail"),
            "companyWebsite" => self::get("companyWebsite"),
            "companyImprint" => self::get("companyImprint"),
            "companyBank" => self::get("companyBank"),
            "companyIban" => self::get("companyIban"),
            "companyBic" => self::get("companyBic"),
            "companyUstIdNr" => self::get("companyUstIdNr"),
        ];

        return $companyDetails;
    }

    /**
     * @param string $path
     * @param array{count:int, size:float} $data
     * @return void
     */
    private static function getFilesInfoByPath(string $path, array &$data): void
    {
        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            if ($file->getFilename() === ".gitkeep") {
                continue;
            }

            $data["count"] += 1;
            $data["size"] += $file->getSize();
        }
    }

    public static function getFilesInfo(): void
    {
        $paths = [
            "upload/",
            "storage/generated/",
        ];
        $data = [
            "count" => 0,
            "size" => 0,
        ];

        foreach ($paths as $path) {
            self::getFilesInfoByPath($path, $data);
        }

        JSONResponseHandler::sendResponse([
            "count" => $data["count"],
            "size" => ceil($data["size"] / 1000000)
        ]);
    }
}
