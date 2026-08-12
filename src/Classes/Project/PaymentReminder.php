<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;
use Src\Classes\Controller\SendPaymentReminderController;
use Src\Classes\Pdf\TransactionPdf\PaymentReminderPDF;

class PaymentReminder
{
    private const MAX_LEVEL = 3;

    public static function getNextLevel(int $invoiceId): int
    {
        $query = "SELECT MAX(level) AS max_level FROM invoice_reminder WHERE invoice_id = :invoiceId";
        $data = DBAccess::selectQuery($query, [
            "invoiceId" => $invoiceId,
        ]);

        $lastLevel = (int) ($data[0]["max_level"] ?? 0);

        return min($lastLevel + 1, self::MAX_LEVEL);
    }

    public static function getPDF(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");

        $invoice = new Invoice($invoiceId, $orderId);
        $level = self::getNextLevel($invoiceId);

        $pdf = new PaymentReminderPDF($invoice, $level);
        $pdf->generate();
        $pdf->generateOutput($pdf->getTitle());
    }

    public static function send(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");

        $invoice = new Invoice($invoiceId, $orderId);
        $level = self::getNextLevel($invoiceId);

        $pdf = new PaymentReminderPDF($invoice, $level);
        $pdf->generate();
        $pdf->saveOutput();

        $query = "INSERT INTO invoice_reminder (invoice_id, level, sent_date) VALUES (:invoiceId, :level, :sentDate)";
        DBAccess::insertQuery($query, [
            "invoiceId" => $invoiceId,
            "level" => $level,
            "sentDate" => date("Y-m-d"),
        ]);

        $email = $invoice->getInvoiceEmail();
        if ($email !== false) {
            SendPaymentReminderController::handle([
                "email" => $email,
                "invoiceNumber" => $invoice->getNumber(),
                "level" => $level,
                "attachment" => [
                    $pdf->getOutputPath() => $pdf->getTitle(),
                ],
            ]);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "level" => $level,
        ]);
    }
}
