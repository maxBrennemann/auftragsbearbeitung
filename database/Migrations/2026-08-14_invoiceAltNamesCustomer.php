<?php

/**
 * Kopfzeilen-Override (invoice_alt_names) galt bisher nur für eine einzelne
 * Rechnung und musste bei jeder neuen Rechnung desselben Kunden erneut
 * eingetippt werden. Diese Migration bindet die Einträge stattdessen an den
 * Kunden (id_customer statt id_invoice), damit sie über alle Rechnungen
 * dieses Kunden hinweg wiederverwendet werden - analog zur `address`-Tabelle.
 *
 * Bestehende Zeilen werden über invoice.order_id -> auftrag.Kundennummer dem
 * jeweiligen Kunden zugeordnet. Zeilen ohne auflösbaren Kunden (verwaiste
 * invoice_alt_names-Einträge) sowie danach entstehende exakte Duplikate
 * (gleicher Kunde, gleicher Text) werden entfernt.
 */
return new class () {

    private $queries = [
        "ALTER TABLE `invoice_alt_names` ADD `id_customer` INT NULL AFTER `id_invoice`;",
        "UPDATE invoice_alt_names ian
            JOIN invoice i ON i.id = ian.id_invoice
            JOIN auftrag a ON a.Auftragsnummer = i.order_id
            SET ian.id_customer = a.Kundennummer;",
        "DELETE FROM invoice_alt_names WHERE id_customer IS NULL;",
        "DELETE t1 FROM invoice_alt_names t1
            INNER JOIN invoice_alt_names t2
            WHERE t1.id > t2.id AND t1.id_customer = t2.id_customer AND t1.text = t2.text;",
        "ALTER TABLE `invoice_alt_names` CHANGE `id_customer` `id_customer` INT NOT NULL;",
        "ALTER TABLE `invoice_alt_names` DROP COLUMN `id_invoice`;",
        "ALTER TABLE `invoice_alt_names` ADD INDEX `idx_id_customer` (`id_customer`);",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
