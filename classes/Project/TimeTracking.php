<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class TimeTracking
{

    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = (int) $userId;

        if ($this->userId <= 0) {
            throw new \Exception("Invalid user id");
        }
    }

    /**
     * returns time table of current user in this month
     */
    public function current(int $userId = -1)
    {
        return self::month(0, $userId);
    }

    public static function month(int $month, int $userId = -1)
    {
        $query = "SELECT started_at, 
                stopped_at, 
                duration_ms, 
                MONTHNAME(started_at) AS month_started, 
                task,
                edit_log 
            FROM user_timetracking 
            WHERE user_id = :userId 
                AND MONTH(started_at) = MONTH(CURDATE());";
        $data = DBAccess::selectQuery($query, ["userId" => $userId]);
        return $data;
    }

    public function toTable() {}

    public function sum(int $month = 0) {}

    public function getMonthsOverview()
    {
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
                DATE_FORMAT(started_at, '%H:%i:%s %d.%m.%Y') AS `start`, 
                DATE_FORMAT(stopped_at, '%H:%i:%s %d.%m.%Y') AS `stop`, 
                YEAR(started_at) AS `year`,
                time_format(sec_to_time(duration_ms / 1000), '%H:%i:%s') AS `time`,
                MONTHNAME(started_at) AS month_started,
                task AS `task`, 
                edit_log AS `edit`
            FROM user_timetracking 
            WHERE user_id = :idUser
            ORDER BY started_at DESC;";
        $data = DBAccess::selectQuery($query, [
            "idUser" => $this->userId,
        ]);

        $months = [];
        foreach ($data as $row) {
            $month = $matchMonths[$row["month_started"]] . " " . $row["Jahr"];
            if (isset($months[$month])) {
                $months[$month][] = $row;
            } else {
                $months[$month] = [$row];
            }
        }

        return $months;
    }

    public function getTimeTables($start, $stop, $all = false)
    {
        $params =  [
            "idUser" => $this->userId,
        ];

        $dateQuery = "";
        if ($all == false) {
            $dateQuery = "AND DATE(started_at) >= :start
            AND DATE(stopped_at) <= :stop";
            $params["start"] = $start;
            $params["stop"] = $stop;
        }

        /* https://stackoverflow.com/questions/61990604/sql-how-to-convert-milliseconds-from-a-table-to-hhmmss-in-sql-query */
        $query = "SELECT
                id,
                DATE_FORMAT(started_at, '%H:%i:%s') AS `start`, 
                DATE_FORMAT(stopped_at, '%H:%i:%s') AS `stop`,
                time_format(sec_to_time(duration_ms / 1000), '%H:%i:%s') AS `time`,
                DATE_FORMAT(started_at, '%d.%m.%Y') as `date`,
                task AS `task`, 
                edit_log AS `edit`
            FROM user_timetracking 
            WHERE user_id = :idUser
                $dateQuery
            ORDER BY started_at DESC;";
        $data = DBAccess::selectQuery($query, $params);
        return $data;
    }

    public function matchMonths($data)
    {
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

        foreach ($data as &$row) {
            $month = $matchMonths[$row["month_started"]];
            $row["month_started"] =  $month;
        }

        return $data;
    }

    public function addEntry(int $start, int $stop, string $task): int
    {
        date_default_timezone_set("Europe/Berlin");
        $durationMs = $stop - $start;

        $start = (int) floor($start / 1000);
        $stop = (int) floor($stop / 1000);

        $start = date("Y-m-d H:i:s", $start);
        $stop = date("Y-m-d H:i:s", $stop);

        $query = "INSERT INTO user_timetracking (`user_id`, started_at, stopped_at, duration_ms, task) VALUES (:userId, :start, :stop, :durationMs, :task);";

        $queryId = (int) DBAccess::insertQuery($query, [
            "userId" => $this->userId,
            "start" => $start,
            "stop" => $stop,
            "durationMs" => $durationMs,
            "task" => $task,
        ]);

        return $queryId;
    }

    public function isOwner($id) {}
}
