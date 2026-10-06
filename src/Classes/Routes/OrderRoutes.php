<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class OrderRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Project\Auftrag::getOpenOrders()
     * @uses \Src\Classes\Project\Auftrag::getColors()
     * @uses \Src\Classes\Project\Step::getSteps()
     * @uses \Src\Classes\Project\Angebot::getPDF()
     * @uses \Src\Classes\Project\DeliveryNote::getPDF()
     */
    protected static $getRoutes = [
        "/order/open" => [\Src\Classes\Project\Auftrag::class, "getOpenOrders"],
        "/order/{id}/colors" => [\Src\Classes\Project\Auftrag::class, "getColors"],
        "/order/{id}/steps" => [\Src\Classes\Project\Step::class, "getSteps"],
        "/order/offer/{offerId}/pdf" => [\Src\Classes\Project\Angebot::class, "getPDF"],
        "/order/{id}/delivery-note/pdf" => [\Src\Classes\Project\DeliveryNote::class, "getPDF"],
    ];

    /**
     * @uses \Src\Classes\Project\Auftrag::addOrder()
     * @uses \Src\Classes\Project\Auftrag::addColor()
     * @uses \Src\Classes\Project\Auftrag::addColors()
     * @uses \Src\Classes\Project\Auftrag::updateOrderType()
     * @uses \Src\Classes\Project\Auftrag::updateOrderTitle()
     * @uses \Src\Classes\Project\Auftrag::updateContactPerson()
     * @uses \Src\Classes\Project\Auftrag::updateDate()
     * @uses \Src\Classes\Project\Auftrag::addFiles()
     * @uses \Src\Classes\Project\Fahrzeug::addFiles()
     * @uses \Src\Classes\Project\Auftrag::changeCustomer()
     * @uses \Src\Classes\Project\Angebot::sendOffer()
     * @uses \Src\Classes\Project\Angebot::setAddress()
     * @uses \Src\Classes\Project\Angebot::setContact()
     * @uses \Src\Classes\Project\Angebot::handleAltNames()
     * @uses \Src\Classes\Project\Angebot::addText()
     */
    protected static $postRoutes = [
        "/order" => [\Src\Classes\Project\Auftrag::class, "addOrder"],
        "/order/{id}/colors/add" => [\Src\Classes\Project\Auftrag::class, "addColor"],
        "/order/{id}/colors/multiple" => [\Src\Classes\Project\Auftrag::class, "addColors"],
        "/order/{id}/type" => [\Src\Classes\Project\Auftrag::class, "updateOrderType"],
        "/order/{id}/title" => [\Src\Classes\Project\Auftrag::class, "updateOrderTitle"],
        "/order/{id}/contact-person" => [\Src\Classes\Project\Auftrag::class, "updateContactPerson"],
        "/order/{id}/update-date" => [\Src\Classes\Project\Auftrag::class, "updateDate"],
        "/order/{id}/add-files" => [\Src\Classes\Project\Auftrag::class, "addFiles"],
        "/order/{id}/vehicle/{vehicleId}/add-files" => [\Src\Classes\Project\Fahrzeug::class, "addFiles"],
        "/order/{id}/change-customer" => [\Src\Classes\Project\Auftrag::class, "changeCustomer"],
        "/order/offer/{offerId}/send" => [\Src\Classes\Project\Angebot::class, "sendOffer"],
        "/order/offer/{offerId}/address" => [\Src\Classes\Project\Angebot::class, "setAddress"],
        "/order/offer/{offerId}/contact" => [\Src\Classes\Project\Angebot::class, "setContact"],
        "/order/offer/{offerId}/alt-names" => [\Src\Classes\Project\Angebot::class, "handleAltNames"],
        "/order/offer/{offerId}/text" => [\Src\Classes\Project\Angebot::class, "addText"],
    ];

    /**
     * @uses \Src\Classes\Project\Auftrag::updateOrder()
     * @uses \Src\Classes\Project\Auftrag::editDescription()
     * @uses \Src\Classes\Project\Auftrag::archive()
     * @uses \Src\Classes\Project\Auftrag::finish()
     * @uses \Src\Classes\Project\Auftrag::updateColor()
     * @uses \Src\Classes\Project\Auftrag::changeCustomer()
     * @uses \Src\Classes\Project\Fahrzeug::attachVehicle()
     *
     * @uses \Src\Classes\Project\Fahrzeug::updateName()
     * @uses \Src\Classes\Project\Fahrzeug::updateLicensePlate()
     * @uses \Src\Classes\Project\Angebot::completeOffer()
     * @uses \Src\Classes\Project\Angebot::rejectOffer()
     * @uses \Src\Classes\Project\Angebot::toggleText()
     * @uses \Src\Classes\Project\Angebot::editText()
     * @uses \Src\Classes\Project\OfferLayout::updateItemsOrder()
     */
    protected static $putRoutes = [
        "/order/{id}" => [\Src\Classes\Project\Auftrag::class, "updateOrder"],
        "/order/{id}/description" => [\Src\Classes\Project\Auftrag::class, "editDescription"],
        "/order/{id}/archive" => [\Src\Classes\Project\Auftrag::class, "archive"],
        "/order/{id}/finish" => [\Src\Classes\Project\Auftrag::class, "finish"],
        "/order/{id}/colors/{colorId}" => [\Src\Classes\Project\Auftrag::class, "updateColor"],
        "/order/{id}/change-customer" => [\Src\Classes\Project\Auftrag::class, "changeCustomer"],
        "/order/{id}/vehicles/{vehicleId}" => [\Src\Classes\Project\Fahrzeug::class, "attachVehicle"],

        "/order/vehicles/{vehicleId}/name" => [\Src\Classes\Project\Fahrzeug::class, "updateName"],
        "/order/vehicles/{vehicleId}/license-plate" => [\Src\Classes\Project\Fahrzeug::class, "updateLicensePlate"],

        "/order/offer/{offerId}/complete" => [\Src\Classes\Project\Angebot::class, "completeOffer"],
        "/order/offer/{offerId}/reject" => [\Src\Classes\Project\Angebot::class, "rejectOffer"],
        "/order/offer/{offerId}/text" => [\Src\Classes\Project\Angebot::class, "toggleText"],
        "/order/offer/{offerId}/text/{textId}" => [\Src\Classes\Project\Angebot::class, "editText"],
        "/order/offer/{offerId}/positions" => [\Src\Classes\Project\OfferLayout::class, "updateItemsOrder"],
    ];

    /**
     * @uses \Src\Classes\Project\Auftrag::deleteOrder()
     * @uses \Src\Classes\Project\Auftrag::deleteColor()
     * @uses \Src\Classes\Project\Auftrag::deleteFile()
     * @uses \Src\Classes\Project\Fahrzeug::removeVehicle()
     * @uses \Src\Classes\Project\Angebot::deleteOffer()
     * @uses \Src\Classes\Project\Angebot::deleteText()
     */
    protected static $deleteRoutes = [
        "/order/{id}" => [\Src\Classes\Project\Auftrag::class, "deleteOrder"],
        "/order/{id}/colors/{colorId}" => [\Src\Classes\Project\Auftrag::class, "deleteColor"],
        "/order/{id}/files/{fileId}" => [\Src\Classes\Project\Auftrag::class, "deleteFile"],
        "/order/{id}/vehicles/{vehicleId}" => [\Src\Classes\Project\Fahrzeug::class, "removeVehicle"],
        "/order/offer/{offerId}" => [\Src\Classes\Project\Angebot::class, "deleteOffer"],
        "/order/offer/{offerId}/text/{textId}" => [\Src\Classes\Project\Angebot::class, "deleteText"],
    ];
}
