<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class LoginRoutes extends Routes
{

    protected static $getRoutes = [];

    /**
     * @uses \Classes\Login::handleLogin()
     * @uses \Classes\Login::autloginWrapper()
     * @uses \Classes\Login::logout()
     */
    protected static $postRoutes = [
        "/login" => [\Classes\Login::class, "handleLogin"],
        "/login/auto" => [\Classes\Login::class, "autloginWrapper"],
        "/logout" => [\Classes\Login::class, "logout"],
    ];
}
