<?php

namespace Classes\Routes;

class Routes
{

    function __construct()
    {
    }

    protected static $getRoutes = [];
    protected static $postRoutes = [];
    protected static $putRoutes = [];
    protected static $deleteRoutes = [];

    protected static function get($route)
    {
        if (static::checkUrlPatterns($route, static::$getRoutes)) {
            return;
        }

        if (!isset(static::$getRoutes[$route])) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $callback = static::$getRoutes[$route];
        self::callCallback($callback);
    }

    protected static function post($route)
    {
        if (static::checkUrlPatterns($route, static::$postRoutes)) {
            return;
        }

        if (!isset(static::$postRoutes[$route])) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $callback = static::$postRoutes[$route];
        self::callCallback($callback);
    }

    protected static function put($route)
    {
        if (static::checkUrlPatterns($route, static::$putRoutes)) {
            return;
        }

        if (!isset(static::$putRoutes[$route])) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $callback = static::$putRoutes[$route];
        self::callCallback($callback);
    }

    protected static function delete($route)
    {
        if (static::checkUrlPatterns($route, static::$deleteRoutes)) {
            return;
        }

        if (!isset(static::$deleteRoutes[$route])) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $callback = static::$deleteRoutes[$route];
        self::callCallback($callback);
    }

    private static function checkUrlPatterns($url, $routes)
    {
        foreach ($routes as $route => $callback) {
            if (static::matchUrlPattern($url, $route)) {
                $callback();
                return true;
            }
        }

        return false;
    }

    private static function matchUrlPattern($url, $route)
    {
        $urlParts = explode("/", $url);
        $routeParts = explode("/", $route);

        if (count($urlParts) != count($routeParts)) {
            return false;
        }

        for ($i = 0; $i < count($urlParts); $i++) {
            if ($routeParts[$i] == $urlParts[$i]) {
                continue;
            }

            if (substr($routeParts[$i], 0, 1) == "{" && substr($routeParts[$i], -1) == "}") {
                self::setUrlParameter(substr($routeParts[$i], 1, -1), $urlParts[$i]);
                continue;
            }

            return false;
        }

        return true;
    }

    private static function setUrlParameter($key, $value)
    {
        Tools::add($key, $value);
    }

    public static function handleRequest($route)
    {
        $method = $_SERVER["REQUEST_METHOD"];
        switch ($method) {
            case "GET":
                static::get($route);
                break;
            case "POST":
                static::post($route);
                break;
            case "PUT":
                static::put($route);
                break;
            case "DELETE":
                static::delete($route);
                break;
            default:
                JSONResponseHandler::throwError(405, "Method not allowed");
        }
    }

    private static function callCallback($callback)
    {
        $callback();
    }
}
