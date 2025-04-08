<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class OrderItemRoutes extends Routes
{

    /**
     * 
     * @uses Classes\Project\Auftrag::getOrderItems()
     * 
     * @uses Classes\Project\Angebot::getOfferTemplate()
     * @uses Classes\Project\Angebot::getOfferItems()
     */
    protected static $getRoutes = [
        "/order-items/{id}/table" => [],
        "/order-items/{id}/all" => [\Classes\Project\Auftrag::class, "getOrderItems"],

        "/order-items/offer/template/{customerId}" => [\Classes\Project\Angebot::class, "getOfferTemplate"],
        "/order-items/offer/{id}/all" => [\Classes\Project\Angebot::class, "getOfferItems"],
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
