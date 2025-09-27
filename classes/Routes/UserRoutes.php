<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class UserRoutes extends Routes
{
    protected static $getRoutes = [];

    /**
     * @uses \Classes\Project\User::add()
     */
    protected static $postRoutes = [
        "/user" => [\Classes\Project\User::class, "add"],
    ];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
