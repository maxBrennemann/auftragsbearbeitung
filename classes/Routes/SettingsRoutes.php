<?php

namespace Classes\Routes;

class SettingsRoutes extends Routes {

    protected static $getRoutes = [
        
    ];

    protected static $postRoutes = [

    ];

    protected static $putRoutes = [
        "/settings/global-timetracking" => "TimeTrackingController::toggleDisplayTimeTracking",
    ];

    public function __construct() {
        parent::__construct();
    }

}
