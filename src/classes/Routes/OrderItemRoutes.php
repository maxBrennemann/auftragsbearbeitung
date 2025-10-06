<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class OrderItemRoutes extends Routes
{
    /**
     * @uses Classes\Project\Zeit::empty()
     * @uses Classes\Project\Auftrag::getOrderItems()
     * @uses Classes\Project\Auftrag::getInvoicePostenTableAjax()
     * @uses Classes\Project\Zeit::get()
     * @uses Classes\Project\Leistung::get()
     *
     * @uses Classes\Project\Angebot::getOfferTemplate()
     * @uses Classes\Project\Angebot::getOfferItems()
     */
    protected static $getRoutes = [
        "/order-items/{id}/table" => [\Src\Classes\Project\Zeit::class, "empty"],
        "/order-items/{id}/all" => [\Src\Classes\Project\Auftrag::class, "getOrderItems"],
        "/order-items/{id}/time/{itemId}" => [\Src\Classes\Project\Zeit::class, "get"],
        "/order-items/{id}/service/{itemId}" => [\Src\Classes\Project\Leistung::class, "get"],
        "/order-items/{id}/invoice" => [\Src\Classes\Project\Auftrag::class, "getInvoicePostenTableAjax"],

        "/order-items/offer/template/{customerId}" => [\Src\Classes\Project\Angebot::class, "getOfferTemplate"],
        "/order-items/offer/{id}/all" => [\Src\Classes\Project\Angebot::class, "getOfferItems"],
    ];

    /**
     * @uses Classes\Project\Zeit::empty()
     * @uses Classes\Project\Zeit::add()
     * @uses Classes\Project\Leistung::add()
     */
    protected static $postRoutes = [
        "/order-items/{id}/overview" => [\Src\Classes\Project\Zeit::class, "empty"],
        "/order-items/{id}/times" => [\Src\Classes\Project\Zeit::class, "add"],
        "/order-items/{id}/services" => [\Src\Classes\Project\Leistung::class, "add"],
    ];

    protected static $putRoutes = [];

    /**
    * @uses Classes\Project\Zeit::delete()
    * @uses Classes\Project\Leistung::delete()
    */
    protected static $deleteRoutes = [
        "/order-items/time/{itemId}" => [\Src\Classes\Project\Zeit::class, "delete"],
        "/order-items/service/{itemId}" => [\Src\Classes\Project\Leistung::class, "delete"],
    ];
}
