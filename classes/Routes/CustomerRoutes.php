<?php

namespace Classes\Routes;

class CustomerRoutes extends Routes
{

    protected static $getRoutes = [
        "/customer/{id}/contacts" => [\Classes\Project\Kunde::class, "getContacts"],
        "/customer/{id}/addresses" => [],
    ];

    protected static $postRoutes = [
        "/customer" => [\Classes\Project\Kunde::class, "addCustomerAjax"],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public static function handleRequest($route)
    {
        parent::handleRequest($route);
    }
}
