<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class InvoiceRoutes extends Routes
{
    /**
     * @uses \Classes\Project\Invoice::getOpenInvoiceData()
     * @uses \Classes\Project\Invoice::getPDF()
     */
    protected static $getRoutes = [
        "/invoice/open" => [\Classes\Project\Invoice::class, "getOpenInvoiceData"],
        "/invoice/{invoiceId}/pdf" => [\Classes\Project\Invoice::class, "getPDF"],
    ];

    /**
     * @uses \Classes\Project\Invoice::setInvoicePaid()
     * @uses \Classes\Project\Invoice::setInvoiceDate()
     * @uses \Classes\Project\Invoice::setServiceDate()
     * @uses \Classes\Project\Invoice::addText()
     * @uses \Classes\Project\Invoice::completeInvoice()
     * @uses \Classes\Project\Invoice::setAddress()
     * @uses \Classes\Project\Invoice::setContact()
     * @uses \Classes\Project\Invoice::handleAltNames()
     *
     * @uses \Classes\Project\InvoiceNumberTracker::initInvoiceNumber()
     */
    protected static $postRoutes = [
        "/invoice/{invoiceId}/paid" => [\Classes\Project\Invoice::class, "setInvoicePaid"],
        "/invoice/{invoiceId}/invoice-date" => [\Classes\Project\Invoice::class, "setInvoiceDate"],
        "/invoice/{invoiceId}/service-date" => [\Classes\Project\Invoice::class, "setServiceDate"],
        "/invoice/{invoiceId}/text" => [\Classes\Project\Invoice::class, "addText"],
        "/invoice/{invoiceId}/complete" => [\Classes\Project\Invoice::class, "completeInvoice"],
        "/invoice/{invoiceId}/address" => [\Classes\Project\Invoice::class, "setAddress"],
        "/invoice/{invoiceId}/contact" => [\Classes\Project\Invoice::class, "setContact"],
        "/invoice/{invoiceId}/alt-names" => [\Classes\Project\Invoice::class, "handleAltNames"],

        "/invoice/init-invoice-number" => [\Classes\Project\InvoiceNumberTracker::class, "initInvoiceNumber"],
    ];

    /**
     * @uses \Classes\Project\Invoice::toggleText()
     * @uses \Classes\Project\InvoiceLayout::updateItemsOrder()
     */
    protected static $putRoutes = [
        "/invoice/{invoiceId}/text" => [\Classes\Project\Invoice::class, "toggleText"],
        "/invoice/{invoiceId}/positions" => [\Classes\Project\InvoiceLayout::class, "updateItemsOrder"],
    ];
}
