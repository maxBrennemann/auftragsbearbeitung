<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class InvoiceRoutes extends Routes
{

    /**
     * @uses \Classes\Project\Invoice::getOpenInvoiceData()
     */
    protected static $getRoutes = [
        "/invoice/open" => [\Classes\Project\Invoice::class, "getOpenInvoiceData"],
    ];

    /**
     * @uses \Classes\Project\Invoice::setInvoicePaid()
     * @uses \Classes\Project\Invoice::setInvoiceDate()
     * @uses \Classes\Project\Invoice::setServiceDate()
     * @uses \Classes\Project\Invoice::addText()
     * @uses \Classes\Project\Invoice::completeInvoice()
     */
    protected static $postRoutes = [
        "/invoice/{invoiceId}/paid" => [\Classes\Project\Invoice::class, "setInvoicePaid"],
        "/invoice/{invoiceId}/invoice-date" => [\Classes\Project\Invoice::class, "setInvoiceDate"],
        "/invoice/{invoiceId}/service-date" => [\Classes\Project\Invoice::class, "setServiceDate"],
        "/invoice/{invoiceId}/text" => [\Classes\Project\Invoice::class, "addText"],
        "/invoice/{invoiceId}/complete" => [\Classes\Project\Invoice::class, "completeInvoice"],
    ];

    /**
     * @uses \Classes\Project\Invoice::toggleText()
     */
    protected static $putRoutes = [
        "/invoice/{invoiceId}/text" => [\Classes\Project\Invoice::class, "toggleText"],
    ];
}
