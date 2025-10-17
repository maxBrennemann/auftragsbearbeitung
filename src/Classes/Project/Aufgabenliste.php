<?php

namespace Src\Classes\Project;

use Src\Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;

class Aufgabenliste
{
    public static function aktuelleSchritteAlsTabelleAusgeben(): void
    {
        $query = "SELECT IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Name, auftrag.Auftragsbezeichnung, schritte.Bezeichnung, IF(schritte.Datum = '0000-00-00', 'kein Datum', schritte.Datum) AS Datum, auftrag.Auftragsnummer 
            FROM schritte 
            LEFT JOIN auftrag ON schritte.Auftragsnummer = auftrag.Auftragsnummer 
            LEFT JOIN kunde ON kunde.Kundennummer = auftrag.Kundennummer 
            WHERE auftrag.Rechnungsnummer = 0 
                AND auftrag.`status` != '" . OrderState::Default->value . "'
                AND schritte.istErledigt = 1 
            ORDER BY schritte.Priority DESC";
    }
}
