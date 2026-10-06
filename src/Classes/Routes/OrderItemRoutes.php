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
     * @uses Classes\Project\Angebot::getExistingOfferTemplate()
     * @uses Classes\Project\Angebot::getOfferItems()
     *
     * @uses Classes\Project\Zeit::get()
     * @uses Classes\Project\Leistung::get()
     */
    protected static $getRoutes = [
        "/order-items/{id}/table" => [\Src\Classes\Project\Zeit::class, "empty"],
        "/order-items/{id}/all" => [\Src\Classes\Project\Auftrag::class, "getOrderItems"],
        "/order-items/{id}/time/{itemId}" => [\Src\Classes\Project\Zeit::class, "get"],
        "/order-items/{id}/service/{itemId}" => [\Src\Classes\Project\Leistung::class, "get"],
        "/order-items/{id}/invoice" => [\Src\Classes\Project\Auftrag::class, "getInvoicePostenTableAjax"],

        "/order-items/offer/template/{customerId}" => [\Src\Classes\Project\Angebot::class, "getOfferTemplate"],
        "/order-items/offer/{offerId}/edit" => [\Src\Classes\Project\Angebot::class, "getExistingOfferTemplate"],
        "/order-items/offer/{id}/all" => [\Src\Classes\Project\Angebot::class, "getOfferItems"],

        "/order-items/times/{itemId}" => [\Src\Classes\Project\Zeit::class, "get"],
        "/order-items/services/{itemId}" => [\Src\Classes\Project\Leistung::class, "get"],
    ];

    /**
     * @uses Classes\Project\Zeit::add()
     * @uses Classes\Project\Leistung::add()
     * @uses Classes\Project\Zeit::addToOffer()
     * @uses Classes\Project\Leistung::addToOffer()
     */
    protected static $postRoutes = [
        "/order-items/{id}/times" => [\Src\Classes\Project\Zeit::class, "add"],
        "/order-items/{id}/services" => [\Src\Classes\Project\Leistung::class, "add"],

        "/order-items/offer/{id}/times" => [\Src\Classes\Project\Zeit::class, "addToOffer"],
        "/order-items/offer/{id}/services" => [\Src\Classes\Project\Leistung::class, "addToOffer"],
    ];

    /**
     * @uses Classes\Project\Zeit::update()
     * @uses Classes\Project\Leistung::update()
     * @uses Classes\Project\Zeit::updateInOffer()
     * @uses Classes\Project\Leistung::updateInOffer()
     */
    protected static $putRoutes = [
        "/order-items/{id}/times/{itemId}" => [\Src\Classes\Project\Zeit::class, "update"],
        "/order-items/{id}/services/{itemId}" => [\Src\Classes\Project\Leistung::class, "update"],

        "/order-items/offer/{id}/times/{itemId}" => [\Src\Classes\Project\Zeit::class, "updateInOffer"],
        "/order-items/offer/{id}/services/{itemId}" => [\Src\Classes\Project\Leistung::class, "updateInOffer"],
    ];

    /**
    * @uses Classes\Project\Zeit::delete()
    * @uses Classes\Project\Leistung::delete()
    */
    protected static $deleteRoutes = [
        "/order-items/time/{itemId}" => [\Src\Classes\Project\Zeit::class, "delete"],
        "/order-items/service/{itemId}" => [\Src\Classes\Project\Leistung::class, "delete"],
    ];
}
