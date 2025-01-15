<?php

function getTableConfig()
{
    return [
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
                "beforeSelect" => "",
                "beforeDelete" => [\Classes\Project\Auftrag::class, "resetAnsprechpartner"],
            ],
            "joins" => [],
        ],
    ];
}

function getTableConfigFrontOffice()
{
    $tableConfig = getTableConfig();
    $data = [];
    foreach ($tableConfig as $key => $table) {
        $data[$key] = [
            "primaryKey" => $table["primaryKey"],
            "columns" => [],
        ];

        foreach ($table["columns"] as $index => $column) {
            $label = $table["names"][$index];
            $data[$key]["columns"][] = [
                "key" => $column,
                "label" => $label,
            ];
        }
    }

    return $data;
}
