<?php

namespace Classes\Project;

use Classes\Controller\TemplateController;
use Exception;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class InvoiceLayout
{
    private Invoice $invoice;
    private array $layout;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->getLayoutData();
    }

    private function getLayoutData()
    {
        $query = "SELECT * FROM invoice_layout WHERE invoice_id = :invoiceId ORDER BY position;";
        $data = DBAccess::selectQuery($query, [
            "invoiceId" => $this->invoice->getId(),
        ]);

        $this->layout = $data;
    }

    public function getLayout(): array
    {
        return $this->layout;
    }

    private function getFlattendInvoiceContent(): array
    {
        $items = $this->invoice->loadPostenFromAuftrag();
        $texts = array_filter($this->invoice->getTexts(), fn ($el) => $el["active"] != 0);
        $vehicles = $this->invoice->getAttachedVehicles();

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                "id" => $item->getPostennummer(),
                "type" => "item",
                "content" => $item->getDescription(),
            ];
        }

        foreach ($texts as $text) {
            $result[] = [
                "id" => $text["id"],
                "type" => "text",
                "content" => $text["text"],
            ];
        }

        foreach ($vehicles as $vehicle) {
            $result[] = [
                "id" => $vehicle["Nummer"],
                "type" => "vehicle",
                "content" => $vehicle["Kennzeichen"] . " " . $vehicle["Fahrzeug"],
            ];
        }

        return $result;
    }

    public function isOrdered(): bool
    {
        return $this->layout == null ? false : true;
    }

    private function writeItemsOrder(array $positions): bool
    {
        $query = "INSERT INTO invoice_layout (invoice_id, position, content_type, content_id) VALUES (:invoiceId, :position, :type, :id) ON DUPLICATE KEY UPDATE position = VALUES(position)";

        foreach ($positions as $entry) {
            try {
                DBAccess::insertQuery($query, [
                    "invoiceId" => $this->invoice->getId(),
                    "position" => $entry["position"],
                    "type" => $entry["type"],
                    "id" => $entry["id"],
                ]);
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    private function deleteLayoutEntry(string $contentType, int $contentId)
    {
        $query = "";
        DBAccess::deleteQuery($query, [
            "contentType" => $contentType,
            "contentId" => $contentId,
        ]);
    }

    /**
     * Default order is: invoiceItems, texts, vehicles
     * If some elements are present, they are shown first, the other items are shown like the order above after the elements
     * @return void
     */
    public static function getItemsOrderTemplate()
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");

        $invoice = new Invoice($invoiceId, $orderId);
        $invoiceLayout = new InvoiceLayout($invoice);

        $items = $invoiceLayout->getFlattendInvoiceContent();
        $template = TemplateController::getTemplate("invoiceItemsOrder", [
            "items" => $items,
        ]);

        JSONResponseHandler::sendResponse([
            "template" => $template,
        ]);
    }

    public static function updateItemsOrder()
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");

        $positions = Tools::get("positions");
        $positions = json_decode($positions, true);

        $invoice = new Invoice($invoiceId, $orderId);
        $invoiceLayout = new InvoiceLayout($invoice);

        $status = $invoiceLayout->writeItemsOrder($positions);

        if ($status) {
            JSONResponseHandler::returnOK();
        } else {
            JSONResponseHandler::sendErrorResponse(400, "Malformed data");
        }
    }
}
