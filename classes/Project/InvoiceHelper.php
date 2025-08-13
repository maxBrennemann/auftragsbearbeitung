<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class InvoiceHelper
{

    public static function getOpenInvoiceSum(): int
    {
        $query = "SELECT ROUND(SUM(auftragssumme.orderPrice), 2) AS summe
			FROM auftrag, auftragssumme
			WHERE auftrag.Rechnungsnummer != 0 
				AND auftrag.Bezahlt = 0
				AND auftrag.Auftragsnummer = auftragssumme.id";
        $summe = DBAccess::selectQuery($query)[0]["summe"];
        if ($summe == null) {
            return 0;
        }
        return (int) $summe;
    }

    public static function getOpenInvoiceData()
    {
        $data = DBAccess::selectQuery("SELECT auftrag.Auftragsnummer AS Nummer,
				auftrag.Rechnungsnummer,
                invoice.invoice_number,
				auftrag.Auftragsbezeichnung AS Bezeichnung, 
				auftrag.Auftragsbeschreibung AS Beschreibung, 
				auftrag.Kundennummer,
				DATE_FORMAT(auftrag.Datum, '%d.%m.%Y') as Datum,
				kunde.Firmenname,
				CONCAT(FORMAT(auftragssumme.orderPrice, 2, 'de_DE'), ' €') AS Summe 
			FROM auftrag, auftragssumme, kunde, invoice
			WHERE auftrag.Kundennummer = kunde.Kundennummer 
				AND Rechnungsnummer != 0 
				AND auftrag.Bezahlt = 0 
				AND auftrag.Auftragsnummer = auftragssumme.id
                AND invoice.id = auftrag.Rechnungsnummer");

        JSONResponseHandler::sendResponse([
            "data" => $data,
        ]);
    }
}
