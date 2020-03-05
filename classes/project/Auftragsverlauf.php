<?php

require_once('classes/DBAccess.php');

/*
 * Zu protokollierende Daten:
 * posten:   Beim Hinzufügen / Löschen / Bearbeiten speichern                       ✔
 * step:     Beim Hinzufügen / Löschen / Bearbeiten / Abarbeiten speichern          ✔
 * vehicle:  Beim Hinzufügen / Löschen / Bearbeiten / Bild hochladen abspeichern    ✔
 * file:     Beim Hinzufügen / Löschen abspeichern                                  ✔
 * rechnung: Beim Erstellen der Rechnung abspeichern
 * angebot:  Beim Erstellen / Übernehmen abspeichern
*/

/*
 * States:
 * added
 * deleted
 * edited
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
    public function addToHistory($number, $type, $state) {
        DBAccess::insertQuery("INSERT INTO history (`orderid`, `number`, `type`, `state`) VALUES ({$this->auftragsnummer}, $number, $type, '$state')");
    }

    public function getHistory() {
        $query = "SELECT history.id, history.insertstamp, history_type.name, CONCAT(COALESCE(postendata.Beschreibung, ''), COALESCE(schritte.Bezeichnung, '')) AS Beschreibung FROM history LEFT JOIN history_type ON history_type.type_id = history.type LEFT JOIN postendata ON postendata.Auftragsnummer = history.orderid AND postendata.Postennummer = history.number LEFT JOIN schritte ON schritte.Auftragsnummer = history.orderid AND schritte.Schrittnummer = history.number WHERE history.orderid = {$this->auftragsnummer}";
        return DBAccess::selectQuery($query);
    }

    public function representHistoryAsHTML() {
        $history = $this->getHistory();
        $html = "";
        foreach ($history as $h) {
            $html .= "<div class=\"showInMiddle\">{$h['name']}: {$h['Beschreibung']}</div><div class=\"line\"></div>";
        }
        return $html;
    }

}

?>
