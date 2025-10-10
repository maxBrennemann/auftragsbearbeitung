<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class CustomerRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Project\Kunde::searchCustomers()
     * @uses \Src\Classes\Project\Kunde::empty()
     * @uses \Src\Classes\Project\Kunde::getContacts()
     * @uses \Src\Classes\Project\Kunde::
     * @uses \Src\Classes\Project\Kunde::getColors()
     */
    protected static $getRoutes = [
        "/customer/search" => [\Src\Classes\Project\Kunde::class, "searchCustomers"],

        "/customer/{id}/contacts" => [\Src\Classes\Project\Kunde::class, "getContacts"],
        "/customer/{id}/addresses" => [\Src\Classes\Project\Kunde::class, "empty"],
        "/customer/{id}/colors" => [\Src\Classes\Project\Kunde::class, "getColors"],
    ];

    /**
     * @uses \Src\Classes\Project\Kunde::addCustomer()
     * @uses \Src\Classes\Project\Address::addAddress()
     * @uses \Src\Classes\Project\Kunde::addContact()
     * @uses \Src\Classes\Project\Kunde::addVehicle()
     */
    protected static $postRoutes = [
        "/customer" => [\Src\Classes\Project\Kunde::class, "addCustomer"],
        "/customer/{id}/address" => [\Src\Classes\Project\Address::class, "addAddress"],
        "/customer/{id}/contact" => [\Src\Classes\Project\Kunde::class, "addContact"],
        "/customer/{id}/vehicle" => [\Src\Classes\Project\Kunde::class, "addVehicle"],
    ];

    /**
     * @uses \Src\Classes\Project\Kunde::setNote()
     * @uses \Src\Classes\Project\Kunde::updateCustomer()
     */
    protected static $putRoutes = [
        "/customer/{id}/note" => [\Src\Classes\Project\Kunde::class, "setNote"],
        "/customer/{id}" => [\Src\Classes\Project\Kunde::class, "updateCustomer"],
    ];

    /**
     * @uses \Src\Classes\Project\Kunde::delete()
     */
    protected static $deleteRoutes = [
        "/customer/{id}/" => [\Src\Classes\Project\Kunde::class, "delete"],
    ];
}
