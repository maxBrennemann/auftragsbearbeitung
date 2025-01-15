<?php

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
        "names" => [],
        "permissions" => ["read", "create", "update", "delete"],
        "hooks" => [
            "beforeSelect" => "",
            "beforeDelete" => [\Classes\Project\Auftrag::class, "resetAnsprechpartner"],
        ],
        "joins" => [],
    ]
];
