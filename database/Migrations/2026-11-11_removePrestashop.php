<?php

/**
 * Entfernt die komplette PrestaShop-Anbindung samt der zugehörigen
 * Sticker-/Shop-Produktverwaltung.
 *
 * Die Sticker-Domäne (`src/Classes/Sticker/`, `StickerRoutes`, die Seiten
 * `sticker`, `sticker-overview`, `sticker-images`, `tags-overview`, das
 * Frontend unter `public/res/js/sticker/` sowie die Cron-Tasks
 * `SyncPrestashop`/`UpdatePrestashop`/`FacebookExport`/`ImportStats`) existierte
 * ausschließlich, um Motiv-/Sticker-Produkte über die PrestaShop-Webservice-API
 * zu verwalten und in den Shop zu exportieren. Mit dem Wegfall der Anbindung
 * entfallen auch die 16 `module_sticker_*`-Tabellen und der Trigger
 * `create_sizes`, der beim Anlegen eines Motivs Standard-Größen erzeugte.
 *
 * `produkt.id_category` bleibt bewusst bestehen: sie referenziert die interne
 * `category`-Tabelle (`Src\Classes\Project\Category`) und hat nichts mit
 * PrestaShop-Kategorien zu tun.
 *
 * FOREIGN_KEY_CHECKS wird für die Dauer der Migration deaktiviert, damit die
 * Tabellen unabhängig von ihrer gegenseitigen Fremdschlüssel-Reihenfolge
 * (u. a. `module_sticker_exports` -> `module_sticker_sticker_data`) gedroppt
 * werden können.
 */
return new class () {
    /** @var array<int, string> */
    private array $queries = [
        "SET FOREIGN_KEY_CHECKS = 0;",

        "DROP TRIGGER IF EXISTS `create_sizes`;",

        "DROP TABLE IF EXISTS `module_sticker_accessoires`;",
        "DROP TABLE IF EXISTS `module_sticker_categories`;",
        "DROP TABLE IF EXISTS `module_sticker_changelog`;",
        "DROP TABLE IF EXISTS `module_sticker_chatgpt`;",
        "DROP TABLE IF EXISTS `module_sticker_exports`;",
        "DROP TABLE IF EXISTS `module_sticker_image`;",
        "DROP TABLE IF EXISTS `module_sticker_log_id`;",
        "DROP TABLE IF EXISTS `module_sticker_search_data`;",
        "DROP TABLE IF EXISTS `module_sticker_sizes`;",
        "DROP TABLE IF EXISTS `module_sticker_sticker_data`;",
        "DROP TABLE IF EXISTS `module_sticker_sticker_tag`;",
        "DROP TABLE IF EXISTS `module_sticker_sticker_tag_group`;",
        "DROP TABLE IF EXISTS `module_sticker_sticker_tag_group_match`;",
        "DROP TABLE IF EXISTS `module_sticker_tags`;",
        "DROP TABLE IF EXISTS `module_sticker_textiles`;",
        "DROP TABLE IF EXISTS `module_sticker_texts`;",

        "SET FOREIGN_KEY_CHECKS = 1;",
    ];

    /**
     * @return array<int, string>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }
};
