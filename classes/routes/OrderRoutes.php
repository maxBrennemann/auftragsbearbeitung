<?php

require_once("classes/routes/Routes.php");

class OrderRoutes extends Routes {

    protected static $getRoutes = [
        "/order/{id}/colors" => "Auftrag::getColors",
    ];

    protected static $postRoutes = [
        "/order/add" => "Auftrag::addOrder",
        "/order/{id}/colors/add" => "Auftrag::addColor",
    ];

    protected static $putRoutes = [
        "/order/{id}" => "Auftrag::updateOrder",
        "/order/{id}/to-archive" => "Auftrag::setOrderArchived",
        "/order/{id}/colors/{colorId}" => "Auftrag::updateColor",
        "/order/{id}/vehicles/{vehicleId}" => "Fahrzeug::attachVehicle",
    ];

    protected static $deleteRoutes = [
        "/order/{id}" => "Auftrag::deleteOrder",
        "/order/{id}/colors/{colorId}" => "Auftrag::deleteColor",
    ];

    public function __construct() {
        parent::__construct();
    }

}
