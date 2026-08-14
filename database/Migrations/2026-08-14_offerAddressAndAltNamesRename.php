<?php

/**
 * Rollt die bereits für Rechnungen vorhandene Adress-/Kontakt-/Kopfzeilen-Übernahme
 * (address_id/contact_id + Kopfzeilen-Override) auf Angebote aus, da die dafür nötige
 * Basis (TransactionPDF::$addressId/$contactId, fillAddress($altNames)) bereits
 * dokumenttyp-übergreifend existiert.
 *
 * `invoice_alt_names` wird zu `customer_alt_names` umbenannt, weil die Tabelle seit der
 * Umstellung auf id_customer (siehe 2026-08-14_invoiceAltNamesCustomer.php) kein
 * Rechnungs-Konzept mehr ist, sondern ein Kunden-Konzept - und jetzt auch von Angeboten
 * genutzt wird.
 */
return new class () {

    private $queries = [
        "ALTER TABLE `offer` ADD `contact_id` INT NULL AFTER `customer_id`, ADD `address_id` INT NULL AFTER `contact_id`;",
        "RENAME TABLE `invoice_alt_names` TO `customer_alt_names`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
