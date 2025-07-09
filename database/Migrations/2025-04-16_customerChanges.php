<?php

return new class () {
    private $queries = [
        "DROP TABLE IF EXISTS kundenlogin;",
        "ALTER TABLE `kunde` ADD `fax` VARCHAR(32) NULL DEFAULT NULL AFTER `Website`, ADD `note` TEXT NULL DEFAULT NULL AFTER `fax`;",
        "UPDATE kunde
        JOIN kunde_extended ON kunde.Kundennummer = kunde_extended.kundennummer
        SET 
            kunde.note = kunde_extended.notizen,
            kunde.fax = kunde_extended.Faxnummer;",
        "CREATE TABLE customer_changelog (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            valid_until DATETIME DEFAULT NULL,
            field_changed VARCHAR(64) NOT NULL,
            old_value TEXT,
            new_value TEXT,
            changed_by_user_id INT DEFAULT NULL,
            FOREIGN KEY (customer_id) REFERENCES kunde(Kundennummer)
        );",
        "DROP TABLE IF EXISTS kunde_extended;",
        "DROP TABLE IF EXISTS kunde_aenderungen;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
