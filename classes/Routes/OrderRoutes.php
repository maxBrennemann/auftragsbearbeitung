<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class OrderRoutes extends Routes
{

    /**
     * @uses \Classes\Project\Auftrag::getColors()
     */
    protected static $getRoutes = [
        "/order/{id}/colors" => [\Classes\Project\Auftrag::class, "getColors"],
    ];

    /**
     * @uses \Classes\Project\Auftrag::addOrder()
     * @uses \Classes\Project\Auftrag::addColor()
     * @uses \Classes\Project\Auftrag::updateOrderType()
     * @uses \Classes\Project\Fahrzeug::updateOrderTitle()
     * @uses \Classes\Project\Auftrag::updateContactPerson()
     * @uses \Classes\Project\Fahrzeug::updateDate()
     */
    protected static $postRoutes = [
        "/order/add" => [\Classes\Project\Auftrag::class, "addOrder"],
        "/order/{id}/colors/add" => [\Classes\Project\Auftrag::class, "addColor"],
        "/order/{id}/type" => [\Classes\Project\Auftrag::class, "updateOrderType"],
        "/order/{id}/title" => [\Classes\Project\Auftrag::class, "updateOrderTitle"],
        "/order/{id}/contact-person" => [\Classes\Project\Auftrag::class, "updateContactPerson"],
        "/order/{id}/update-date" => [\Classes\Project\Auftrag::class, "updateDate"],
    ];

    /**
     * @uses \Classes\Project\Auftrag::updateOrder()
     * @uses \Classes\Project\Auftrag::setOrderArchived()
     * @uses \Classes\Project\Auftrag::updateColor()
     * @uses \Classes\Project\Fahrzeug::attachVehicle()
     */
    protected static $putRoutes = [
        "/order/{id}" => [\Classes\Project\Auftrag::class, "updateOrder"],
        "/order/{id}/to-archive" => [\Classes\Project\Auftrag::class, "setOrderArchived"],
        "/order/{id}/colors/{colorId}" => [\Classes\Project\Auftrag::class, "updateColor"],
        "/order/{id}/vehicles/{vehicleId}" => [\Classes\Project\Fahrzeug::class, "attachVehicle"],
    ];

    protected static $deleteRoutes = [
        "/order/{id}" => [\Classes\Project\Auftrag::class, "deleteOrder"],
        "/order/{id}/colors/{colorId}" => [\Classes\Project\Auftrag::class, "deleteColor"],
    ];
}
