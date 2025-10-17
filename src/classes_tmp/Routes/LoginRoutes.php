<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class LoginRoutes extends Routes
{
    protected static $getRoutes = [];

    /**
     * @uses \Src\Classes\Login::handleLogin()
     * @uses \Src\Classes\Login::autloginWrapper()
     * @uses \Src\Classes\Login::handleLogout()
     */
    protected static $postRoutes = [
        "/auth/login" => [\Src\Classes\Login::class, "handleLogin"],
        "/auth/login/auto" => [\Src\Classes\Login::class, "autloginWrapper"],
        "/auth/logout" => [\Src\Classes\Login::class, "handleLogout"],
    ];
}
