<?php

require "UpdateMySql.php";

return new class extends UpdateMySql {

    public function upgrade() {
        $values = [
            [8, "Wenn Du einen vorhandenen Aufkleber aktualisierst, werden die gespeicherten Informationen von Deinen neuen Einstellungen überschrieben. Vorsicht hierbei ist geboten, wenn der Aufkleber nicht über dieses Tool erstellt wurde oder im Shop Backoffice nachträglich bearbeitet wurde, da es dann vorkommen kann, dass die hier angezeigten Daten veraltet sind. Bitte überprüfe somit manuell, ob Du etwas überschreiben könntest, wenn Du Dir nicht sicher bist."],
            [9, "Wenn Du eine vorhandenes Wandtattoo aktualisierst, werden die gespeicherten Informationen von Deinen neuen Einstellungen überschrieben. Vorsicht hierbei ist geboten, wenn der Aufkleber nicht über dieses Tool erstellt wurde oder im Shop Backoffice nachträglich bearbeitet wurde, da es dann vorkommen kann, dass die hier angezeigten Daten veraltet sind. Bitte überprüfe somit manuell, ob Du etwas überschreiben könntest, wenn Du Dir nicht sicher bist."],
            [10, "Wenn Du eine vorhandenes Textil aktualisierst, werden die gespeicherten Informationen von Deinen neuen Einstellungen überschrieben. Vorsicht hierbei ist geboten, wenn der Aufkleber nicht über dieses Tool erstellt wurde oder im Shop Backoffice nachträglich bearbeitet wurde, da es dann vorkommen kann, dass die hier angezeigten Daten veraltet sind. Bitte überprüfe somit manuell, ob Du etwas überschreiben könntest, wenn Du Dir nicht sicher bist."],
        ];
        DBAccess::insertMultiple("INSERT INTO info_texte (id, info) VALUES ", $values);

        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DELETE FROM info_texte WHERE id IN (8, 9, 10");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>