<?php

class TimeTracking {

    function __construct() {
        
    }

    /**
     * returns time table of current user in this month
     */
    public static function current(int $userId = -1) {
        return self::month(0, $userId);
    }

    /**
     * 
     */
    public static function month(int $month, int $userId = -1) {
        $query = "SELECT started_at, stopped_at, duration_ms, MONTHNAME(started_at) AS month_started, task, edit_log FROM user_timetracking WHERE user_id = :userId and MONTH(started_at) = MONTH(CURDATE());";
        $data = DBAccess::selectQuery($query, ["userId" => $userId]);
        return $data;
    }

    public static function toTable() {

    }

    public static function sum(int $month = 0) {

    }

    //temp
    public static function getTimeTables($idUser) {
        $column_names = array(
            0 => array("COLUMN_NAME" => "Beginn"),
            1 => array("COLUMN_NAME" => "Ende"),
            2 => array("COLUMN_NAME" => "Zeit"),
            3 => array("COLUMN_NAME" => "Aufgabe"),
            4 => array("COLUMN_NAME" => "Bearbeitungsnotiz"),
        );

        $matchMonths = [
            "January" => "Januar",
            "February" => "Februar",
            "March" => "März",
            "April" => "April",
            "May" => "Mai",
            "June" => "Juni",
            "July" => "Juli",
            "August" => "August",
            "September" => "September",
            "October" => "Oktober",
            "November" => "November",
            "December" => "Dezember",
        ];

        /* https://stackoverflow.com/questions/61990604/sql-how-to-convert-milliseconds-from-a-table-to-hhmmss-in-sql-query */
        $query = "SELECT 
                DATE_FORMAT(started_at, '%H:%i:%s %d.%m.%Y') AS Beginn, 
                DATE_FORMAT(stopped_at, '%H:%i:%s %d.%m.%Y') AS Ende, 
                YEAR(started_at) AS Jahr,
                time_format(sec_to_time(duration_ms / 1000), '%H:%i:%s') AS Zeit, 
                MONTHNAME(started_at) AS month_started, task AS Aufgabe, 
                edit_log AS Bearbeitungsnotiz 
            FROM user_timetracking 
            WHERE user_id = :idUser
            ORDER BY started_at DESC;";
        $data = DBAccess::selectQuery($query, ["idUser" => $idUser]);

        $months = [];
        foreach ($data as $row) {
            $month = $matchMonths[$row["month_started"]] . " " . $row["Jahr"];
            if (isset($months[$month])) {
                $months[$month][] = $row;
            } else {
                $months[$month] = [$row];
            }
        }

        $timeTables = [];
        foreach ($months as $month => $entries) {
            $t = new Table();
            $t->createByData($entries, $column_names);
            $timeTables[$month] = $t->getTable();
        }

        return $timeTables;
    }

    /**
     * this function is called from Ajax.php,
     * it adds a new entry to the database and calculates the duration
     */
    public static function addEntry() {
        $start = (int) $_POST["startTime"];
        $stop = (int) $_POST["stopTime"];
        $task = $_POST["task"];

        $durationMs = $stop - $start;

        /**
         * $start and $stop are passed as unix timestamps in milliseconds,
         * strtotime() expects seconds, so we have to divide by 1000,
         * also we have to add 2 hours because of the timezone,
         * otherwise the time would be 2 hours behind, because UTC is 2 hours behind UTC+2 (Berlin),
         * gmdate() is used to get the date in UTC
         */
        $start = gmdate("Y-m-d H:i:s", strtotime('+2 hours', $start / 1000));
        $stop = gmdate("Y-m-d H:i:s", strtotime('+2 hours', $stop / 1000));

        $query = "INSERT INTO user_timetracking (user_id, started_at, stopped_at, duration_ms, task) VALUES (:userId, :start, :stop, :durationMs, :task);";

        $queryId = DBAccess::insertQuery($query, [
            "userId" => $_SESSION["userid"],
            "start" => $start,
            "stop" => $stop,
            "durationMs" => $durationMs,
            "task" => $task,
        ]);

        echo json_encode([
            "id" => $queryId,
            "start" => $start,
            "stop" => $stop,
            "durationMs" => $durationMs,
            "task" => $task,
        ]);
    }

}
