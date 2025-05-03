<?php

namespace Classes\Auth;

class SessionController
{

    private static bool $started = false;
    private static int $lifetime = 1200;

    public static function start()
    {
        if (self::$started) {
            return;
        }

        session_start();
        self::$started = true;

        if (isset($_SESSION["LAST_ACTIVITY"]) 
            && (time() - $_SESSION["LAST_ACTIVITY"] > self::$lifetime)) {
            self::destroy();
            exit;
        }

        $_SESSION["LAST_ACTIVITY"] = time();
    }

    public static function isLoggedIn(): bool
    {
        self::start();
        return isset($_SESSION["user_id"]);
    }

    public static function login(int $userId)
    {
        self::start();
        $_SESSION["user_id"] = $userId;
    }

    public static function logout()
    {
        self::start();
        self::destroy();
    }

    private static function destroy()
    {
		$_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_unset();
        session_destroy();
        self::$started = false;
    }
}
