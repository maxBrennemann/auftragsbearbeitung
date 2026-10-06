<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;
use Src\Classes\Controller\SendCreditNoteController;
use Src\Classes\Link;
use Src\Classes\Pdf\TransactionPdf\CreditNotePDF;

/**
 * Gutschrift zur Rechnung: mindert das Entgelt einer bereits ausgestellten Rechnung nachträglich
 * (z. B. Preisnachlass), ohne die Rechnung selbst zu verändern. Jede Gutschrift ist ein eigener
 * Beleg mit eigener Nummer aus dem Rechnungsnummernkreis und Bezug auf die Originalrechnung.
 */
class CreditNote
{
    /**
     * SQL-Ausdruck für die Summe aller Gutschriften (netto) zur jeweiligen Zeile der Tabelle `invoice`,
     * zum Abziehen von invoice.amount in Auswertungen und Listen.
     */
    public const SQL_CREDITED_NET = "COALESCE((SELECT SUM(cn.net_amount) FROM invoice_credit_note cn WHERE cn.invoice_id = invoice.id), 0)";

    /**
     * @return array<int, array<string, string>>
     */
    public static function getForInvoice(int $invoiceId): array
    {
        $query = "SELECT id, invoice_id, credit_number, creation_date, net_amount, vat_rate, reason
            FROM invoice_credit_note
            WHERE invoice_id = :invoiceId
            ORDER BY credit_number";

        return DBAccess::selectQuery($query, [
            "invoiceId" => $invoiceId,
        ]);
    }

    /**
     * @return array<string, string>|null
     */
    public static function get(int $invoiceId, int $creditNoteId): ?array
    {
        $query = "SELECT id, invoice_id, credit_number, creation_date, net_amount, vat_rate, reason
            FROM invoice_credit_note
            WHERE id = :creditNoteId AND invoice_id = :invoiceId";
        $data = DBAccess::selectQuery($query, [
            "creditNoteId" => $creditNoteId,
            "invoiceId" => $invoiceId,
        ]);

        return $data[0] ?? null;
    }

    /**
     * Summe aller Gutschriften zu einer Rechnung (netto, positiv).
     */
    public static function getTotalNetForInvoice(int $invoiceId): float
    {
        $query = "SELECT COALESCE(SUM(net_amount), 0) AS total FROM invoice_credit_note WHERE invoice_id = :invoiceId";
        $data = DBAccess::selectQuery($query, [
            "invoiceId" => $invoiceId,
        ]);

        return (float) $data[0]["total"];
    }

    public static function getFileName(int $creditNumber): string
    {
        return "Gutschrift_" . $creditNumber;
    }

    public static function getPdfLink(int $creditNumber): string
    {
        return Link::getResourcesShortLink(self::getFileName($creditNumber) . ".pdf", "pdf");
    }

    public static function create(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");
        $amount = round((float) str_replace(",", ".", (string) Tools::get("amount")), 2);
        $reason = trim((string) Tools::get("reason"));
        $sendMail = (int) Tools::get("sendMail") === 1;

        try {
            $invoice = new Invoice($invoiceId, $orderId);
        } catch (\Exception $e) {
            JSONResponseHandler::throwError(404, "Rechnung nicht gefunden");
        }

        if ($invoice->getNumber() === 0) {
            JSONResponseHandler::throwError(400, "Eine Gutschrift ist erst nach Abschluss der Rechnung möglich");
        }

        if ($amount <= 0) {
            JSONResponseHandler::throwError(400, "Der Gutschriftsbetrag muss größer als 0 sein");
        }

        if ($reason === "") {
            JSONResponseHandler::throwError(400, "Bitte einen Grund für die Gutschrift angeben");
        }

        $remaining = round($invoice->getAmount() - self::getTotalNetForInvoice($invoiceId), 2);
        if ($amount > $remaining) {
            JSONResponseHandler::throwError(400, "Der Gutschriftsbetrag übersteigt den verbleibenden Rechnungsbetrag von " . number_format($remaining, 2, ',', '.') . " € netto");
        }

        $vatRaw = Settings::get("invoice.vatRate");
        $vatRate = is_numeric($vatRaw) ? (float) $vatRaw : 19.0;

        $creditNumber = InvoiceNumberTracker::reserveNextNumber();
        $creditNoteId = DBAccess::insertQuery("INSERT INTO invoice_credit_note (invoice_id, credit_number, creation_date, net_amount, vat_rate, reason)
            VALUES (:invoiceId, :creditNumber, CURDATE(), :amount, :vatRate, :reason)", [
            "invoiceId" => $invoiceId,
            "creditNumber" => $creditNumber,
            "amount" => $amount,
            "vatRate" => $vatRate,
            "reason" => mb_substr($reason, 0, 255),
        ]);

        /* der Beleg wird einmalig erzeugt und danach nur noch als gespeicherte Datei ausgeliefert */
        $creditNote = self::get($invoiceId, $creditNoteId);
        if ($creditNote === null) {
            JSONResponseHandler::throwError(500, "Gutschrift konnte nicht gespeichert werden");
        }

        $pdf = new CreditNotePDF($invoice, $creditNote);
        $pdf->generate();
        $pdf->saveOutput();

        OrderHistory::add($orderId, $creditNoteId, OrderHistory::TYPE_ORDER, OrderHistory::STATE_ADDED, "Gutschrift Nr. $creditNumber zur Rechnung Nr. {$invoice->getNumber()} über " . number_format($amount, 2, ',', '.') . " € netto");

        $mailSent = false;
        if ($sendMail) {
            $mailSent = self::sendMail($invoice, $pdf, $creditNumber);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "id" => $creditNoteId,
            "number" => $creditNumber,
            "link" => self::getPdfLink($creditNumber),
            "mailRequested" => $sendMail,
            "mailSent" => $mailSent,
        ]);
    }

    public static function send(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");
        $creditNoteId = (int) Tools::get("creditNoteId");

        try {
            $invoice = new Invoice($invoiceId, $orderId);
        } catch (\Exception $e) {
            JSONResponseHandler::throwError(404, "Rechnung nicht gefunden");
        }

        $creditNote = self::get($invoiceId, $creditNoteId);
        if ($creditNote === null) {
            JSONResponseHandler::throwError(404, "Gutschrift nicht gefunden");
        }

        $pdf = new CreditNotePDF($invoice, $creditNote);
        if (!file_exists($pdf->getOutputPath())) {
            JSONResponseHandler::throwError(404, "Die Gutschrift-PDF wurde nicht gefunden");
        }

        if (!self::sendMail($invoice, $pdf, (int) $creditNote["credit_number"])) {
            JSONResponseHandler::throwError(500, "Die Gutschrift konnte nicht versendet werden. Ist eine Rechnungs-E-Mail-Adresse hinterlegt?");
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    private static function sendMail(Invoice $invoice, CreditNotePDF $pdf, int $creditNumber): bool
    {
        $email = $invoice->getInvoiceEmail();
        if ($email === false) {
            return false;
        }

        $sent = SendCreditNoteController::handle([
            "email" => $email,
            "creditNumber" => $creditNumber,
            "invoiceNumber" => $invoice->getNumber(),
            "attachment" => [
                $pdf->getOutputPath() => $pdf->getTitle() . ".pdf",
            ],
        ]);

        if ($sent) {
            $orderId = $invoice->getOrder()->getAuftragsnummer();
            OrderHistory::add($orderId, $creditNumber, OrderHistory::TYPE_ORDER, OrderHistory::STATE_SENT, "Gutschrift Nr. $creditNumber per E-Mail versendet");
        }

        return $sent;
    }
}
