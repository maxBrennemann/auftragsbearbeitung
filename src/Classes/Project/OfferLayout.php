<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class OfferLayout
{
    private Angebot $offer;
    private DocumentLayout $documentLayout;

    public function __construct(Angebot $offer)
    {
        $this->offer = $offer;
        $this->documentLayout = new DocumentLayout("offer", $offer->getId());
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function getOrderedOfferContent(): array
    {
        $items = Posten::getOfferItems($this->offer->getId());
        $texts = array_filter($this->offer->getTexts(), fn($el) => $el["active"] != 0);

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

        return $this->documentLayout->getOrderedContent($all);
    }

    /**
     * @param array<int, mixed> $positions
     */
    private function writeItemsOrder(array $positions): bool
    {
        return $this->documentLayout->writeItemsOrder($positions);
    }

    public static function getItemsOrderTemplate(): void
    {
        $offerId = (int) Tools::get("offerId");
        $customerId = (int) Tools::get("customerId");

        $offer = new Angebot($offerId, $customerId);
        $offerLayout = new OfferLayout($offer);

        $items = $offerLayout->getOrderedOfferContent();
        $template = TemplateController::getTemplate("invoiceItemsOrder", [
            "items" => $items,
        ]);

        JSONResponseHandler::sendResponse([
            "template" => $template,
        ]);
    }

    public static function updateItemsOrder(): void
    {
        $offerId = (int) Tools::get("offerId");
        $customerId = (int) Tools::get("customerId");

        $positions = Tools::get("positions");
        $positions = json_decode($positions, true);

        $offer = new Angebot($offerId, $customerId);
        $offerLayout = new OfferLayout($offer);

        $status = $offerLayout->writeItemsOrder($positions);

        if ($status) {
            JSONResponseHandler::returnOK();
        } else {
            JSONResponseHandler::sendErrorResponse(400, "Malformed data");
        }
    }
}
