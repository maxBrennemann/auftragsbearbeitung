<?php

/**
 * Alte Benachrichtigungen speicherten rohes HTML (<a href=...>) im content-Feld.
 * Der Inhalt wird jetzt als reiner Text gerendert; die Verlinkung kommt aus type + specific_id.
 * Zusätzlich werden kaputte Alt-Einträge ohne gültige Referenz (Auftrag 0) entfernt.
 */
return new class () {
    private $queries = [
        "UPDATE `user_notifications`
            SET `content` = REGEXP_REPLACE(`content`, '<[^>]*>', '')
            WHERE `content` REGEXP '<[^>]+>';",
        "DELETE FROM `user_notifications`
            WHERE `type` = 6 AND `specific_id` = 0 AND `content` = 'Auftrag 0 wurde abgeschlossen';",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
