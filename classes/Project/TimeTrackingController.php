<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

use Classes\Project\User;

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

    public static function showTimeTracking(?int $id = null) {
        if (!self::validateUser()) {
            JSONResponseHandler::throwError(401, "Unvalidated user");
        }

        $userId = User::getCurrentUserId();
        $timeTracking = new TimeTracking($userId);
        $data = $timeTracking->getTimeTables();

        JSONResponseHandler::sendResponse($data);
    }

    public static function showTimeTrackingOverview() {}

    public static function addEntry() {
        $start = (int) Tools::get("startTime");
        $stop = (int) Tools::get("stopTime");
        $task = (string) Tools::get("task");

        if (!self::validateUser()) {
            JSONResponseHandler::throwError(401, "Unvalidated user");
        }

        $userId = User::getCurrentUserId();
        $timeTracking = new TimeTracking($userId);
        $data = $timeTracking->addEntry($start, $stop, $task);

        JSONResponseHandler::sendResponse($data);
    }

    public static function editEntry(int $id)
    {
        $timeTracking = new TimeTracking(0);
        if (!User::isAdmin() && !$timeTracking->isOwner($id)) {
            return;
        }
    }

    public static function deleteEntry($id)
    {
        if (!User::isAdmin()) {
            return;
        }

        $id = (int) $id;
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
