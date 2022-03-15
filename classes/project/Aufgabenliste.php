<?php

error_reporting(E_ALL);

class Aufgabenliste {
    
	public static function aktuelleSchritteAlsTabelleAusgeben() {
		$query = "SELECT IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Name, auftrag.Auftragsbezeichnung, schritte.Bezeichnung, schritte.Datum, auftrag.Auftragsnummer FROM schritte LEFT JOIN auftrag ON schritte.Auftragsnummer = auftrag.Auftragsnummer LEFT JOIN kunde ON kunde.Kundennummer = auftrag.Kundennummer WHERE auftrag.Rechnungsnummer = 0 AND auftrag.archiviert != 0 AND schritte.istErledigt = 1 ORDER BY schritte.Priority DESC";

		$data = DBAccess::selectQuery($query);
		$column_names = array(0 => array("COLUMN_NAME" => "Name"), 1 => array("COLUMN_NAME" => "Auftragsbezeichnung"),
		2 => array("COLUMN_NAME" => "Bezeichnung"), 3 => array("COLUMN_NAME" => "Datum"));

		/*$form = new FormGenerator("schritte", "", "");
		$table = $form->createTableByData($data, $column_names, "auftrag", null);
		return $table;*/

		$linker = new Link();
		$linker->addBaseLink("auftrag");
		$linker->setIterator("id", $data, "Auftragsnummer");

		$table = new Table();
		$table->createByData($data, $column_names);
		$table->addLink($linker);
		return $table->getTable();
	}

}

?>