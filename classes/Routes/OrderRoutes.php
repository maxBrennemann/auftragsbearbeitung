<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class OrderRoutes extends Routes
{
    /**
     * @uses \Classes\Project\Auftrag::getOpenOrders()
     * @uses \Classes\Project\Auftrag::getColors()
     * @uses \Classes\Project\Step::getSteps()
     * @uses \Classes\Project\Angebot::getPDF()
     */
    protected static $getRoutes = [
        "/order/open" => [\Classes\Project\Auftrag::class, "getOpenOrders"],
        "/order/{id}/colors" => [\Classes\Project\Auftrag::class, "getColors"],
        "/order/{id}/steps" => [\Classes\Project\Step::class, "getSteps"],
        "/order/offer/{offerId}/pdf" => [\Classes\Project\Angebot::class, "getPDF"],
    ];

    /**
     * @uses \Classes\Project\Auftrag::addOrder()
     * @uses \Classes\Project\Auftrag::addColor()
     * @uses \Classes\Project\Auftrag::addColors()
     * @uses \Classes\Project\Auftrag::updateOrderType()
     * @uses \Classes\Project\Auftrag::updateOrderTitle()
     * @uses \Classes\Project\Auftrag::updateContactPerson()
     * @uses \Classes\Project\Auftrag::updateDate()
     * @uses \Classes\Project\Auftrag::addFiles()
     * @uses \Classes\Project\Auftrag::resetInvoice()
     * @uses \Classes\Project\Fahrzeug::addFiles()
     */
    protected static $postRoutes = [
        "/order" => [\Classes\Project\Auftrag::class, "addOrder"],
        "/order/{id}/colors/add" => [\Classes\Project\Auftrag::class, "addColor"],
        "/order/{id}/colors/multiple" => [\Classes\Project\Auftrag::class, "addColors"],
        "/order/{id}/type" => [\Classes\Project\Auftrag::class, "updateOrderType"],
        "/order/{id}/title" => [\Classes\Project\Auftrag::class, "updateOrderTitle"],
        "/order/{id}/contact-person" => [\Classes\Project\Auftrag::class, "updateContactPerson"],
        "/order/{id}/update-date" => [\Classes\Project\Auftrag::class, "updateDate"],
        "/order/{id}/add-files" => [\Classes\Project\Auftrag::class, "addFiles"],
        "/order/{id}/reset-invoice" => [\Classes\Project\Auftrag::class, "resetInvoice"],
        "/order/{id}/vehicle/{vehicleId}/add-files" => [\Classes\Project\Fahrzeug::class, "addFiles"],
    ];

    /**
     * @uses \Classes\Project\Auftrag::updateOrder()
     * @uses \Classes\Project\Auftrag::editDescription()
     * @uses \Classes\Project\Auftrag::archive()
     * @uses \Classes\Project\Auftrag::finish()
     * @uses \Classes\Project\Auftrag::updateColor()
     * @uses \Classes\Project\Fahrzeug::attachVehicle()
     *
     * @uses \Classes\Project\Fahrzeug::updateName()
     * @uses \Classes\Project\Fahrzeug::updateLicensePlate()
     */
    protected static $putRoutes = [
        "/order/{id}" => [\Classes\Project\Auftrag::class, "updateOrder"],
        "/order/{id}/description" => [\Classes\Project\Auftrag::class, "editDescription"],
        "/order/{id}/archive" => [\Classes\Project\Auftrag::class, "archive"],
        "/order/{id}/finish" => [\Classes\Project\Auftrag::class, "finish"],
        "/order/{id}/colors/{colorId}" => [\Classes\Project\Auftrag::class, "updateColor"],
        "/order/{id}/vehicles/{vehicleId}" => [\Classes\Project\Fahrzeug::class, "attachVehicle"],

        "/order/vehicles/{vehicleId}/name" => [\Classes\Project\Fahrzeug::class, "updateName"],
        "/order/vehicles/{vehicleId}/license-plate" => [\Classes\Project\Fahrzeug::class, "updateLicensePlate"],
    ];

    /**
     * @uses \Classes\Project\Auftrag::deleteOrder()
     * @uses \Classes\Project\Auftrag::deleteColor()
     * @uses \Classes\Project\Fahrzeug::removeVehicle()
     */
    protected static $deleteRoutes = [
        "/order/{id}" => [\Classes\Project\Auftrag::class, "deleteOrder"],
        "/order/{id}/colors/{colorId}" => [\Classes\Project\Auftrag::class, "deleteColor"],
        "/order/{id}/vehicles/{vehicleId}" => [\Classes\Project\Fahrzeug::class, "removeVehicle"],
    ];
}
