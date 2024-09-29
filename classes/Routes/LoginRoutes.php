<?php

namespace Classes\Routes;

class LoginRoutes extends Routes
{

    protected static $getRoutes = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @uses Login::handleLogin()
     * @uses Login::autloginWrapper()
     * @uses Login::logout()
     */
    protected static $postRoutes = [
        "/login" => [\Classes\Login::class, "handleLogin"],
        "/login/auto" => [\Classes\Login::class, "autloginWrapper"],
        "/logout" => [\Classes\Login::class, "logout"],
    ];
}
