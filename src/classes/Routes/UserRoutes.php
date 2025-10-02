<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class UserRoutes extends Routes
{
    protected static $getRoutes = [];

    /**
     * @uses \Src\Classes\Project\User::add()
     */
    protected static $postRoutes = [
        "/user" => [\Src\Classes\Project\User::class, "add"],
    ];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
