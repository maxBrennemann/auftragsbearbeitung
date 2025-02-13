<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class LoginRoutes extends Routes
{

    protected static $getRoutes = [];

    /**
     * @uses \Classes\Login::handleLogin()
     * @uses \Classes\Login::autloginWrapper()
     * @uses \Classes\Login::handleLogout()
     */
    protected static $postRoutes = [
        "/auth/login" => [\Classes\Login::class, "handleLogin"],
        "/auth/login/auto" => [\Classes\Login::class, "autloginWrapper"],
        "/auth/logout" => [\Classes\Login::class, "handleLogout"],
    ];
}
