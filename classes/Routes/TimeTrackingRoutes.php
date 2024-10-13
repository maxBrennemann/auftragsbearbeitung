<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class TimeTrackingRoutes extends Routes
{

    protected static $getRoutes = [
        "/time-tracking/current-user" => [\Classes\Project\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/{id}" => [\Classes\Project\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/overview" => [\Classes\Project\TimeTrackingController::class, "showTimeTrackingOverview"],
    ];

    protected static $postRoutes = [
        "/time-tracking/add" => [\Classes\Project\TimeTrackingController::class, "addEntry"],
    ];

    protected static $putRoutes = [
        "time-tracking/edit/{id}" => [\Classes\Project\TimeTrackingController::class, "editEntry"],
    ];

    protected static $deleteRoutes = [
        "/time-tracking/delete/{id}" => [\Classes\Project\TimeTrackingController::class, "deleteEntry"],
    ];
}
