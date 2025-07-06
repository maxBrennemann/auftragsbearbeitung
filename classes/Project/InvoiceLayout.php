<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

use Classes\Controller\TemplateController;

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
        $texts = array_filter($this->invoice->getTexts(), fn($el) => $el["active"] != 0);
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
}
