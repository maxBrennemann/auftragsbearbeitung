<?php

namespace Classes\Project;

class SearchUtils
{
    public const CONFIG = [
        "kunde" => [
            "columns" => [
                "Firmenname" => ["type" => "text", "alias" => "name", "fuzzy" => true],
                "Vorname" => ["type" => "text", "alias" => "name", "fuzzy" => true],
                "Nachname" => ["type" => "text", "alias" => "name", "fuzzy" => true],
                "Email" => ["type" => "text", "alias" => "email", "fuzzy" => false],
                "Website" => ["type" => "text", "alias" => "details", "fuzzy" => true],
                "TelefonFestnetz" => ["type" => "phone", "alias" => ["tel", "phone"]],
                "TelefonMobil" => ["type" => "phone", "alias" => ["tel", "phone"]],
                "fax" => ["type" => "phone", "alias" => ["tel", "phone"]],
                "note" => ["type" => "text", "alias" => "details", "fuzzy" => true],
            ],
            "joins" => [
                "contactPerson" => "contact_person.customer_id = customer.id",
                "address" => "",
            ],
            "id" => "Kundennummer",
        ],
        "produkt" => [
            "columns" => [
                "Marke" => ["type" => "text", "alias" => "name", "fuzzy" => true],
                "Bezeichnung" => ["type" => "text", "alias" => "details", "fuzzy" => true],
                "Beschreibung" => ["type" => "text", "alias" => "details", "fuzzy" => true],
            ],
            "id" => "Nummer",
        ],
        "auftrag" => [
            "columns" => [
                "Auftragsbezeichnung" => ["type" => "text", "alias" => "name", "fuzzy" => true],
                "Auftragsbeschreibung" => ["type" => "text", "alias" => "details", "fuzzy" => true],
            ],
            "id" => "Auftragsnummer",
        ],
        "wiki_articles" => [
            "columns" => [
                "content" => ["type" => "text", "alias" => "details", "fuzzy" => true],
                "title" => ["type" => "text", "alias" => "name", "fuzzy" => true],
                "keywords" => ["type" => "text", "alias" => "details", "fuzzy" => true],
            ],
            "id" => "id",
        ],
    ];

    public static function parseSearchInput(string $input): array
    {
        $matches = [];
        preg_match_all('/(\w+):([^\s]+)/', $input, $matches, PREG_SET_ORDER);

        $filters = [];
        foreach ($matches as $match) {
            $filters[$match[1]] = $match[2];
        }

        $freeText = trim(preg_replace('/\w+:[^\s]+/', '', $input));

        return [
            $filters,
            $freeText,
        ];
    }

    public static function normalizePhone(string $number): int
    {
        return (int) preg_replace('/\D+/', '', $number);
    }
}
