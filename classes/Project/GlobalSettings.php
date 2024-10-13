<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class GlobalSettings
{

    public static function getSetting($title)
    {
        $query = "SELECT content FROM settings WHERE title = '$title'";
        $response = DBAccess::selectQuery($query);
        if ($response != null) {
            return $response[0]["content"];
        }
        return null;
    }

    public static function addSetting($title, $content)
    {
        if (self::getSetting($title) == null) {
            $query = "INSERT INTO settings (title, content) VALUES ('$title', '$content')";
            DBAccess::insertQuery($query);
            return true;
        }
        return false;
    }

    public static function changeSetting($title, $content)
    {
        if (self::getSetting($title) != null) {
            $query = "UPDATE settings SET content = '$content' WHERE title = '$title'";
            DBAccess::updateQuery($query);
            return true;
        }
        return false;
    }
}
