<?php

require_once("classes/routes/Routes.php");

class TimeTrackingRoutes extends Routes {

    protected static $getRoutes = [
        "/time-tracking/current-user" => "TimeTrackingController::showTimeTracking",
        "/time-tracking/{id}" => "TimeTrackingController::showTimeTracking",
        "/time-tracking/overview" => "TimeTrackingController::showTimeTrackingOverview",
    ];

    protected static $postRoutes = [
        "/time-tracking/add" => "TimeTrackingController::addEntry",
    ];

    protected static $putRoutes = [
        "time-tracking/edit/{id}" => "TimeTrackingController::editEntry",
    ];

    protected static $deleteRoutes = [
        "/time-tracking/delete/{id}" => "TimeTrackingController::deleteEntry",
    ];

    public function __construct() {
        parent::__construct();
    }

}
