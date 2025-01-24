<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class TimeTrackingRoutes extends Routes
{

    /**
     * @uses \Classes\Project\TimeTrackingController::showTimeTracking()
     * @uses \Classes\Project\TimeTrackingController::showTimeTracking()
     * @uses \Classes\Project\TimeTrackingController::showTimeTrackingOverview()
     */
    protected static $getRoutes = [
        "/time-tracking/current-user" => [\Classes\Project\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/{id}" => [\Classes\Project\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/overview" => [\Classes\Project\TimeTrackingController::class, "showTimeTrackingOverview"],
    ];

    /**
     * @uses \Classes\Project\TimeTrackingController::addEntry()
     */
    protected static $postRoutes = [
        "/time-tracking/add" => [\Classes\Project\TimeTrackingController::class, "addEntry"],
    ];

    /**
     * @uses \Classes\Project\TimeTrackingController::editEntry()
     */
    protected static $putRoutes = [
        "time-tracking/{id}" => [\Classes\Project\TimeTrackingController::class, "editEntry"],
    ];

    /**
     * @uses \Classes\Project\TimeTrackingController::deleteEntry()
     */
    protected static $deleteRoutes = [
        "/time-tracking/{id}" => [\Classes\Project\TimeTrackingController::class, "deleteEntry"],
    ];
}
