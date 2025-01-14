<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class OrderItemRoutes extends Routes
{

    /**
     *
     */
    protected static $getRoutes = [
        "/order-items/{id}/table" => [],
        "/order-items/{id}/all" => [\Classes\Project\Auftrag::class, "itemsOverview"],
    ];

    /**
     * @uses Classes\Project\Auftrag::getItemsOverview()
     * @uses Classes\Project\Zeit::add()
     */
    protected static $postRoutes = [
        "/order-items/{id}/overview" => [],
        "/order-items/{id}/times" => [\Classes\Project\Zeit::class, "add"],
    ];

}
