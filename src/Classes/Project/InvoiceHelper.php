<?php

namespace Src\Classes\Project;

use Exception;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;
use Src\Classes\Controller\SendInvoiceController;
use Src\Classes\Notification\NotificationManager;
use Src\Classes\Notification\NotificationType;
use Src\Classes\Pdf\TransactionPdf\InvoicePDF;
use Src\Classes\Protocol;

class InvoiceHelper
{

    private static function getVatRate(): float
    {
        $rate = (float) Settings::get('invoice.vatRate');
        return $rate / 100.0;
    }

    public static function getOpenInvoiceSum(): int
    {
        $query = "SELECT ROUND(SUM(invoice.amount - " . CreditNote::SQL_CREDITED_NET . "), 2) AS summe
				FROM auftrag, invoice
				WHERE auftrag.Rechnungsnummer != 0
					AND auftrag.Bezahlt = 0
					AND invoice.id = auftrag.Rechnungsnummer";
        $sum = DBAccess::selectQuery($query)[0]["summe"];
        if ($sum == null) {
            return 0;
        }
        return (int) $sum;
    }

    public static function getOpenInvoiceSumFormatted(): string
    {
        $sum = self::getOpenInvoiceSum();
        $rate = self::getVatRate();
        $sum *= 1 + $rate;
        return number_format($sum, 2, ',', '.') . ' €';
    }

    public static function getOpenInvoiceData(): void
    {
        $dueIn = (int) Settings::get("invoice.dueDate");
        $show = Tools::get("show");

        if ($show === "due") {
            $dueCondition = "AND DATE_ADD(invoice.creation_date, INTERVAL $dueIn DAY) <= CURDATE()";
        } else {
            $dueCondition = "";
        }

        $rate = self::getVatRate();
        $mult = 1 + $rate;
        /* offener Betrag = Rechnungsbetrag abzüglich Gutschriften zur Rechnung */
        $openAmount = "(invoice.amount - " . CreditNote::SQL_CREDITED_NET . ")";

        $data = DBAccess::selectQuery("SELECT
                auftrag.Rechnungsnummer,
                auftrag.Auftragsnummer AS Nummer,
                invoice.invoice_number,
				auftrag.Auftragsbezeichnung AS Bezeichnung, 
				auftrag.Auftragsbeschreibung AS Beschreibung, 
				auftrag.Kundennummer,
				DATE_FORMAT(auftrag.Datum, '%d.%m.%Y') AS Datum,
                DATE_FORMAT(invoice.creation_date, '%d.%m.%Y') AS Rechnungsdatum,
                DATE_FORMAT(DATE_ADD(invoice.creation_date, INTERVAL $dueIn DAY), '%d.%m.%Y') AS Faelligkeitsdatum,
				IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) AS 'Name',
				CONCAT(FORMAT($openAmount, 2, 'de_DE'), ' €') AS Summe,
                CONCAT(FORMAT($openAmount * $mult, 2, 'de_DE'), ' €') AS Summe_mwst
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

    private const AMOUNT_TOLERANCE = 0.01;

    public static function setInvoicePaidExternal(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $amount = (float) Tools::get("amount");
        $otherIds = Tools::get("otherIds");
        if (!is_array($otherIds)) {
            $otherIds = [];
        }
        $otherIds = array_map("intval", $otherIds);
        //$description = Tools::get("description");

        $day = date("Y-m-d");
        $dayGerman = date("d.m.Y");

        /* 1. exact single-invoice match on the primary id */
        if (self::exactMatch($invoiceId, $amount)) {
            self::markInvoicesPaid([$invoiceId], $day, $dayGerman);
            return;
        }

        /* 2. exact single-invoice match on one of the alternative ids
         * (the payment reference might list the "other" id first) */
        foreach ($otherIds as $id) {
            if (self::exactMatch($id, $amount)) {
                self::markInvoicesPaid([$id], $day, $dayGerman);
                return;
            }
        }

        /* 3. combined match: a single payment covering multiple open invoices
         * at once (e.g. a customer pays two invoices in one transfer) */
        $candidateIds = array_unique(array_filter(array_merge([$invoiceId], $otherIds), fn ($id) => $id > 0));
        $openInvoices = self::openInvoicesByIds($candidateIds);

        if (count($openInvoices) > 1) {
            $sum = array_sum(array_map(fn ($row) => (float) $row["amount"], $openInvoices));
            if (abs($sum - $amount) < self::AMOUNT_TOLERANCE) {
                $ids = array_map(fn ($row) => (int) $row["invoice_number"], $openInvoices);
                self::markInvoicesPaid($ids, $day, $dayGerman);
                return;
            }
        }

        /* 4. last resort: match by id only (amount unverified), needs manual review */
        foreach ($otherIds as $id) {
            if (self::matchId($id, $amount)) {
                self::markInvoicesPaid([$id], $day, $dayGerman, true);
                return;
            }
        }
    }

    /**
     * @param array<int, int> $invoiceIds
     */
    private static function markInvoicesPaid(array $invoiceIds, string $day, string $dayGerman, bool $needsVerification = false): void
    {
        $suffix = $needsVerification ? " Bitte überprüfen und ggf. korrigieren." : "";

        foreach ($invoiceIds as $id) {
            Protocol::write("Invoice", "Set invoice $id as payed on $day." . ($needsVerification ? " Please verify." : ""));
            NotificationManager::addNotification(null, NotificationType::ORDER_PAYED, "Rechnung $id wurde am $dayGerman bezahlt.$suffix", $id);

            Invoice::setInvoicePaid($id, $day, "ueberweisung");
        }
    }

    private static function exactMatch(int $id, float $amount): bool
    {
        if ($id <= 0) {
            return false;
        }

        $query = "SELECT id
            FROM invoice, auftrag
            WHERE auftrag.Auftragsnummer = invoice.order_id
                AND invoice.`status` = 'finalized'
                AND auftrag.Bezahlt = 0
                AND invoice_number = :id
                AND ABS((invoice.amount - " . CreditNote::SQL_CREDITED_NET . ") - :amount) < " . self::AMOUNT_TOLERANCE . "";
        $data = DBAccess::selectQuery($query, [
            "id" => $id,
            "amount" => $amount
        ]);

        if (empty($data) || count($data) > 1) {
            return false;
        }

        return true;
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, array<string, string>>
     */
    private static function openInvoicesByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));
        if (empty($ids)) {
            return [];
        }

