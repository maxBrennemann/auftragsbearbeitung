<?php

require_once('classes/DBAccess.php');
require_once('classes/Login.php');

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

class Auftragsverlauf {

    private $auftragsnummer = 0;

    function __construct($auftragsnummer) {
        $this->auftragsnummer = (int) $auftragsnummer;
        if ($this->auftragsnummer <= 0) {
            throw new error("Auftrag existiert nicht");
        }
    }

    /*
     * Zu jeder Änderung in einem Auftrag wird in die Auftragsverlaufstabelle ein Eintrag
     * geschrieben, der diese Änderung protokolliert, dabei wird die Art der Änderung, die
     * Id zur Identifikation der anderen Tabellenspalten und ein Zeitstempel miteingetragen.
     * Eventuell kann später noch ein Notizfeld hinzugefügt werden.
     */
    public function addToHistory($number, $type, $state, $alternative_text = "") {
        $userId = Login::getUserId();
        DBAccess::insertQuery("INSERT INTO history (`orderid`, `number`, `type`, `state`, `member_id`, `alternative_text`) VALUES ({$this->auftragsnummer}, $number, $type, '$state', $userId, '$alternative_text')");
    }

    /*
     * added member join to get the user id
    */
    public function getHistory() {
        $query = "SELECT history.id, history.insertstamp, history_type.name, 
                CONCAT(COALESCE(history.alternative_text, 'Fehler: kein Text gefunden'), COALESCE(CONCAT(fahrzeuge.Kennzeichen, ' ', fahrzeuge.Fahrzeug), ''), COALESCE(notizen.Notiz, '')) AS Beschreibung, members.username, mitarbeiter.vorname, history.state
            FROM history 
            LEFT JOIN history_type ON history_type.type_id = history.type 
            LEFT JOIN postendata ON postendata.Auftragsnummer = history.orderid 
                AND postendata.Postennummer = history.number 
            LEFT JOIN schritte ON schritte.Auftragsnummer = history.orderid 
                AND schritte.Schrittnummer = history.number 
            LEFT JOIN members ON members.id = history.member_id
            LEFT JOIN members_mitarbeiter ON members.id = members_mitarbeiter.id_member
            LEFT JOIN mitarbeiter ON members_mitarbeiter.id_mitarbeiter = mitarbeiter.id
            LEFT JOIN fahrzeuge_auftraege ON fahrzeuge_auftraege.id_auftrag = history.orderid
                AND fahrzeuge_auftraege.id_fahrzeug = history.number
            LEFT JOIN fahrzeuge ON fahrzeuge.Nummer = fahrzeuge_auftraege.id_fahrzeug
            LEFT JOIN notizen ON notizen.Nummer = history.number
            WHERE history.orderid = {$this->auftragsnummer}
                ";

        $new_query = "SELECT history.id, history.insertstamp, history_type.name , CONCAT(COALESCE(history.alternative_text, ''), COALESCE(ids.descr, '')) AS Beschreibung, history.state, members.username, mitarbeiter.vorname
            FROM history
            LEFT JOIN (
                (SELECT CONCAT(fahrzeuge.Kennzeichen, ' ', fahrzeuge.Fahrzeug) AS `descr`, fahrzeuge_auftraege.id_fahrzeug AS id, 3 AS `type` FROM fahrzeuge, fahrzeuge_auftraege WHERE fahrzeuge.Nummer = fahrzeuge_auftraege.id_fahrzeug)
                UNION
                (SELECT notizen.Notiz AS `descr`, notizen.Nummer AS id, 7 AS `type` FROM notizen)
            ) ids ON history.number = ids.id 
            AND history.type = ids.type
            LEFT JOIN history_type ON history_type.type_id = history.type
            LEFT JOIN members ON members.id = history.member_id
            LEFT JOIN members_mitarbeiter ON members.id = members_mitarbeiter.id_member
            LEFT JOIN mitarbeiter ON members_mitarbeiter.id_mitarbeiter = mitarbeiter.id
            WHERE history.orderid = {$this->auftragsnummer}";

        return DBAccess::selectQuery($new_query);
    }

    public function representHistoryAsHTML() {
        $history = $this->getHistory();
        $html = "";
        foreach ($history as $h) {
            $datetime = $h['insertstamp'];
            $datetime = date('d.m.Y H:i', strtotime($datetime));
            $beschreibung = $h['Beschreibung'];
            $person = $h['vorname'];

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

        $html .= "<div class=\"showInMiddle\">Keine weiteren Einträge</div>";

        return $html;
    }

}

?>
