<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class InvoiceLayout
{
    private Invoice $invoice;
    private DocumentLayout $documentLayout;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->documentLayout = new DocumentLayout("invoice", $invoice->getId());
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function getOrderedInvoiceContent(): array
    {
        $items = $this->invoice->loadPostenFromAuftrag();
        $texts = array_filter($this->invoice->getTexts(), fn($el) => $el["active"] != 0);
        $vehicles = $this->invoice->getAttachedVehicles();

        /*
         * Leistungsdatum (id < 0, siehe Invoice::getTexts()) wird immer als letzter Eintrag
         * der Liste gerendert und dafür aus der normalen Sortierung/dem Layout herausgehalten.
         */
        $performanceDateEntry = null;
        $regularTexts = [];
        foreach ($texts as $text) {
            if ((int) $text["id"] < 0) {
                $performanceDateEntry = [
                    "id" => $text["id"],
                    "type" => "text",
                    "content" => $text["text"],
                ];
            } else {
                $regularTexts[] = $text;
            }
        }

        $all = [];

        foreach ($items as $item) {
            $all[] = [
                "id" => $item->getPostennummer(),
                "type" => "item",
                "content" => $item->getDescription(),
            ];
        }

        foreach ($regularTexts as $text) {
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

        return $this->documentLayout->getOrderedContent($all, $performanceDateEntry);
    }

    /**
     * @param array<int, mixed> $positions
     */
    private function writeItemsOrder(array $positions): bool
    {
        return $this->documentLayout->writeItemsOrder($positions);
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
        if ($invoice->isLocked()) {
            JSONResponseHandler::sendErrorResponse(400, "Die Rechnung ist abgeschlossen und kann nicht mehr geändert werden.");
            return;
        }

        $invoiceLayout = new InvoiceLayout($invoice);

        $status = $invoiceLayout->writeItemsOrder($positions);

        if ($status) {
            JSONResponseHandler::returnOK();
        } else {
            JSONResponseHandler::sendErrorResponse(400, "Malformed data");
        }
    }
}
