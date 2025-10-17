<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class InvoiceNumberTracker
{
    public static function peekNextInvoiceNumber(): int
    {
        return self::getCurrentInvoiceNumber() + 1;
    }

    public static function getCurrentInvoiceNumber(): int
    {
        $query = "SELECT last_used_number FROM invoice_number_tracker WHERE id = 1";
        $result = DBAccess::selectQuery($query);

        if (empty($result)) {
            return 1;
        }

        return (int) $result[0]["last_used_number"];
    }

    public static function getMaxInvoiceNumber(): int
    {
        $query = "SELECT MAX(invoice_number) AS max_invoice_number FROM invoice;";
        $result = DBAccess::selectQuery($query);

        $maxInvoiceNumber = 1;
        if (!empty($result)) {
            $maxInvoiceNumber = (int) $result[0]["max_invoice_number"];
        }

        $currentInvoiceTracker = self::getCurrentInvoiceNumber();
        return max($maxInvoiceNumber, $currentInvoiceTracker);
    }

    public static function completeInvoice(Invoice $invoice): int
    {
        $lastUsedNumber = self::getCurrentInvoiceNumber();
        $newInvoiceNumber = $lastUsedNumber + 1;

        $query = "UPDATE invoice_number_tracker SET last_used_number = :lastUsedNumber WHERE id = 1";
        DBAccess::insertQuery($query, [
            "lastUsedNumber" => $newInvoiceNumber
        ]);

        $query = "UPDATE invoice SET 
                invoice_number = :invoiceNumber, 
                `status` = 'finalized',
                finalized_date = CURDATE()
            WHERE id = :invoiceId";
        DBAccess::updateQuery($query, [
            "invoiceNumber" => $newInvoiceNumber,
            "invoiceId" => $invoice->getId()
        ]);

        return $newInvoiceNumber;
    }

    public static function initInvoiceNumber(): void
    {
        $newInvoiceNumber = (int) Tools::get("invoiceNumber");
        $query = "REPLACE INTO invoice_number_tracker (id, last_used_number) VALUES (1, :lastUsedNumber);";
        DBAccess::insertQuery($query, [
            "lastUsedNumber" => $newInvoiceNumber
        ]);

        JSONResponseHandler::returnOK();
    }
}
