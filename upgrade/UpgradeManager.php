<?php

namespace Upgrade;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

class UpgradeManager
{

    private static bool $forceUpdate = false;

    public static function upgrade($forceUpdate = false)
    {
        self::$forceUpdate = $forceUpdate;
        self::checkForInitialization();
        $matches = self::checkForSQLQueries();
        self::executeMatches($matches);
    }

    public static function downgrade() {}

    private static function checkForSQLQueries()
    {
        $query = "SELECT migration_date FROM migration_tracker ORDER BY migration_date DESC LIMIT 1";
        $data = DBAccess::selectQuery($query);
        $initDate = strtotime("1970-01-01");
        if ($data != null) {
            $initDate = strtotime($data[0]["migration_date"]);
        }

        $files = [];
        if (is_dir("upgrade/Changes")) {
            $files = scandir("upgrade/Changes");
            Tools::outputLog("found " . count($files) - 2 . " file(s)", "migration");
        } else {
            Tools::outputLog("migrations directory is missing", "migration", "warning");
        }

        $possibleMatches = [];

        foreach ($files as $file) {
            $parts = explode("_", $file, 2);
            if (count($parts) <= 1) {
                continue;
            }

            $date = $parts[0];
            $name = $parts[1];

            $migrationDate = strtotime($date);
            if ($migrationDate >= $initDate) {
                $possibleMatches[] = [
                    "date" => $date,
                    "name" => $name,
                    "fileName" => $file,
                ];
            }
        }

        $matches = [];
        foreach ($possibleMatches as $pMatch) {
            $query = "SELECT id FROM migration_tracker WHERE `migration_date` = :mDate AND `migration_name` = :mName LIMIT 1;";
            $data = DBAccess::selectQuery($query, [
                "mDate" => $pMatch["date"],
                "mName" => $pMatch["name"],
            ]);

            if ($data == null) {
                $matches[] = $pMatch;
            }
        }

        Tools::outputLog("found " . count($matches) . " match(es)", "migration");

        return $matches;
    }
    
    private static function executeMatches($matches)
    {
        foreach ($matches as $match) {
            $fileName = $match["fileName"];
            $anonymousUpdater = require "upgrade/Changes/" . $fileName;

            $queries = $anonymousUpdater->getQueries();
            Tools::outputLog("found " . count($queries) . " query(-ies) in " . $match["name"], "migration");

            $output = self::executeSQLQueries($queries, $match);
            if (!$output) {
                return;
            }
        }
    }

    private static function executeSQLQueries($queries, $match) {
        $noError = true;

        foreach ($queries as $query) {
            try {
                DBAccess::executeQuery($query);
            } catch (\Exception $e) {
                Tools::outputLog($e->getMessage(), "migration", "error");

                $noError = false;

                if (!self::$forceUpdate) {
                    return false;
                }
            }
        }

        if (self::$forceUpdate || $noError) {
            self::updateMigrationTracker($match);
        }

        return true;
    }

    private static function updateMigrationTracker($match) {
        $query = "INSERT INTO migration_tracker (migration_name, migration_date, file_name) VALUES (:mName, :mDate, :fName);";
        DBAccess::insertQuery($query, [
            "mDate" => $match["date"],
            "mName" => $match["name"],
            "fName" => $match["fileName"],
        ]);
    }

    private static function checkForInitialization()
    {
        $query = "SELECT * FROM information_schema.tables WHERE table_name = 'migration_tracker' LIMIT 1;";
        $data = DBAccess::selectQuery($query);

        if ($data == null) {
            self::init();
        }
    }

    private static function init()
    {
        Tools::outputLog("initializing migration", "migration");
        $query = "CREATE TABLE migration_tracker (
            id INT NOT NULL AUTO_INCREMENT,
            migration_name VARCHAR(255) NOT NULL,
            migration_date DATE,
            file_name VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB;";
        DBAccess::executeQuery($query);
    }
}
