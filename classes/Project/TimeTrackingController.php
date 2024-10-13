<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class TimeTrackingController
{

    public static function showTimeTracking(?int $id = null) {}

    public static function showTimeTrackingOverview() {}

    public static function addEntry() {}

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
        $query = "DELETE FROM zeiterfassung WHERE id = :id;";
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
        Config::toggle("showTimeGlobal");
        JSONResponseHandler::returnOK();
    }
}
