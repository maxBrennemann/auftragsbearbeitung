<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class InvoiceRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Project\InvoiceHelper::getOpenInvoiceData()
     * @uses \Src\Classes\Project\InvoiceHelper::recalculateInvoices()
     * @uses \Src\Classes\Project\Invoice::getPDF()
     * @uses \Src\Classes\Project\PaymentReminder::getPDF()
     */
    protected static $getRoutes = [
        "/invoice/open" => [\Src\Classes\Project\InvoiceHelper::class, "getOpenInvoiceData"],
        "/invoice/recalculate-all" => [\Src\Classes\Project\InvoiceHelper::class, "recalculateInvoices"],
        "/invoice/{invoiceId}/pdf" => [\Src\Classes\Project\Invoice::class, "getPDF"],
        "/invoice/{invoiceId}/reminder/pdf" => [\Src\Classes\Project\PaymentReminder::class, "getPDF"],
    ];

    /**
     * @uses \Src\Classes\Project\Invoice::setInvoicePaidAjax()
     * @uses \Src\Classes\Project\Invoice::setInvoiceDate()
     * @uses \Src\Classes\Project\Invoice::setServiceDate()
     * @uses \Src\Classes\Project\Invoice::setPerformanceDateVisibility()
     * @uses \Src\Classes\Project\Invoice::addText()
     * @uses \Src\Classes\Project\Invoice::completeInvoice()
     * @uses \Src\Classes\Project\Invoice::setAddress()
     * @uses \Src\Classes\Project\Invoice::setContact()
     * @uses \Src\Classes\Project\Invoice::handleAltNames()
     *
     * @uses \Src\Classes\Project\InvoiceNumberTracker::initInvoiceNumber()
     *
     * @uses \Src\Classes\Project\PaymentReminder::send()
     */
    protected static $postRoutes = [
        "/invoice/{invoiceId}/paid" => [\Src\Classes\Project\Invoice::class, "setInvoicePaidAjax"],
        "/invoice/{invoiceId}/invoice-date" => [\Src\Classes\Project\Invoice::class, "setInvoiceDate"],
        "/invoice/{invoiceId}/service-date" => [\Src\Classes\Project\Invoice::class, "setServiceDate"],
        "/invoice/{invoiceId}/service-date/visibility" => [\Src\Classes\Project\Invoice::class, "setPerformanceDateVisibility"],
        "/invoice/{invoiceId}/text" => [\Src\Classes\Project\Invoice::class, "addText"],
        "/invoice/{invoiceId}/complete" => [\Src\Classes\Project\Invoice::class, "completeInvoice"],
        "/invoice/{invoiceId}/address" => [\Src\Classes\Project\Invoice::class, "setAddress"],
        "/invoice/{invoiceId}/contact" => [\Src\Classes\Project\Invoice::class, "setContact"],
        "/invoice/{invoiceId}/alt-names" => [\Src\Classes\Project\Invoice::class, "handleAltNames"],
        "/invoice/{invoiceId}/reminder/send" => [\Src\Classes\Project\PaymentReminder::class, "send"],

        "/invoice/init-invoice-number" => [\Src\Classes\Project\InvoiceNumberTracker::class, "initInvoiceNumber"],
    ];

    /**
     * @uses \Src\Classes\Project\Invoice::toggleText()
     * @uses \Src\Classes\Project\Invoice::editText()
     * @uses \Src\Classes\Project\InvoiceLayout::updateItemsOrder()
     */
    protected static $putRoutes = [
        "/invoice/{invoiceId}/text" => [\Src\Classes\Project\Invoice::class, "toggleText"],
        "/invoice/{invoiceId}/text/{textId}" => [\Src\Classes\Project\Invoice::class, "editText"],
        "/invoice/{invoiceId}/positions" => [\Src\Classes\Project\InvoiceLayout::class, "updateItemsOrder"],
    ];

    /**
     * @uses \Src\Classes\Project\Invoice::setInvoiceUnpaidAjax()
     * @uses \Src\Classes\Project\Invoice::deleteText()
     */
    protected static $deleteRoutes = [
        "/invoice/{invoiceId}/paid" => [\Src\Classes\Project\Invoice::class, "setInvoiceUnpaidAjax"],
        "/invoice/{invoiceId}/text/{textId}" => [\Src\Classes\Project\Invoice::class, "deleteText"],
    ];
}
