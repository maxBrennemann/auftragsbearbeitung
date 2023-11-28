<?php

class Routes {

    function __construct() {
        
    }

    protected static $getRoutes = [];
    protected static $postRoutes = [];
    protected static $putRoutes = [];
    protected static $deleteRoutes = [];

    protected static function get($route) {
        if (!isset(self::$getRoutes[$route])) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $callback = self::$getRoutes[$route];
        $callback();
    }

    protected static function post($route) {
        if (!isset(self::$postRoutes[$route])) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $callback = self::$postRoutes[$route];
        $callback();
    }

    protected static function put($route) {
        if (!isset(self::$putRoutes[$route])) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $callback = self::$putRoutes[$route];
        $callback();
    
    }

    protected static function delete($route) {
        if (!isset(self::$deleteRoutes[$route])) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $callback = self::$deleteRoutes[$route];
        $callback();
    }

    public static function handleRequest($route) {
        $method = $_SERVER["REQUEST_METHOD"];
        switch ($method) {
            case "GET":
                self::get($route);
                break;
            case "POST":
                self::post($route);
                break;
            case "PUT":
                self::put($route);
                break;
            case "DELETE":
                self::delete($route);
                break;
            default:
                JSONResponseHandler::throwError(405, "Method not allowed");
        }
    }

}
