<?php

namespace Src\Classes\Project;

use Exception;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class InvoiceHelper
{

    public static function getOpenInvoiceSum(): int
    {
        $query = "SELECT ROUND(SUM(invoice.amount), 2) AS summe
			FROM auftrag, invoice
			WHERE auftrag.Rechnungsnummer != 0 
				AND auftrag.Bezahlt = 0
				AND auftrag.Auftragsnummer = invoice.order_id";
        $sum = DBAccess::selectQuery($query)[0]["summe"];
        if ($sum == null) {
            return 0;
        }
        return (int) $sum;
    }

    public static function getOpenInvoiceSumFormatted(): string
    {
        $sum = self::getOpenInvoiceSum();
        $sum = $sum * 1.19;
        return number_format($sum, 2, ',', '.') . ' €';
    }

    public static function getOpenInvoiceData(): void
    {
        $dueIn = (int) Settings::get("companyDueDate");
        $show = Tools::get("show");

        if ($show === "due") {
            $dueCondition = "AND DATE_ADD(invoice.creation_date, INTERVAL $dueIn DAY) <= CURDATE()";
        } else {
            $dueCondition = "";
        }

        $data = DBAccess::selectQuery("SELECT auftrag.Auftragsnummer AS Nummer,
				auftrag.Rechnungsnummer,
                invoice.invoice_number,
				auftrag.Auftragsbezeichnung AS Bezeichnung, 
				auftrag.Auftragsbeschreibung AS Beschreibung, 
				auftrag.Kundennummer,
				DATE_FORMAT(auftrag.Datum, '%d.%m.%Y') AS Datum,
                DATE_FORMAT(invoice.creation_date, '%d.%m.%Y') AS Rechnungsdatum,
                DATE_FORMAT(DATE_ADD(invoice.creation_date, INTERVAL $dueIn DAY), '%d.%m.%Y') AS Faelligkeitsdatum,
				IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) AS 'Name',
				CONCAT(FORMAT(invoice.amount, 2, 'de_DE'), ' €') AS Summe,
                CONCAT(FORMAT(invoice.amount * 1.19, 2, 'de_DE'), ' €') AS Summe_mwst
			FROM auftrag, kunde, invoice
			WHERE auftrag.Kundennummer = kunde.Kundennummer 
				AND Rechnungsnummer != 0
				AND auftrag.Bezahlt = 0
                AND invoice.id = auftrag.Rechnungsnummer
                $dueCondition");

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

    public static function setInvoicePaidExternal(): void {}
}
