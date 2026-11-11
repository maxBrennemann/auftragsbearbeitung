<?php
declare(strict_types=1);

return [
    "global" => [
        "page_title" => 'Übersicht',
    ],
    "payment_types" => [
        "unbezahlt" => "Unbezahlt",
        "ueberweisung" => "Überweisung",
        "bar" => "Bar",
        "paypal" => "PayPal",
        "kreditkarte" => "Kreditkarte",
        "amazonpay" => "AmazonPay",
        "weiteres" => "Weiteres",
        "unknown" => "Unbekannte Zahlungsart",
    ],
    "upload" => [
        "singleFile" => "Datei auswählen",
        "multipleFiles" => "Dateien auswählen",
        "orDragAndDrop" => "oder hier ablegen.",
    ],
    "info" => [
        "lists" => 
            'Wähle aus einer der erstellten Listen. Listen können im Auftrag als Stütze für die Herangehensweise verwendet werden. Um eine neue Liste zu erstellen, gehe auf "Listen" im Endbereich der Seite.',
        "invoice_mail" =>
            'Es wird automatisch eine E-Mail an die hier hinterlegte Adresse gesendet, wenn eine Rechnung für diesen Kunden erstellt wird. Du musst die Funktion nicht gesondert aktivieren.',
        "vat_info" =>
            '0 = Kleinunternehmer (ohne MwSt.)',
    ],
];
