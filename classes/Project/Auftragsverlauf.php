<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use Classes\Login;

/*
 * Zu protokollierende Daten:
 * posten:   Beim Hinzufügen / Löschen / Bearbeiten speichern                       ✔
 * step:     Beim Hinzufügen / Löschen / Bearbeiten / Abarbeiten speichern          ✔
 * vehicle:  Beim Hinzufügen / Löschen / Bearbeiten / Bild hochladen abspeichern    ✔
 * file:     Beim Hinzufügen / Löschen abspeichern                                  ✔
 * notiz:    Beim Hinzufügen / Löschen abspeichern                                  ✔
 * rechnung: Beim Erstellen der Rechnung abspeichern
 * angebot:  Beim Erstellen / Übernehmen abspeichern
*/

/*
 * States:
 * added
 * deleted
 * edited
 * finished
*/

class Auftragsverlauf
{

    private $auftragsnummer = 0;

    function __construct($auftragsnummer)
    {
        $this->auftragsnummer = (int) $auftragsnummer;
        if ($this->auftragsnummer <= 0) {
            throw new \Error("Auftrag existiert nicht");
        }
    }

    /*
     * Zu jeder Änderung in einem Auftrag wird in die Auftragsverlaufstabelle ein Eintrag
     * geschrieben, der diese Änderung protokolliert, dabei wird die Art der Änderung, die
     * Id zur Identifikation der anderen Tabellenspalten und ein Zeitstempel miteingetragen.
     * Eventuell kann später noch ein Notizfeld hinzugefügt werden.
     */
    public function addToHistory($number, $type, $state, $alternative_text = "")
    {
        $userId = Login::getUserId();
        $query = "INSERT INTO history (orderid, `number`, `type`, `state`, member_id, alternative_text) VALUES (:orderId, :number, :type, :state, :userId, :alternative_text)";
        $params = array(
            ":orderId" => $this->auftragsnummer,
            ":number" => $number,
            ":type" => $type,
            ":state" => $state,
            ":userId" => $userId,
            ":alternative_text" => $alternative_text
        );
        DBAccess::insertQuery($query, $params);
    }

    /*
     * added member join to get the user id
    */
    public function getHistory()
    {
        $query = "SELECT history.id, history.insertstamp, history_type.name , CONCAT(COALESCE(history.alternative_text, ''), COALESCE(ids.descr, '')) AS Beschreibung, history.state, user.username, user.prename
            FROM history
            LEFT JOIN (
                (SELECT CONCAT(fahrzeuge.Kennzeichen, ' ', fahrzeuge.Fahrzeug) AS `descr`, fahrzeuge_auftraege.id_fahrzeug AS id, 3 AS `type` FROM fahrzeuge, fahrzeuge_auftraege WHERE fahrzeuge.Nummer = fahrzeuge_auftraege.id_fahrzeug)
                UNION
                (SELECT notes.note AS `descr`, notes.id AS id, 7 AS `type` FROM notes)
            ) ids ON history.number = ids.id 
            AND history.type = ids.type
            LEFT JOIN history_type ON history_type.type_id = history.type
            LEFT JOIN user ON user.id = history.member_id
            WHERE history.orderid = {$this->auftragsnummer}";

        return DBAccess::selectQuery($query);
    }

    public function representHistoryAsHTML()
    {
        $history = $this->getHistory();
        $html = "";
        foreach ($history as $h) {
            $datetime = $h['insertstamp'];
            $datetime = date('d.m.Y H:i', strtotime($datetime));
            $beschreibung = $h['Beschreibung'];
            $person = $h['prename'];

            switch ($h['state']) {
                case "added":
                    $html .= "<div class=\"showInMiddle\">{$h['name']}: <i>{$beschreibung}</i><br>hinzugefügt am {$datetime}<br>von {$person}</div><div class=\"verticalLine\"></div>";
                    break;
                case "edited":
                    // muss noch ergänzt werden, irgenwas mit bearbeitet
                    $html .= "";
                    break;
                case "finished":
                    $html .= "<div class=\"showInMiddle\">{$h['name']}: <i>{$beschreibung}</i><br>abgeschlossen am {$datetime}<br>von {$person}</div><div class=\"verticalLine\"></div>";
                    break;
                case "deleted":
                    $html .= "<div class=\"showInMiddle\">{$h['name']}: <i>{$beschreibung}</i><br>gelöscht am {$datetime}<br>von {$person}</div><div class=\"verticalLine\"></div>";
                    break;
            }
        }

        $html .= "<div class=\"showInMiddle\">Keine (weiteren) Einträge</div>";

        return $html;
    }
}
