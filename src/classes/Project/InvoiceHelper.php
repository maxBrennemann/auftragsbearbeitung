<?php

namespace Src\Classes\Project;

use Exception;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class InvoiceHelper
{

    public static function getOpenInvoiceSum(): int
    {
        $query = "SELECT ROUND(SUM(invoice.amount), 2) AS summe
			FROM auftrag, invoice
			WHERE auftrag.Rechnungsnummer != 0 
				AND auftrag.Bezahlt = 0
				AND auftrag.Auftragsnummer = invoice.order_id";
        $summe = DBAccess::selectQuery($query)[0]["summe"];
        if ($summe == null) {
            return 0;
        }
        return (int) $summe;
    }

    public static function getOpenInvoiceData(): void
    {
        $data = DBAccess::selectQuery("SELECT auftrag.Auftragsnummer AS Nummer,
				auftrag.Rechnungsnummer,
                invoice.invoice_number,
				auftrag.Auftragsbezeichnung AS Bezeichnung, 
				auftrag.Auftragsbeschreibung AS Beschreibung, 
				auftrag.Kundennummer,
				DATE_FORMAT(auftrag.Datum, '%d.%m.%Y') as Datum,
				kunde.Firmenname,
				CONCAT(FORMAT(invoice.amount, 2, 'de_DE'), ' €') AS Summe 
			FROM auftrag, kunde, invoice
			WHERE auftrag.Kundennummer = kunde.Kundennummer 
				AND Rechnungsnummer != 0
				AND auftrag.Bezahlt = 0
                AND invoice.id = auftrag.Rechnungsnummer");

        JSONResponseHandler::sendResponse([
            "data" => $data,
        ]);
    }

    public static function recalculateInvoices(): void
    {
        $error = [];
        $invoices = DBAccess::selectQuery("SELECT id, order_id FROM invoice;");
        foreach ($invoices as $invoice) {
            $id = (int) $invoice["id"];
            $orderId = (int) $invoice["order_id"];
            try {
                $i = new Invoice($id, $orderId);
                $i->setInvoiceSum();
            } catch (Exception $e) {
                $error[] = [
                    "id" => $id,
                    "message" => $e->getMessage(),
                ];
            }
        }

        JSONResponseHandler::sendResponse($error);
    }
}
