<?php

function getTableConfig(): array
{
    return [
        "address" => [
            "columns" => [
                "id",
                "id_customer",
                "strasse",
                "hausnr",
                "plz",
                "ort",
                "zusatz",
                "country",
                "art",
            ],
            "primaryKey" => "id",
            "names" => [
                "Id",
                "Kundennummer",
                "Straße",
                "Hausnummer",
                "PLZ",
                "Ort",
                "Zusatz",
                "Land",
                "Art der Adresse",
            ],
            "permissions" => ["read", "create", "update"],
        ],
        "ansprechpartner" => [
            "columns" => [
                "Nummer",
                "Kundennummer",
                "Vorname",
                "Nachname",
                "Email",
                "Durchwahl",
                "Mobiltelefonnummer"
            ],
            "primaryKey" => "Nummer",
            "names" => [
                "Nummer",
                "Kundennummer",
                "Vorname",
                "Nachname",
                "Email",
                "Durchwahl",
                "Mobiltelefonnummer"
            ],
            "permissions" => ["read", "create", "update", "delete"],
            "hooks" => [
                "beforeRead" => "",
                "beforeDelete" => [\Classes\Project\Auftrag::class, "resetAnsprechpartner"],
            ],
            "joins" => [],
        ],
        "auftrag" => [
            "columns" => [
                "Auftragsnummer",
                "Kundennummer",
                "Auftragsbezeichnung",
                "Auftragsbeschreibung",
                "Auftragstyp",
                "Datum",
                "Termin",
                "Fertigstellung",
                "AngenommenDurch",
                "AngenommenPer",
                "Ansprechpartner",
                "Rechnungsnummer",
                "Bezahlt",
                "archiviert",
            ],
            "primaryKey" => "Auftragsnummer",
        ],
        "auftragstyp" => [
            "columns" => [
                "id",
                "Auftragstyp",
            ],
            "primaryKey" => "id",
            "names" => [
                "Nummer",
                "Auftragstyp",
            ],
            "permissions" => ["read", "create", "update"],
        ],
        "color" => [
            "columns" => [
                "id",
                "color_name",
                "hex_value",
                "short_name",
                "producer",
            ],
            "primaryKey" => "id",
            "names" => [
                "Id",
                "Farbname",
                "Hexwert",
                "Kurzbezeichnung",
                "Hersteller",
            ],
            "permissions" => ["read", "create", "update", "delete"],
            "hooks" => [
                "beforeRead" => [],
                "afterRead" => [\Classes\Project\Color::class, "convertHexToHTML"],
            ],
            "joins" => [
                "color_auftrag" => [
                    "relatedTable" => "color_auftrag",
                    "localKey" => "id",
                    "foreignKey" => "id_color",
                ],
                "auftrag" => [
                    "relatedTable" => "auftrag",
                    "localKey" => "Auftragsnummer",
                    "foreignKey" => "Kundennummer",
                ],
            ],
        ],
        "dateien" => [
            "columns" => [
                "id",
                "dateiname",
                "originalname",
                "date",
                "typ",
            ],
            "primaryKey" => "id",
            "names" => [
                "Nummer",
                "Gespeicherter Name",
                "Name",
                "Datum",
                "Dateityp",
            ],
            "hidden" => [
                "id",
                "dateiname",
            ],
            "permissions" => ["read", "update", "delete"],
            "joins" => [
                "connected_orders" => [
                    "relatedTable" => "dateien_auftraege",
                    "localKey" => "id",
                    "foreignKey" => "id_datei",
                ],
                "connected_vehicles" => [
                    "relatedTable" => "dateien_fahrzeuge",
                    "localKey" => "id",
                    "foreignKey" => "id_datei",
                ],
                "connected_items" => [
                    "relatedTable" => "dateien_posten",
                    "localKey" => "id",
                    "foreignKey" => "id_file",
                ],
                "connected_products" => [
                    "relatedTable" => "dateien_produkte",
                    "localKey" => "id",
                    "foreignKey" => "id_datei",
                ],
            ],
        ],
        "einkauf" => [
            "columns" => [
                "id",
                "name",
                "description",
            ],
            "primaryKey" => "id",
            "names" => [
                "Nummer",
                "Name",
                "Beschreibung",
            ],
            "permissions" => ["read", "create", "update"],
        ],
        "fahrzeuge" => [
            "columns" => [
                "Nummer",
                "Kundennummer",
                "Kennzeichen",
                "Fahrzeug"
            ],
            "primaryKey" => "Nummer",
            "names" => [
                "Nummer",
                "Kundennummer",
                "Kennzeichen",
                "Fahrzeug"
            ],
            "permissions" => ["read", "create", "update", "delete"],
            "joins" => [
                "connected_vehicles" => [
                    "relatedTable" => "fahrzeuge_auftraege",
                    "localKey" => "Nummer",
                    "foreignKey" => "id_fahrzeug",
                ],
            ],
        ],
        "leistung" => [
            "columns" => [
                "Nummer",
                "Bezeichnung",
                "Beschreibung",
                "Quelle",
                "Aufschlag",
            ],
            "primaryKey" => "Nummer",
            "names" => [
                "Nummer",
                "Bezeichnung",
                "Beschreibung",
                "Quelle",
                "Aufschlag",
            ],
            "permissions" => [],
        ],
        "module_sticker_image" => [
            "columns" => [
                "id_datei",
                "id_motiv",
                "image_sort",
                "id_product",
                "id_image_shop",
                "description",
                "image_order",
            ],
            "primaryKey" => "id_datei",
            "names" => [
                "Dateinummer",
                "Motivnummer",
                "Bildtyp",
                "Produktnummer",
                "Bildnummer im Shop",
                "Beschreibung",
                "Position",
            ],
            "permissions" => ["read", "create", "update", "delete"],
            "joins" => [
                "files" => [
                    "relatedTable" => "dateien",
                    "localKey" => "id_datei",
                    "foreignKey" => "id",
                ],
            ],
            "hooks" => [
                "afterRead" => [\Classes\Sticker\StickerImage::class, "prepareData"],
                "afterJoin" => [\Classes\Sticker\StickerImage::class, "prepareData"],
            ],
        ],
        "module_sticker_sticker_data" => [
            "columns" => [
                "id",
                "category",
                "name",
                "is_plotted",
                "is_short_time",
                "is_long_time",
                "is_walldecal",
                "is_multipart",
                "is_shirtcollection",
                "is_colorable",
                "is_customizable",
                "is_for_configurator",
                "price_class",
                "size_summary",
                "creation_date",
                "directory_name",
                "is_revised",
                "is_marked",
                "additional_info",
                "additional_data",
            ],
            "primaryKey" => "id",
            "names" => [
                "Nummer",
                "Kategorie",
                "Name",
                "geplottet",
                "Werbeaufkleber",
                "Hochleistungsfolie",
                "Wandtattoo",
                "Mehrteilig",
                "Textil",
                "Einfärbbar",
                "Personalisierbar",
                "Für Konfigurator",
                "Preisklasse",
                "Größen",
                "Erstelldatum",
                "Verzeichnis",
                "Überarbeitet",
                "Gemerkt",
                "Zusatzinfo",
                "Erweiterte Infos",
            ],
            "permissions" => [],
        ],
        "produkt" => [
            "columns" => [
                "Nummer",
                "Marke",
                "Preis",
                "Einkaufspreis",
                "Bezeichnung",
                "Beschreibung",
                "Bild",
                "einkaufs_id",
                "id_category",
            ],
            "primaryKey" => "Nummer",
            "names" => [
                "Nummer",
                "Marke",
                "Preis",
                "Einkaufspreis",
                "Bezeichnung",
                "Beschreibung",
                "Bild",
                "Einkaufsreferenz",
                "Kategorienummer",
            ],
            "hidden" => [
                "Bild",
                "einkaufs_id",
                "id_category",
            ],
            "permissions" => ["read", "create"],
        ],
        "schritte" => [
            "columns" => [
                "Schrittnummer",
                "Auftragsnummer",
                "assignedTo",
                "Bezeichnung",
                "Datum",
                "Priority",
                "finishingDate",
                "istErledigt",
            ],
            "primaryKey" => "Schrittnummer",
            "names" => [
                "Nummer",
                "Auftrag",
                "allgemein",
                "Bezeichnung",
                "Datum",
                "Priorität",
                "Erledigt am",
                "Status",
            ],
            "hooks" => [
                "afterRead" => [\Classes\Project\Step::class, "prepareData"],
            ],
            "permissions" => ["read"],
        ],
        "user" => [
            "columns" => [
                "id",
                "lastname",
                "prename",
                "username",
                "email",
                "password",
                "validated",
                "role",
                "max_working_hours",
            ],
            "primaryKey" => "id",
            "names" => [
                "Nummer",
                "Nachname",
                "Vorname",
                "Username",
                "Email",
                "Passwort",
                "Validiert",
                "Rolle",
                "Arbeitsstunden",
            ],
            "hidden" => [
                "password",
                "validated",
            ],
            "permissions" => ["read", "create", "update"],
        ],
    ];
}

function getTableConfigFrontOffice()
{
    $tableConfig = getTableConfig();
    $data = [];
    foreach ($tableConfig as $key => $table) {
        $data[$key] = [
            "primaryKey" => $table["primaryKey"] ?? "",
            "columns" => [],
        ];

        $table["columns"] = array_filter(
            $table["columns"],
            fn($el) => !in_array($el, $table["hidden"] ?? [])
        );

        foreach ($table["columns"] as $index => $column) {
            $label = $table["names"][$index] ?? $column;
            $data[$key]["columns"][] = [
                "key" => $column,
                "label" => $label,
            ];
        }
    }

    return $data;
}
