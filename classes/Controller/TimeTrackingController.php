<?php

namespace Classes\Controller;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

use Classes\Project\User;
use Classes\Project\TimeTracking;
use Classes\Project\Config;

class TimeTrackingController
{

    private static function validateUser()
    {
        $userId = User::getCurrentUserId();
        if ($userId == -1) {
            return false;
        }

        return true;
    }

    public static function showTimeTracking(?int $id = null)
    {
        if (!self::validateUser()) {
            JSONResponseHandler::throwError(401, "Unvalidated user");
        }

        $start = Tools::get("start");
        $stop = Tools::get("stop");

        $all = Tools::get("all");
        $all = $all == "true";

        if ($start == null || $stop == null) {
            $all = true;
        }

        $userId = User::getCurrentUserId();
        $timeTracking = new TimeTracking($userId);
        $data = $timeTracking->getTimeTables($start, $stop, $all);

        JSONResponseHandler::sendResponse($data);
    }

    public static function showTimeTrackingOverview() {}

    public static function addEntry()
    {
        $start = (int) Tools::get("start");
        $stop = (int) Tools::get("stop");
        $task = (string) Tools::get("task");

        if (!self::validateUser()) {
            JSONResponseHandler::throwError(401, "Unvalidated user");
        }

        $userId = User::getCurrentUserId();
        $timeTracking = new TimeTracking($userId);
        $id = $timeTracking->addEntry($start, $stop, $task);
        $data = DBAccess::selectQuery("SELECT
                id,
                DATE_FORMAT(started_at, '%H:%i:%s') AS `start`, 
                DATE_FORMAT(stopped_at, '%H:%i:%s') AS `stop`,
                time_format(sec_to_time(duration_ms / 1000), '%H:%i:%s') AS `time`,
                DATE_FORMAT(started_at, '%d.%m.%Y') as `date`,
                task AS `task`, 
                edit_log AS `edit`
            FROM user_timetracking 
            WHERE id = :id
            ORDER BY started_at DESC;", [
            "id" => $id,
        ]);

        JSONResponseHandler::sendResponse($data[0]);
    }

    public static function editEntry(int $id)
    {
        $timeTracking = new TimeTracking(0);
        if (!User::isAdmin() && !$timeTracking->isOwner($id)) {
            return;
        }
    }

    public static function deleteEntry()
    {
        $userId = User::getCurrentUserId();
        $id = (int) Tools::get("id");

        $query = "SELECT user_id FROM user_timetracking WHERE id = :id";
        $timeTrackingUserId = DBAccess::selectQuery($query, [
            "id" => $id,
        ]);

        if (!User::isAdmin() && (int) $timeTrackingUserId[0]["user_id"] !== $userId) {
            return;
        }

        $query = "DELETE FROM user_timetracking WHERE id = :id;";
        DBAccess::deleteQuery($query, ["id" => $id]);

        $affectedRows = DBAccess::getAffectedRows();

        if ($affectedRows === 0) {
            return JSONResponseHandler::returnNotFound();
        }

        JSONResponseHandler::returnOK();
    }

    /**
     * toggles global time tracking view
     */
    public static function toggleDisplayTimeTracking()
    {
        $value = Config::toggle("showTimeGlobal");
        JSONResponseHandler::sendResponse([
            "status" => "success",
            "display" => $value,
        ]);
    }
}
