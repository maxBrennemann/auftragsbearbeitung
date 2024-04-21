<?php

require_once("classes/routes/Routes.php");
require_once("classes/project/TimeTrackingController.php");

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
