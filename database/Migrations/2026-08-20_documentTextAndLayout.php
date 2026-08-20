<?php

use MaxBrennemann\PhpUtilities\DBAccess;

/**
 * Generalisiert den bisher rechnungsspezifischen Baustein-Text-Mechanismus
 * (invoice_text: toggle-bare Zusatztexte, invoice_layout: manuelles Drag-Reorder
 * von Posten/Texten/Fahrzeugen) auf beliebige Dokumenttypen, damit Angebote
 * (OfferPDF) denselben Mechanismus nutzen können, ohne ihn zu duplizieren -
 * analog zur bereits erfolgten Umstellung von invoice_alt_names auf
 * customer_alt_names (siehe 2026-08-14_offerAddressAndAltNamesRename.php).
 *
 * `document_type` unterscheidet künftig die Dokumente ('invoice', 'offer', ...),
 * `document_id` ersetzt die bisherigen id_invoice/invoice_id-Spalten. Bestehende
 * Rechnungsdaten werden auf document_type = 'invoice' gesetzt, damit sich am
 * Verhalten für Rechnungen nichts ändert.
 *
 * invoice_layout.invoice_id trägt einen FOREIGN KEY auf invoice(id) (siehe
 * 2025-06-27_invoiceOrder.php) - der muss vor der Umwidmung der Spalte zu
 * document_id entfernt werden, sonst würden künftige Angebots-Zeilen (deren
 * document_id auf offer.id statt invoice.id zeigt) an der Fremdschlüsselprüfung
 * scheitern. Der Constraint wurde ohne expliziten Namen angelegt, MariaDB
 * vergibt dafür je nach DB-Historie unterschiedliche automatische Namen -
 * deshalb wird der tatsächliche Name hier zur Laufzeit aus information_schema
 * ermittelt statt hartkodiert zu werden.
 */
return new class () {
    /** @var array<int, string> */
    private array $queries;

    public function __construct()
    {
        $this->queries = [
            "RENAME TABLE `invoice_text` TO `document_text`;",
            "ALTER TABLE `document_text`
                CHANGE `id_invoice` `document_id` INT NOT NULL,
                ADD `document_type` VARCHAR(16) NOT NULL DEFAULT 'invoice' AFTER `id`;",

            "RENAME TABLE `invoice_layout` TO `document_layout`;",
        ];

        $foreignKeyName = $this->findLayoutForeignKeyName();
        if ($foreignKeyName !== null) {
            $this->queries[] = "ALTER TABLE `document_layout` DROP FOREIGN KEY `{$foreignKeyName}`;";
        }

        $this->queries[] = "ALTER TABLE `document_layout` DROP INDEX `invoice_id`;";
        $this->queries[] = "ALTER TABLE `document_layout`
            CHANGE `invoice_id` `document_id` INT NOT NULL,
            ADD `document_type` VARCHAR(16) NOT NULL DEFAULT 'invoice' AFTER `id`;";
        $this->queries[] = "ALTER TABLE `document_layout` ADD UNIQUE(`document_type`, `document_id`, `content_type`, `content_id`);";
    }

    private function findLayoutForeignKeyName(): ?string
    {
        $query = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_layout' AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            LIMIT 1;";
        $data = DBAccess::selectQuery($query);

        return $data[0]["CONSTRAINT_NAME"] ?? null;
    }

    public function getQueries()
    {
        return $this->queries;
    }
};
