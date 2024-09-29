<?php

namespace Classes\Routes;

class SettingsRoutes extends Routes
{

    protected static $getRoutes = [];

    protected static $postRoutes = [];

    /**
     * @uses \Classes\Project\TimeTrackingController::toggleDisplayTimeTracking
     */
    protected static $putRoutes = [
        "/settings/global-timetracking" => [\Classes\Project\TimeTrackingController::class, "toggleDisplayTimeTracking"],
    ];

    public function __construct()
    {
        parent::__construct();
    }
}
