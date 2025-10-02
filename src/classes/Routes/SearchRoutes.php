<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class SearchRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Project\SearchController::ajaxSearch()
     * @uses \Src\Classes\Project\SearchController::searchAll()
     */
    protected static $getRoutes = [
        "/search" => [\Src\Classes\Project\SearchController::class, "ajaxSearch"],
        "/search/all" => [\Src\Classes\Project\SearchController::class, "searchAll"],
    ];
}
