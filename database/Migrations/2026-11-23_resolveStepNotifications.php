<?php

/**
 * Schritte ließen sich bisher nicht abschließen, daher blieben Aufgaben-Benachrichtigungen ewig ungelesen.
 * - Aufgaben zu bereits erledigten Schritten (istErledigt = 0 heißt "erledigt") werden als gelesen markiert.
 * - Aufgaben und Aufgaben-Updates zu gelöschten Schritten werden entfernt.
 */
return new class () {
    private $queries = [
        "UPDATE `user_notifications` n
            JOIN `schritte` s ON s.`Schrittnummer` = n.`specific_id`
            SET n.`ischecked` = 1
            WHERE n.`type` = 1 AND s.`istErledigt` = 0 AND n.`ischecked` = 0;",
        "DELETE n FROM `user_notifications` n
            LEFT JOIN `schritte` s ON s.`Schrittnummer` = n.`specific_id`
            WHERE n.`type` IN (1, 7) AND s.`Schrittnummer` IS NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
