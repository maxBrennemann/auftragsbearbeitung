<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class SearchRoutes extends Routes
{

    /**
     * @uses \Classes\Project\SearchController::init();
     */
    protected static $getRoutes = [
        "/search" => [\Classes\Project\SearchController::class, "init"],
    ];
}
