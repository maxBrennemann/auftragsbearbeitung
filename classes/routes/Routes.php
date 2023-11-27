<?php

class Routes {

    function __construct() {
        
    }

    protected static $routes = [];

    public static function get($path, $callback) {
        self::$routes[$path] = $callback;
        self::validateServerRequest("GET", $path);
    }

    public static function post($path, $callback) {
        self::$routes[$path] = $callback;
        self::validateServerRequest("POST", $path);
    }

    public static function put($path, $callback) {
        self::$routes[$path] = $callback;
        self::validateServerRequest("PUT", $path);
    }

    public static function delete($path, $callback) {
        self::$routes[$path] = $callback;
        self::validateServerRequest("DELETE", $path);
    }

    private static function validateServerRequest($requestMethod, $path) {
        if ($_SERVER['REQUEST_METHOD'] != $requestMethod) {
            JSONResponseHandler::throwError(405, "Method not allowed");
        }

        if (!array_key_exists($path, self::$routes)) {
            JSONResponseHandler::throwError(404, "Path not found");
        }
    }

}
