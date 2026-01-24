<?php

return new class {

    private $queries = [

        // Neue Config Tabelle
        "CREATE TABLE `config_settings` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(64) NOT NULL,
            `userId` INT NOT NULL,
            `content` VARCHAR(128) NULL,
            `numberContent` FLOAT NULL,
            `jsonContent` JSON NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_config (`name`, userId),
            PRIMARY KEY (`id`)) ENGINE = InnoDB;",

        // -------- Datenmigration --------

        // errorReporting
        "INSERT INTO config_settings (name, userId, numberContent)
        SELECT
            'errorReporting',
            0,
            CASE content
                WHEN 'on' THEN 1
                WHEN 'true' THEN 1
                ELSE 0
            END
        FROM settings
        WHERE title = 'errorReporting';",

        // cacheStatus
        "INSERT INTO config_settings (name, userId, numberContent)
        SELECT
            'cacheStatus',
            0,
            CASE content
                WHEN 'on' THEN 1
                WHEN 'true' THEN 1
                ELSE 0
            END
        FROM settings
        WHERE title = 'cacheStatus';",

        /* -------------------------
         * Number-Settings
         * ------------------------- */

        "INSERT INTO config_settings (name, userId, numberContent)
        SELECT 'invoice.defaultWage', 0, CAST(content AS FLOAT)
        FROM settings
        WHERE title = 'defaultWage';",

        "INSERT INTO config_settings (name, userId, numberContent)
        SELECT 'invoice.dueDate', 0, CAST(content AS FLOAT)
        FROM settings
        WHERE title = 'companyDueDate';",

        "INSERT INTO config_settings (name, userId, numberContent)
        SELECT 'company.logoId', 0, CAST(content AS FLOAT)
        FROM settings
        WHERE title = 'companyLogo';",

        /* -------------------------
         * String-Settings
         * ------------------------- */

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.name', 0, content FROM settings WHERE title = 'companyName';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.address', 0, content FROM settings WHERE title = 'companyAddress';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.zip', 0, content FROM settings WHERE title = 'companyZip';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.city', 0, content FROM settings WHERE title = 'companyCity';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.phone', 0, content FROM settings WHERE title = 'companyPhone';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.email', 0, content FROM settings WHERE title = 'companyEmail';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.website', 0, content FROM settings WHERE title = 'companyWebsite';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.country', 0, content FROM settings WHERE title = 'companyCountry';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.imprint', 0, content FROM settings WHERE title = 'companyImprint';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.bank', 0, content FROM settings WHERE title = 'companyBank';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.IBAN', 0, content FROM settings WHERE title = 'companyIban';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.UstIdNr', 0, content FROM settings WHERE title = 'companyUstIdNr';",

        "INSERT INTO config_settings (name, userId, content)
        SELECT 'company.BIC', 0, content FROM settings WHERE title = 'companyBic';",

        "DROP TABLE `settings`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