        /* native prepares don't allow reusing a named placeholder, so each IN list gets its own set */
        $numberPlaceholders = [];
        $orderPlaceholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $numberPlaceholders[] = ":number$index";
            $orderPlaceholders[] = ":order$index";
            $params["number$index"] = $id;
            $params["order$index"] = $id;
        }
        $inNumbers = implode(",", $numberPlaceholders);
        $inOrders = implode(",", $orderPlaceholders);

        $query = "SELECT invoice.id, invoice.invoice_number, (invoice.amount - " . CreditNote::SQL_CREDITED_NET . ") AS amount
            FROM invoice, auftrag
            WHERE auftrag.Auftragsnummer = invoice.order_id
                AND invoice.`status` = 'finalized'
                AND auftrag.Bezahlt = 0
                AND (invoice.invoice_number IN ($inNumbers) OR auftrag.Auftragsnummer IN ($inOrders))";

        return DBAccess::selectQuery($query, $params);
    }

    private static function matchId(int $id, float $amount): bool
    {
        $query = "SELECT id, amount, invoice_number, auftrag.Auftragsnummer as order_id
            FROM invoice, auftrag
            WHERE auftrag.Auftragsnummer = invoice.order_id
                AND invoice.`status` = 'finalized'
                AND auftrag.Bezahlt = 0;";
        $data = DBAccess::selectQuery($query);

        if (empty($data)) {
            return false;
        }

        foreach ($data as $invoice) {
            $invoiceId = $invoice["invoice_number"];
            //$invoiceAmouont = $invoice["amount"];
            $invoiceOrderId = $invoice["order_id"];

            if ($invoiceId == $id || $invoiceOrderId == $id) {
                return true;
            }
        }

        return false;
    }

    public static function sendInvoiceMail(Invoice $invoice, InvoicePDF $invoicePDF, int $invoiceNumber): void
    {
        $pdfPath = $invoicePDF->getOutputPath($invoiceNumber);
        $pdfName = $invoicePDF->getTitle();

        $email = $invoice->getInvoiceEmail();
        if ($email != false) {
            $invoiceData = [
                "email" => $email,
                "invoiceNumber" => $invoiceNumber,
                "attachment" => [
                    $pdfPath => $pdfName,
                ],
            ];

            SendInvoiceController::handle($invoiceData);
        }

        $email = Settings::get("company.invoiceCopyTo");
        if (is_string($email) && $email != "") {
            $invoiceData = [
                "email" => $email,
                "invoiceNumber" => $invoiceNumber,
                "attachment" => [
                    $pdfPath => $pdfName,
                ],
            ];

            SendInvoiceController::handle($invoiceData);
        }
    }
}
