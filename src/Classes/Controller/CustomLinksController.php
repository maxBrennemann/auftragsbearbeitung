<?php

namespace Src\Classes\Controller;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use Src\Classes\Project\Settings;
use Src\Classes\Project\User;
use Src\Classes\Project\InvoiceHelper;
use Src\Classes\Link;

class CustomLinksController
{

    /**
     * @return array<mixed>
     */
    public static function getAvailableLinks(): array
    {
        $openInvoiceSum = InvoiceHelper::getOpenInvoiceSumFormatted();

        return [
            [
                "key" => "new-customer",
                "name" => "Neuen Kunden erstellen",
                "icon" => "user-plus",
                "url" => Link::getPageLink("neuer-kunde"),
                "input" => false,
            ],
            [
                "key" => "customers",
                "name" => "Zu den Kunden",
                "icon" => "users",
                "url" => Link::getPageLink("customer-overview"),
                "linkId" => "kundenLink",
                "input" => [
                    "id" => "kundeninput",
                    "placeholder" => "Kundennummer oder Name",
                    "type" => "text",
                ],
            ],
            [
                "key" => "new-order",
                "name" => "Neuen Auftrag erstellen",
                "icon" => "clipboard-plus",
                "url" => Link::getPageLink("neuer-auftrag"),
                "input" => false,
            ],
            [
                "key" => "finances",
                "name" => "Zur Finanzübersicht",
                "icon" => "wallet",
                "url" => Link::getPageLink("payments"),
                "input" => false,
            ],
            [
                "key" => "invoice",
                "name" => "Rechnung anzeigen",
                "icon" => "receipt",
                "url" => Link::getPageLink("rechnung") . "?target=view&id=",
                "input" => [
                    "id" => "rechnungsinput",
                    "placeholder" => "Rechnungsnummer",
                    "type" => "number",
                    "min" => "1",
                ],
            ],
            [
                "key" => "new-offer",
                "name" => "Neues Angebot erstellen",
                "icon" => "file-plus",
                "url" => Link::getPageLink("angebot"),
                "input" => false,
            ],
            [
                "key" => "new-product",
                "name" => "Neues Produkt erstellen",
                "icon" => "tag",
                "url" => Link::getPageLink("neues-produkt"),
                "input" => false,
            ],
            [
                "key" => "order",
                "name" => "Auftrag anzeigen",
                "icon" => "clipboard-list",
                "url" => Link::getPageLink("auftrag") . "?target=view&id=",
                "input" => [
                    "id" => "auftragsinput",
                    "placeholder" => "Auftragsnummer oder Stichwort",
                    "type" => "text",
                ],
            ],
            [
                "key" => "statistics",
                "name" => "Diagramme und Auswertungen",
                "icon" => "chart-line",
                "url" => Link::getPageLink("diagramme"),
                "input" => false,
            ],
            [
                "key" => "services",
                "name" => "Leistungen",
                "icon" => "wrench",
                "url" => Link::getPageLink("leistungen"),
                "input" => false,
            ],
            [
                "key" => "open-invoices",
                "name" => "Offene Rechnungen: <b>$openInvoiceSum</b>",
                "icon" => "receipt-euro",
                "url" => Link::getPageLink("offene-rechnungen"),
                "input" => false,
            ]
        ];
    }

    public static function getAvailableLinksAjax(): void
    {
        JSONResponseHandler::sendResponse([
            "links" => self::getAvailableLinks(),
            "selected" => array_column(self::getUserLinks(), "key"),
        ]);
    }

    /**
     * Die vom Benutzer gewählten Schnellzugriffe (Einstellung „homeLinks“) in fester Reihenfolge.
     * Ohne gespeicherte Auswahl werden alle Links angezeigt.
     *
     * @return array<mixed>
     */
    public static function getUserLinks(): array
    {
        $links = self::getAvailableLinks();
        $selected = Settings::get("homeLinks", User::getCurrentUserId());

        if (!is_array($selected) || empty($selected)) {
            return $links;
        }

        return array_values(array_filter($links, fn($link) => in_array($link["key"], $selected, true)));
    }

    public static function getUserLinksTemplate(): string
    {
        return TemplateController::getTemplate("customLinks", [
            "customLinks" => self::getUserLinks(),
        ]);
    }
}
