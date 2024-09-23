<?php

namespace Classes\Routes;

class CustomerRoutes extends Routes {

    protected static $getRoutes = [
        "/customer/{id}/contacts" => "Kunde::getContacts",
        "/customer/{id}/addresses" => "",
    ];

    protected static $postRoutes = [
        "/customer" => "Kunde::addCustomerAjax",
    ];

    public function __construct() {
        parent::__construct();
    }

    public static function handleRequest($route) {
        parent::handleRequest($route);
    }

}
