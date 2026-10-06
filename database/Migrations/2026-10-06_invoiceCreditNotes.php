<?php

/**
 * Gutschriften zur Rechnung (kaufmännische Gutschrift / Rechnungskorrektur nach § 17 UStG):
 * nachträgliche Minderung des Entgelts einer bereits ausgestellten Rechnung, z. B. ein
 * Preisnachlass. Die Originalrechnung bleibt unverändert, die Gutschrift ist ein eigener
 * Beleg mit eigener Nummer aus dem Rechnungsnummernkreis (invoice_number_tracker) und
 * Bezug auf die Rechnung.
 *
 * net_amount ist der positive Netto-Minderungsbetrag. vat_rate wird beim Erstellen
 * festgeschrieben, damit der Beleg unabhängig von späteren Änderungen der Einstellung
 * invoice.vatRate reproduzierbar bleibt.
 */
return new class () {

    private $queries = [
        "CREATE TABLE invoice_credit_note (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_id INT NOT NULL,
            credit_number INT NOT NULL,
            creation_date DATE NOT NULL,
            net_amount DECIMAL(10,2) NOT NULL,
            vat_rate DECIMAL(5,2) NOT NULL,
            reason VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY credit_number (credit_number),
            KEY invoice_id (invoice_id)
        );",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
