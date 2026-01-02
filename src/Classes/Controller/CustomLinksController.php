<?php

namespace Src\Classes\Controller;

use Src\Classes\Project\Settings;
use Src\Classes\Project\User;
use Src\Classes\Link;

class CustomLinksController
{
    public static function getAvailableLinks(): array
    {
        return [
            [
                "name" => "Neuen Kunden erstellen",
                "icon" => "iconPersonAdd",
                "url" => Link::getPageLink("neuer-kunde"),
                "input" => false,
            ],
            [
                "name" => "Zu den Kunden",
                "icon" => false,
                "url" => Link::getPageLink("customer-overview"),
                "input" => [
                    "id" => "kundeninput",
                    "type" => "text",
                ],
            ],
            [
                "name" => "Neuen Auftrag erstellen",
                "icon" => "iconOrderAdd",
                "url" => Link::getPageLink("neuer-auftrag"),
                "input" => false,
            ],
            [
                "name" => "Zur Finanzübersicht",
                "icon" => false,
                "url" => Link::getPageLink("payments"),
                "input" => false,
            ],
            [
                "name" => "Rechnung anzeigen",
                "icon" => false,
                "url" => Link::getPageLink("rechnung") . "?target=view&id=",
                "input" => [
                    "id" => "rechnungsinput",
                    "type" => "number",
                    "min" => "1",
                ],
            ],
            [
                "name" => "Neues Angebot erstellen",
                "icon" => false,
                "url" => Link::getPageLink("angebot"),
                "input" => false,
            ],
            [
                "name" => "Neues Produkt erstellen",
                "icon" => "iconProductAdd",
                "url" => Link::getPageLink("neues-produkt"),
                "input" => false,
            ],
            [
                "name" => "Auftrag anzeigen",
                "icon" => false,
                "url" => Link::getPageLink("auftrag") . "?target=view&id=",
                "input" => [
                    "id" => "auftragsinput",
                    "type" => "text",
                ],
            ],
            [
                "name" => "Diagramme und Auswertungen",
                "icon" => "iconChart",
                "url" => Link::getPageLink("diagramme"),
                "input" => false,
            ],
            [
                "name" => "Leistungen",
                "icon" => false,
                "url" => Link::getPageLink("leistungen"),
                "input" => false,
            ],
            [
                "name" => "Motivübersicht",
                "icon" => false,
                "url" => Link::getPageLink("sticker-overview"),
                "input" => false,
            ],
            [
                "name" => "Offene Rechnungen",
                "icon" => false,
                "url" => Link::getPageLink("offene-rechnungen"),
                "input" => false,
            ]
        ];
    }

    public static function getUserLinks(): array
    {
        $userId = User::getCurrentUserId();
        $links = Settings::get("user_" . $userId . "_linkBehavior");
        return json_decode($links, true) ?? [];
    }

    public static function getUserLinksTemplate(): string
    {
        $userLinks = self::getAvailableLinks(); //self::getUserLinks();
        $template = TemplateController::getTemplate("customLinks", [
            "customLinks" => $userLinks,
        ]);

        return $template;
    }
}
