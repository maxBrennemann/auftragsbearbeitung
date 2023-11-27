<?php

require_once("upgrade/Console.php");

class UpgradeManager {

    public static function executeFirstCommand() {
        $command = self::getFirstCommand();
        $result = Console::execute($command);
        echo json_encode(["command" => $command, "result" => $result]);
    }

    private static function getFirstCommand() {
        return "git pull";
    }

    /* no longer needed */
    public static function executeSecondCommand($script) {
        if ($script == null || $script == "." || $script == ".." || $script == "index.php") {
            return ["command" => "empty", "result" => "script name is empty"];
        }
        $command = self::getSecondCommand() . ' ' . $script;
        $result = Console::execute($command);

        $date = substr($script, 0, 10);
        DBAccess::insertQuery("INSERT INTO upgrade_tracker (`date`, title) VALUES ('$date', '$script')");

        return ["command" => $command, "result" => $result];
    }

    private static function getSecondCommand() {
        return "mysql --user=" . $_ENV["USERNAME"] . " --password='" . $_ENV["PASSWORD"] . "' -h " . $_ENV["HOST"] . " -D  " . $_ENV["DATABASE"] . " < ";
    }

    public static function executeNewSQLQueries($file) {
        if ($file == null || $file == "." || $file == ".." || $file == "index.php") {
            return ["command" => "empty", "result" => "script name is empty"];
        }

        $date = substr($file, 0, 10);
        DBAccess::insertQuery("INSERT INTO upgrade_tracker (`date`, title) VALUES ('$date', '$file')");

        $anonymousUpdater = require("changes/" . $file);
        return ["command" => "$file", "result" => $anonymousUpdater->upgrade()];
    }

    public static function checkNewSQL() {
        $query = DBAccess::selectQuery("SELECT `date`, `title` FROM upgrade_tracker ORDER BY date DESC LIMIT 1");
        $directory = dirname(__FILE__) . "/changes";
        $files = scandir($directory);
        $upgrade = [];

        /* if there has never been made the upgrade */
        if ($query == null) {
            return $files;
        }

        foreach ($files as $f) {
            $substr = substr($f, 0, 10);
            if (strlen($substr) == 10) {
                $date = strtotime($substr);
                $comp = strtotime($query[0]["date"]);
                if ($date > $comp) {
                    array_push($upgrade, $f);
                }
            }
        }

        return $upgrade;
    }

}
