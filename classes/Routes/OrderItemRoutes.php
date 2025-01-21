<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class OrderItemRoutes extends Routes
{

    /**
     * @uses Classes\Project\Auftrag::itemsOverview()
     */
    protected static $getRoutes = [
        "/order-items/{id}/table" => [],
        "/order-items/{id}/all" => [\Classes\Project\Auftrag::class, "getOrderItems"],
    ];

    /**
     * @uses Classes\Project\Auftrag::getItemsOverview()
     * @uses Classes\Project\Zeit::add()
     * @uses Classes\Project\Leistung::add()
     */
    protected static $postRoutes = [
        "/order-items/{id}/overview" => [],
        "/order-items/{id}/times" => [\Classes\Project\Zeit::class, "add"],
        "/order-items/{id}/services" => [\Classes\Project\Leistung::class, "add"],
    ];

}
