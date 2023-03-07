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
                DATE_FORMAT(started_at, '%h:%i:%s %d.%m.%Y') AS Beginn, 
                DATE_FORMAT(stopped_at, '%h:%i:%s %d.%m.%Y') AS Ende, 
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

}

?>