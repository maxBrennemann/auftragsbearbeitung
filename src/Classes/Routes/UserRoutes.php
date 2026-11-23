<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class UserRoutes extends Routes
{
    protected static $getRoutes = [];

    /**
     * @uses \Src\Classes\Project\User::ajaxAdd()
     */
    protected static $postRoutes = [
        "/user" => [\Src\Classes\Project\User::class, "ajaxAdd"],
    ];

    /**
     * @uses \Src\Classes\Project\User::ajaxUpdate()
     */
    protected static $putRoutes = [
        "/user/{id}" => [\Src\Classes\Project\User::class, "ajaxUpdate"],
    ];

    /**
     * @uses \Src\Classes\Project\User::ajaxRemoveDevice()
     */
    protected static $deleteRoutes = [
        "/user/{id}/devices/{deviceId}" => [\Src\Classes\Project\User::class, "ajaxRemoveDevice"],
    ];
}
