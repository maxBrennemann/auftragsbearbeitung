<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class TimeTrackingRoutes extends Routes
{
    /**
     * @uses \Classes\Controller\TimeTrackingController::showTimeTracking()
     * @uses \Classes\Controller\TimeTrackingController::showTimeTracking()
     * @uses \Classes\Controller\TimeTrackingController::showTimeTrackingOverview()
     */
    protected static $getRoutes = [
        "/time-tracking/current-user" => [\Classes\Controller\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/{id}" => [\Classes\Controller\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/overview" => [\Classes\Controller\TimeTrackingController::class, "showTimeTrackingOverview"],
    ];

    /**
     * @uses \Classes\Controller\TimeTrackingController::addEntry()
     */
    protected static $postRoutes = [
        "/time-tracking/add" => [\Classes\Controller\TimeTrackingController::class, "addEntry"],
    ];

    /**
     * @uses \Classes\Controller\TimeTrackingController::editEntry()
     */
    protected static $putRoutes = [
        "time-tracking/{id}" => [\Classes\Controller\TimeTrackingController::class, "editEntry"],
    ];

    /**
     * @uses \Classes\Controller\TimeTrackingController::deleteEntry()
     */
    protected static $deleteRoutes = [
        "/time-tracking/{id}" => [\Classes\Controller\TimeTrackingController::class, "deleteEntry"],
    ];
}
