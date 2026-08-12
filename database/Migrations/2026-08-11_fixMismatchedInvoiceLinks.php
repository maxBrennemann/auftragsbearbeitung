<?php

/**
 * Repairs `auftrag.Rechnungsnummer` links that don't match the invoice
 * actually belonging to that order (pointing at a different order's invoice,
 * or at nothing at all). This was caused by Invoice::completeInvoice()
 * trusting the client-supplied `invoiceId` instead of the invoice actually
 * resolved for the given order (fixed separately in
 * src/Classes/Project/Invoice.php).
 *
 * `invoice.order_id` is set once when the invoice row is created and never
 * modified afterwards, so it is the trustworthy side of the relationship.
 * This re-derives `Rechnungsnummer` from it instead of guessing or
 * resetting to 0 (which would wrongly mark already-invoiced orders as not
 * invoiced). Orders that don't have any matching invoice row at all are
 * left untouched - those need manual review, not an automated guess.
 *
 * Idempotent: re-running this only ever affects rows that still disagree.
 */
return new class () {

    private $queries = [
        "UPDATE auftrag a
            JOIN invoice i ON i.order_id = a.Auftragsnummer
            SET a.Rechnungsnummer = i.id
            WHERE a.Rechnungsnummer != 0 AND a.Rechnungsnummer != i.id;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
