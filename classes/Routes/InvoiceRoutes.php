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
     */
    protected static $postRoutes = [
        "/invoice/{invoiceId}/paid" => [\Classes\Project\Rechnung::class, "setInvoicePaid"],
    ];
}
