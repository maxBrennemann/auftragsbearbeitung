<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class InvoiceNumberTracker
{

    public static function peekNextInvoiceNumber(): int
    {
        $query = "SELECT last_used_number FROM invoice_number_tracker WHERE id = 1";
        $result = DBAccess::selectQuery($query);

        if (empty($result)) {
            return 1;
        }

        return (int) $result[0]["last_used_number"] + 1;
    }

    public static function completeInvoice(Rechnung $invoice)
    {
        $query = "SELECT last_used_number FROM invoice_number_tracker WHERE id = 1 FOR UPDATE";
        $result = DBAccess::selectQuery($query);

        $lastUsedNumber = (int) $result[0]["last_used_number"];
        $newInvoiceNumber = $lastUsedNumber + 1;

        $query = "UPDATE invoice_number_tracker SET last_used_number = :lastUsedNumber WHERE id = 1";
        DBAccess::insertQuery($query, [
            "lastUsedNumber" => $newInvoiceNumber
        ]);

        $query = "UPDATE rechnung SET Rechnungsnummer = :invoiceNumber WHERE id = :invoiceId";
        DBAccess::updateQuery($query, [
            "invoiceNumber" => $newInvoiceNumber,
            "invoiceId" => $invoice->getId()
        ]);
    }
}
