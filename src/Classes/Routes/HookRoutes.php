<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use Src\Classes\ResourceManager;

class HookRoutes extends Routes
{

    public static function handleRequest(string $route): void
    {
        $token = ResourceManager::getBearerToken();
        if ($token === null || !hash_equals($_ENV["HOOK_TOKEN"], $token)) {
            ResourceManager::outputHeaderJSON();
            JSONResponseHandler::throwError(401, "Unauthorized API access");
        }

        parent::handleRequest($route);
    }

    /**
     * @uses \Src\Classes\Project\InvoiceHelper::setInvoicePaidExternal()
     */
    protected static $getRoutes = [
        "/hooks/invoice" => [\Src\Classes\Project\InvoiceHelper::class, "setInvoicePaidExternal"],
    ];

    protected static $postRoutes = [];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
