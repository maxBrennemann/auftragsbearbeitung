<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class CustomerRoutes extends Routes
{

    /**
     * @uses \Classes\Project\Kunde::getContacts()
     * @uses \Classes\Project\Kunde::
     * @uses \Classes\Project\Kunde::getColors()
     */
    protected static $getRoutes = [
        "/customer/{id}/contacts" => [\Classes\Project\Kunde::class, "getContacts"],
        "/customer/{id}/addresses" => [],
        "/customer/{id}/colors" => [\Classes\Project\Kunde::class, "getColors"],
    ];

    /**
     * @uses \Classes\Project\Kunde::addCustomerAjax()
     */
    protected static $postRoutes = [
        "/customer" => [\Classes\Project\Kunde::class, "addCustomerAjax"],
    ];

    /**
     * @uses \Classes\Project\Kunde::delete()
     */
    protected static $deleteRoutes = [
        "/customer/{id}/" => [\Classes\Project\Kunde::class, "delete"],
    ];
}
