<?php

namespace Src\Classes\Controller;

use Src\Classes\ResourceManager;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class SessionController
{
    private static bool $started = false;
    private static int $lifetime = 1200;

    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        session_start();
        self::$started = true;

        if (isset($_SESSION["LAST_ACTIVITY"])
            && (time() - $_SESSION["LAST_ACTIVITY"] > self::$lifetime)) {
            self::destroy();

            $type = ResourceManager::getRequestType();
            if ($type == "page") {
                ResourceManager::setPage("login");
                ResourceManager::initPage();
            } elseif ($type == "resource") {
                JSONResponseHandler::sendErrorResponse(401, "unauthorized, please log in");
            } else {
                JSONResponseHandler::sendErrorResponse(401, "unauthorized, please log in");
            }

            exit;
        }

        $_SESSION["LAST_ACTIVITY"] = time();
    }

    public static function isLoggedIn(): bool
    {
        self::start();
        return isset($_SESSION["user_id"]);
    }

    public static function login(int $userId): void
    {
        self::start();
        $_SESSION["user_id"] = $userId;
    }

    public static function logout(): void
    {
        self::start();
        self::destroy();
    }

    private static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            $sessionName = session_name() === false ? "" : session_name();
            setcookie(
                $sessionName,
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_unset();
        session_destroy();
        self::$started = false;
    }
}
