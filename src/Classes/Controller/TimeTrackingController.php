<?php

namespace Src\Classes\Controller;

use Src\Classes\Project\Settings;
use Src\Classes\Project\TimeTracking;
use Src\Classes\Project\User;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class TimeTrackingController
{
    public static function showTimeTracking(?int $id = null): void
    {
        if (User::getCurrentUserId() == -1) {
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

    public static function showTimeTrackingOverview(): void
    {
    }

    public static function addEntry(): void
    {
        $start = (int) Tools::get("start");
        $stop = (int) Tools::get("stop");
        $task = (string) Tools::get("task");

        if (User::getCurrentUserId() == -1) {
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

    public static function editEntry(int $id): void
    {
        $timeTracking = new TimeTracking(0);
        /*if (!User::isAdmin() && !$timeTracking->isOwner($id)) {
            return;
        }*/
    }

    public static function deleteEntry(): void
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
            JSONResponseHandler::returnNotFound();
        }

        JSONResponseHandler::returnOK();
    }

    /**
     * toggles global time tracking view
     */
    public static function toggleDisplayTimeTracking(): void
    {
        $userId = User::getCurrentUserId();
        $value = Settings::toggle("showTimeTracking", $userId);
        JSONResponseHandler::sendResponse([
            "status" => "success",
            "display" => $value,
        ]);
    }

    public static function startTimer(): void
    {

    }

    public static function pauseTimer(): void
    {
        
    }

    public static function resumeTimer(): void
    {
        
    }

    public static function stopTimer(): void
    {
        
    }
}
