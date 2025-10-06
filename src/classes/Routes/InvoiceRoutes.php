<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class InvoiceRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Project\InvoiceHelper::getOpenInvoiceData()
     * @uses \Src\Classes\Project\InvoiceHelper::recalculateInvoices()
     * @uses \Src\Classes\Project\Invoice::getPDF()
     */
    protected static $getRoutes = [
        "/invoice/open" => [\Src\Classes\Project\InvoiceHelper::class, "getOpenInvoiceData"],
        "/invoice/recalculate-all" => [\Src\Classes\Project\InvoiceHelper::class, "recalculateInvoices"],
        "/invoice/{invoiceId}/pdf" => [\Src\Classes\Project\Invoice::class, "getPDF"],
    ];

    /**
     * @uses \Src\Classes\Project\Invoice::setInvoicePaid()
     * @uses \Src\Classes\Project\Invoice::setInvoiceDate()
     * @uses \Src\Classes\Project\Invoice::setServiceDate()
     * @uses \Src\Classes\Project\Invoice::addText()
     * @uses \Src\Classes\Project\Invoice::completeInvoice()
     * @uses \Src\Classes\Project\Invoice::setAddress()
     * @uses \Src\Classes\Project\Invoice::setContact()
     * @uses \Src\Classes\Project\Invoice::handleAltNames()
     *
     * @uses \Src\Classes\Project\InvoiceNumberTracker::initInvoiceNumber()
     */
    protected static $postRoutes = [
        "/invoice/{invoiceId}/paid" => [\Src\Classes\Project\Invoice::class, "setInvoicePaid"],
        "/invoice/{invoiceId}/invoice-date" => [\Src\Classes\Project\Invoice::class, "setInvoiceDate"],
        "/invoice/{invoiceId}/service-date" => [\Src\Classes\Project\Invoice::class, "setServiceDate"],
        "/invoice/{invoiceId}/text" => [\Src\Classes\Project\Invoice::class, "addText"],
        "/invoice/{invoiceId}/complete" => [\Src\Classes\Project\Invoice::class, "completeInvoice"],
        "/invoice/{invoiceId}/address" => [\Src\Classes\Project\Invoice::class, "setAddress"],
        "/invoice/{invoiceId}/contact" => [\Src\Classes\Project\Invoice::class, "setContact"],
        "/invoice/{invoiceId}/alt-names" => [\Src\Classes\Project\Invoice::class, "handleAltNames"],

        "/invoice/init-invoice-number" => [\Src\Classes\Project\InvoiceNumberTracker::class, "initInvoiceNumber"],
    ];

    /**
     * @uses \Src\Classes\Project\Invoice::toggleText()
     * @uses \Src\Classes\Project\InvoiceLayout::updateItemsOrder()
     */
    protected static $putRoutes = [
        "/invoice/{invoiceId}/text" => [\Src\Classes\Project\Invoice::class, "toggleText"],
        "/invoice/{invoiceId}/positions" => [\Src\Classes\Project\InvoiceLayout::class, "updateItemsOrder"],
    ];
}
