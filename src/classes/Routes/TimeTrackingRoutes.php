<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class TimeTrackingRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Controller\TimeTrackingController::showTimeTracking()
     * @uses \Src\Classes\Controller\TimeTrackingController::showTimeTracking()
     * @uses \Src\Classes\Controller\TimeTrackingController::showTimeTrackingOverview()
     */
    protected static $getRoutes = [
        "/time-tracking/current-user" => [\Src\Classes\Controller\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/{id}" => [\Src\Classes\Controller\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/overview" => [\Src\Classes\Controller\TimeTrackingController::class, "showTimeTrackingOverview"],
    ];

    /**
     * @uses \Src\Classes\Controller\TimeTrackingController::addEntry()
     */
    protected static $postRoutes = [
        "/time-tracking/add" => [\Src\Classes\Controller\TimeTrackingController::class, "addEntry"],
    ];

    /**
     * @uses \Src\Classes\Controller\TimeTrackingController::editEntry()
     */
    protected static $putRoutes = [
        "time-tracking/{id}" => [\Src\Classes\Controller\TimeTrackingController::class, "editEntry"],
    ];

    /**
     * @uses \Src\Classes\Controller\TimeTrackingController::deleteEntry()
     */
    protected static $deleteRoutes = [
        "/time-tracking/{id}" => [\Src\Classes\Controller\TimeTrackingController::class, "deleteEntry"],
    ];
}
