<?php

namespace Classes\Project;

use Classes\DBAccess;

class ClientSettings
{

    public static function setGrayScale($color, $type)
    {
        $userId = $_SESSION['userid'];
        DBAccess::updateQuery("UPDATE color_settings SET color = '$color' WHERE userid = $userId AND type = $type");
    }

    public static function getColorConfiguration()
    {
        $color_table = "d8d8d8";
        $color_def = "eff0f1";
        $color_innerDef = "b1b1b1";

        if (isset($_SESSION['userid'])) {
            $userId = $_SESSION['userid'];
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
        $userId = $_SESSION['userid'];
        $value = GlobalSettings::getSetting("filterOrderPosten_$userId");

        if ($value == "true") {
            return true;
        } else {
            return false;
        }
    }

    public static function setFilterOrderPosten()
    {
        $setTo = $_POST["value"];

        $userId = $_SESSION['userid'];
        $value = GlobalSettings::getSetting("filterOrderPosten_$userId");

        if ($value == null) {
            GlobalSettings::addSetting("filterOrderPosten_$userId", $setTo);
        } else {
            GlobalSettings::changeSetting("filterOrderPosten_$userId", $setTo);
        }
    }
}
