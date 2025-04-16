<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class InvoiceRoutes extends Routes
{

    /**
     * @uses \Classes\Project\Rechnung::getOpenInvoiceData()
     */
    protected static $getRoutes = [
        "/invoice/open" => [\Classes\Project\Rechnung::class, "getOpenInvoiceData"],
    ];

    /**
     * @uses \Classes\Project\Rechnung::setInvoicePaid()
     * @uses \Classes\Project\Rechnung::setInvoiceDate()
     * @uses \Classes\Project\Rechnung::setServiceDate()
     * @uses \Classes\Project\Rechnung::addText()
     * @uses \Classes\Project\Rechnung::completeInvoice()
     */
    protected static $postRoutes = [
        "/invoice/{invoiceId}/paid" => [\Classes\Project\Rechnung::class, "setInvoicePaid"],
        "/invoice/{invoiceId}/invoice-date" => [\Classes\Project\Rechnung::class, "setInvoiceDate"],
        "/invoice/{invoiceId}/service-date" => [\Classes\Project\Rechnung::class, "setServiceDate"],
        "/invoice/{invoiceId}/text" => [\Classes\Project\Rechnung::class, "addText"],
        "/invoice/{invoiceId}/complete" => [\Classes\Project\Rechnung::class, "completeInvoice"],
    ];

    /**
     * @uses \Classes\Project\Rechnung::toggleText()
     */
    protected static $putRoutes = [
        "/invoice/{invoiceId}/text" => [\Classes\Project\Rechnung::class, "toggleText"],
    ];
}
