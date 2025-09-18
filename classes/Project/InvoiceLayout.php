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

    private function getLayoutData(): void
    {
        $query = "SELECT * FROM invoice_layout WHERE invoice_id = :invoiceId ORDER BY position;";
        $data = DBAccess::selectQuery($query, [
            "invoiceId" => $this->invoice->getId(),
        ]);

        $this->layout = $data;
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function getOrderedInvoiceContent(): array
    {
        $items = $this->invoice->loadPostenFromAuftrag();
        $texts = array_filter($this->invoice->getTexts(), fn($el) => $el["active"] != 0);
        $vehicles = $this->invoice->getAttachedVehicles();

        $all = [];

        foreach ($items as $item) {
            $all[] = [
                "id" => $item->getPostennummer(),
                "type" => "item",
                "content" => $item->getDescription(),
            ];
        }

        foreach ($texts as $text) {
            $all[] = [
                "id" => $text["id"],
                "type" => "text",
                "content" => $text["text"],
            ];
        }

        foreach ($vehicles as $vehicle) {
            $all[] = [
                "id" => $vehicle["Nummer"],
                "type" => "vehicle",
                "content" => $vehicle["Kennzeichen"] . " " . $vehicle["Fahrzeug"],
            ];
        }

        $allMap = [];
        foreach ($all as $entry) {
            $key = "{$entry['type']}-{$entry['id']}";
            $allMap[$key] = $entry;
        }

        $result = [];
        $usedKeys = [];

        foreach ($this->layout as $layoutEntry) {
            $key = "{$layoutEntry['content_type']}-{$layoutEntry['content_id']}";
            if (isset($allMap[$key])) {
                $result[] = $allMap[$key];
                $usedKeys[$key] = true;
            }
        }

        $defaultOrder = ['item', 'text', 'vehicle'];
        foreach ($defaultOrder as $type) {
            foreach ($allMap as $key => $entry) {
                if ($entry['type'] === $type && !isset($usedKeys[$key])) {
                    $result[] = $entry;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<int, mixed> $positions
     * @return bool
     */
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

    /**
     * Default order is: invoiceItems, texts, vehicles
     * If some elements are present, they are shown first, the other items are shown like the order above after the elements
     */
    public static function getItemsOrderTemplate(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");

        $invoice = new Invoice($invoiceId, $orderId);
        $invoiceLayout = new InvoiceLayout($invoice);

        $items = $invoiceLayout->getOrderedInvoiceContent();
        $template = TemplateController::getTemplate("invoiceItemsOrder", [
            "items" => $items,
        ]);

        JSONResponseHandler::sendResponse([
            "template" => $template,
        ]);
    }

    public static function updateItemsOrder(): void
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
