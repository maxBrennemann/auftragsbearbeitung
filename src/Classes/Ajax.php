<?php

namespace Src\Classes;

use Src\Classes\Controller\SessionController;
use Src\Classes\Routes\CustomerRoutes;
use Src\Classes\Routes\InvoiceRoutes;
use Src\Classes\Routes\LoginRoutes;
use Src\Classes\Routes\NotesRoutes;
use Src\Classes\Routes\NotificationRoutes;
use Src\Classes\Routes\OrderItemRoutes;
use Src\Classes\Routes\OrderRoutes;
use Src\Classes\Routes\ProductRoutes;
use Src\Classes\Routes\SearchRoutes;
use Src\Classes\Routes\SettingsRoutes;
use Src\Classes\Routes\TableRoutes;
use Src\Classes\Routes\TestingRoutes;
use Src\Classes\Routes\TimeTrackingRoutes;
use Src\Classes\Routes\UploadRoutes;
use Src\Classes\Routes\UserRoutes;
use Src\Classes\Routes\VariousRoutes;
use Src\Classes\Routes\HookRoutes;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Ajax
{
    private static string $apiVersion = "v1";
    /** @var array<string> */
    private static array $publicRoutes = [
        "auth",
    ];
    /** @var array<string> */
    private static array $authTokenRoutes = [
        "hooks",
    ];

    public static function handleRequests(): void
    {
        $response = self::getApiRequest();

        $path = $response["path"];
        $routeType = $response["routeType"];

        if (
            !SessionController::isLoggedIn()
            && !in_array($routeType, self::$publicRoutes)
            && !in_array($routeType, self::$authTokenRoutes)
        ) {
            ResourceManager::outputHeaderJSON();
            JSONResponseHandler::throwError(401, "Unauthorized API access");
        }

        switch ($routeType) {
            case "customer":
                CustomerRoutes::handleRequest($path);
                break;
            case "invoice":
                InvoiceRoutes::handleRequest($path);
                break;
            case "auth":
                LoginRoutes::handleRequest($path);
                break;
            case "notes":
                NotesRoutes::handleRequest($path);
                break;
            case "notification":
                NotificationRoutes::handleRequest($path);
                break;
            case "order-items":
                OrderItemRoutes::handleRequest($path);
                break;
            case "order":
                OrderRoutes::handleRequest($path);
                break;
            case "product":
            case "attribute":
            case "category":
                ProductRoutes::handleRequest($path);
                break;
            case "search":
                SearchRoutes::handleRequest($path);
                break;
            case "settings":
                SettingsRoutes::handleRequest($path);
                break;
            case "test":
                TestingRoutes::handleRequest($path);
                break;
            case "tables":
                TableRoutes::handleRequest($path);
                break;
            case "time-tracking":
                TimeTrackingRoutes::handleRequest($path);
                break;
            case "upload":
                UploadRoutes::handleRequest($path);
                break;
            case "user":
                UserRoutes::handleRequest($path);
                break;
            case "template":
            case "manual":
            case "stats":
                VariousRoutes::handleRequest($path);
                break;
            case "hooks":
                HookRoutes::handleRequest($path);
                break;
            default:
                JSONResponseHandler::throwError(404, "Path not found");
        }
    }

    /**
     * @return array{path: string, routeType: string}
     */
    private static function getApiRequest(): array
    {
        ResourceManager::outputHeaderJSON();

        $url = $_SERVER["REQUEST_URI"];
        $url = explode('?', $url, 2);
        $apiPath = str_replace($_ENV["REWRITE_BASE"] . "/", "", $url[0]);
        $apiParts = explode("/", $apiPath);

        if (count($apiParts) < 4) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $apiVersion = $apiParts[2];
        $routeType = $apiParts[3];

        if (self::$apiVersion != $apiVersion) {
            JSONResponseHandler::throwError(404, "api version not supported");
        }

        $path = str_replace("api/" . $apiVersion . "/", "", $apiPath);

        return [
            "path" => $path,
            "routeType" => $routeType,
        ];
    }
}
