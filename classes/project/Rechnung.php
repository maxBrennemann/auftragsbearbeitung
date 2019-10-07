<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Auftrag.php');
require_once('classes/DBAccess.php');

class Rechnung extends Auftrag {

	private $summeMwSt = 0;
	private $summe = 0;

	function __construct($rechnungsnummer) {
		$auftragsnummer = DBAccess::selectQuery("SELECT Auftragsnummer FROM auftrag WHERE Rechnungsnummer = {$rechnungsnummer}");
		if (!empty($auftragsnummer)) {
			$auftragsnummer = $auftragsnummer[0]['Auftragsnummer'];
		} else {
			trigger_error("Rechnungsnummer does not match to Auftragsnummer");
		}
		parent::__construct($auftragsnummer);
	}

    public function PDFgenerieren() {
        
    }

	public static function getNextNumber() {
		$number = DBAccess::selectQuery("SELECT MAX(Rechnungsnummer) FROM auftrag")[0]['MAX(Rechnungsnummer)'];
		$number = (int) $number;
		$number++;
		return $number;
	}

	public static function getOffeneRechnungen() {
		$column_names = array(0 => array("COLUMN_NAME" => "Auftragsnummer"), 1 => array("COLUMN_NAME" => "Kundennummer"), 2 => array("COLUMN_NAME" => "Firmenname"),
				3 => array("COLUMN_NAME" => "Auftragsbezeichnung"), 4 => array("COLUMN_NAME" => "Auftragsbeschreibung"), 5 => array("COLUMN_NAME" => "Datum"), 
				6 => array("COLUMN_NAME" => "Termin"), 7 => array("COLUMN_NAME" => "AngenommenDurch"));
		$data = DBAccess::selectQuery("SELECT auftrag.*, kunde.Firmenname FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Rechnungsnummer != 0 AND Bezahlt = 0");

		for ($i = 0; $i < sizeof($data); $i++) {
			$id = $data[$i]["AngenommenDurch"];
			$angenommenDurch = DBAccess::selectQuery("SELECT Vorname, Nachname FROM mitarbeiter WHERE id = $id");
			$data[$i]["AngenommenDurch"] = $angenommenDurch[0]["Vorname"] . " " . $angenommenDurch[0]["Nachname"];
		}

		$form = new FormGenerator("auftrag", "Datum", "Rechnungsnummer = 0");
		$table = $form->createTableByDataRowLink($data, $column_names, "auftrag", null);
		return $table;
	}

	public static function getOffeneRechnungssumme() {
		$numbers = DBAccess::selectQuery("SELECT Auftragsnummer FROM auftrag WHERE Rechnungsnummer != 0");
		$summe = 0;
		foreach ($numbers as $auftrag) {
			$auftrag = new Auftrag($auftrag['Auftragsnummer']);
			$summe += $auftrag->preisBerechnen();
		}

		return $summe;
	}

}

?>