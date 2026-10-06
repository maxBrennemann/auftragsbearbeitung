<?php

/**
 * Stornorechnungen: ersetzt das bisherige "Rechnung zurücksetzen", bei dem eine bereits
 * ausgestellte Rechnung unter derselben Nummer überschrieben werden konnte. Eine Stornorechnung
 * ist technisch eine Gutschrift über den gesamten noch offenen Rechnungsbetrag und nutzt daher
 * dieselbe Tabelle; `type` unterscheidet die beiden Belegarten.
 *
 * Die stornierte Rechnung bekommt invoice.status = 'cancelled' (Spalte ist VARCHAR(16), daher
 * keine Schemaänderung nötig) und bleibt samt PDF erhalten. Für den Auftrag kann danach eine
 * neue Rechnung erstellt werden, ein Auftrag kann also mehrere Rechnungen haben, von denen
 * höchstens eine nicht storniert ist.
 *
 * Der Dateiname ist bewusst so gewählt, dass diese Migration nach 2026-10-06_invoiceCreditNotes.php
 * einsortiert wird: Migrationen desselben Datums laufen in alphabetischer Reihenfolge, und diese
 * hier ändert die dort angelegte Tabelle.
 */
return new class () {

    private $queries = [
        "ALTER TABLE `invoice_credit_note` ADD `type` ENUM('credit','cancellation') NOT NULL DEFAULT 'credit' AFTER `invoice_id`;",
        "ALTER TABLE `invoice` ADD INDEX `order_id` (`order_id`);",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
