<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class SettingsRoutes extends Routes
{

    protected static $getRoutes = [];

    protected static $postRoutes = [];

    /**
     * @uses Classes\Project\TimeTrackingController::toggleDisplayTimeTracking()
     */
    protected static $putRoutes = [
        "/settings/global-timetracking" => [\Classes\Project\TimeTrackingController::class, "toggleDisplayTimeTracking"],
    ];
}
