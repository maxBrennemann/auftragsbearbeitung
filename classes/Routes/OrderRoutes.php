<?php

namespace Classes\Routes;

class OrderRoutes extends Routes
{

    protected static $getRoutes = [
        "/order/{id}/colors" => [\Classes\Project\Auftrag::class, "getColors"],
    ];

    protected static $postRoutes = [
        "/order/add" => [\Classes\Project\Auftrag::class, "addOrder"],
        "/order/{id}/colors/add" => [\Classes\Project\Auftrag::class, "addColor"],
    ];

    /**
     * @uses Auftrag::updateOrder
     * @uses Auftrag::setOrderArchived
     * @uses Auftrag::updateColor
     * @uses Fahrzeug::attachVehicle
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

    public function __construct()
    {
        parent::__construct();
    }
}