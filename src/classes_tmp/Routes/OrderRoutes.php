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
     */
    protected static $getRoutes = [
        "/order/open" => [\Src\Classes\Project\Auftrag::class, "getOpenOrders"],
        "/order/{id}/colors" => [\Src\Classes\Project\Auftrag::class, "getColors"],
        "/order/{id}/steps" => [\Src\Classes\Project\Step::class, "getSteps"],
        "/order/offer/{offerId}/pdf" => [\Src\Classes\Project\Angebot::class, "getPDF"],
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
     * @uses \Src\Classes\Project\Auftrag::resetInvoice()
     * @uses \Src\Classes\Project\Fahrzeug::addFiles()
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
        "/order/{id}/reset-invoice" => [\Src\Classes\Project\Auftrag::class, "resetInvoice"],
        "/order/{id}/vehicle/{vehicleId}/add-files" => [\Src\Classes\Project\Fahrzeug::class, "addFiles"],
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
    ];

    /**
     * @uses \Src\Classes\Project\Auftrag::deleteOrder()
     * @uses \Src\Classes\Project\Auftrag::deleteColor()
     * @uses \Src\Classes\Project\Fahrzeug::removeVehicle()
     */
    protected static $deleteRoutes = [
        "/order/{id}" => [\Src\Classes\Project\Auftrag::class, "deleteOrder"],
        "/order/{id}/colors/{colorId}" => [\Src\Classes\Project\Auftrag::class, "deleteColor"],
        "/order/{id}/vehicles/{vehicleId}" => [\Src\Classes\Project\Fahrzeug::class, "removeVehicle"],
    ];
}
