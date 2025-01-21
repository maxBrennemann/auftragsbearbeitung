<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class CustomerRoutes extends Routes
{

    /**
     * @uses \Classes\Project\Kunde::getContacts()
     */
    protected static $getRoutes = [
        "/customer/{id}/contacts" => [\Classes\Project\Kunde::class, "getContacts"],
        "/customer/{id}/addresses" => [],
    ];

    /**
     * @uses \Classes\Project\Kunde::addCustomerAjax()
     */
    protected static $postRoutes = [
        "/customer" => [\Classes\Project\Kunde::class, "addCustomerAjax"],
    ];

    public static function handleRequest($route)
    {
        parent::handleRequest($route);
    }
}
