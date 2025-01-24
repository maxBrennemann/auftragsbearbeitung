<?php

function getTableConfig()
{
    return [
        "address" => [
            "columns" => [
                "id",
                "id_customer",
                "ort",
                "plz",
                "strasse",
                "hausnr",
                "zusatz",
                "country",
                "art",
            ],
            "primaryKey" => "id",
            "names" => [
                "Id",
                "Kundennummer",
                "Ort",
                "PLZ",
                "Straße",
                "Hausnummer",
                "Zusatz",
                "Land",
                "Art der Adresse",
            ],
            "permissions" => ["reade", "create", "update"],
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
        "auftragstyp" => [
            "columns" => [
                "id",
                "Auftragstyp",
            ],
            "primaryKey" => ["id"],
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
                "afterRead" => [\Classes\Project\Color::class, "convertHexToHTML"],
            ],
            "joins" => [],
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
            ]
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
            ]
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
