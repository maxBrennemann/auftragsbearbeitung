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
     * @uses \Classes\Project\Kunde::addCustomer()
     * @uses \Classes\Project\Address::addAddress()
     * @uses \Classes\Project\Kunde::addContact()
     * @uses \Classes\Project\Kunde::addVehicle()
     */
    protected static $postRoutes = [
        "/customer" => [\Classes\Project\Kunde::class, "addCustomer"],
        "/customer/{id}/address" => [\Classes\Project\Address::class, "addAddress"],
        "/customer/{id}/contact" => [\Classes\Project\Kunde::class, "addContact"],
        "/customer/{id}/vehicle" => [\Classes\Project\Kunde::class, "addVehicle"],
    ];

    /**
     * @uses \Classes\Project\Kunde::setNote()
     * @uses \Classes\Project\Kunde::updateCustomer()
     */
    protected static $putRoutes = [
        "/customer/{id}/note" => [\Classes\Project\Kunde::class, "setNote"],
        "/customer/{id}" => [\Classes\Project\Kunde::class, "updateCustomer"],
    ];

    /**
     * @uses \Classes\Project\Kunde::delete()
     */
    protected static $deleteRoutes = [
        "/customer/{id}/" => [\Classes\Project\Kunde::class, "delete"],
    ];
}
