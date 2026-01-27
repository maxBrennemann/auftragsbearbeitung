<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class TimeTrackingRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Controller\TimeTrackingController::showTimeTracking()
     * @uses \Src\Classes\Controller\TimeTrackingController::showTimeTracking()
     * @uses \Src\Classes\Controller\TimeTrackingController::showTimeTrackingOverview()
     * @uses \Src\Classes\Controller\TimeTrackingController::getActiveEntry
     */
    protected static $getRoutes = [
        "/time-tracking/current-user" => [\Src\Classes\Controller\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/{id}" => [\Src\Classes\Controller\TimeTrackingController::class, "showTimeTracking"],
        "/time-tracking/overview" => [\Src\Classes\Controller\TimeTrackingController::class, "showTimeTrackingOverview"],
        "/time-tracking/active" => [\Src\Classes\Controller\TimeTrackingController::class, "getActiveEntry"],
    ];

    /**
     * @uses \Src\Classes\Controller\TimeTrackingController::addEntry()
     */
    protected static $postRoutes = [
        "/time-tracking/add" => [\Src\Classes\Controller\TimeTrackingController::class, "addEntry"],
        "/time-tracking/start" => [\Src\Classes\Controller\TimeTrackingController::class, "startTimer"],
        "/time-tracking/{id}/pause" => [\Src\Classes\Controller\TimeTrackingController::class, "pauseTimer"],
        "/time-tracking/{id}/resume" => [\Src\Classes\Controller\TimeTrackingController::class, "resumeTimer"],
        "/time-tracking/{id}/stop" => [\Src\Classes\Controller\TimeTrackingController::class, "stopTimer"],
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
