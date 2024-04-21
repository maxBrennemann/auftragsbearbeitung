<?php

require_once("classes/routes/Routes.php");

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
        "/login" => "Login::handleLogin",
        "/login/auto" => "Login::autloginWrapper",
        "/logout" => "Login::logout",
    ];
}
