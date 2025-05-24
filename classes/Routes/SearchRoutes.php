<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class SearchRoutes extends Routes
{

    /**
     * @uses \Classes\Project\SearchController::ajaxSearch()
     * @uses \Classes\Project\SearchController::searchAll()
     */
    protected static $getRoutes = [
        "/search" => [\Classes\Project\SearchController::class, "ajaxSearch"],
        "/search/all" => [\Classes\Project\SearchController::class, "searchAll"],
    ];
}
