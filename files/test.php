<?php

$table = new Classes\Project\Table();

$columns = [
    "Bezeichnung",
    "Beschreibung",
    "Betrag",
];

$columnsWithSettings = [
    "Bezeichnung" => [
        "status" => "unset",
        "value" => 0
    ],
    "Beschreibung" => [
        "status" => "unset",
        "value" => 1
    ],
    "Betrag" => [
        "status" => "unset",
        "value" => 2,
        "type" => "float",
        "cast" => ["separator" => ",", "from" => "euro", "result" => "integer"],
    ],
];
