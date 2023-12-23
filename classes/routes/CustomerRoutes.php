<?php

require_once("classes/routes/Routes.php");

class CustomerRoutes extends Routes {

    protected static $getRoutes = [
        "/customer/{id}/contacts" => "Kunde::getContacts",
        "/customer/{id}/addresses" => "",
    ];

    protected static $postRoutes = [
        "/customer/add" => "Kunde::addCustomer",
    ];

    public function __construct() {
        parent::__construct();
    }

    public static function handleRequest($route) {
        parent::handleRequest($route);
    }

}
