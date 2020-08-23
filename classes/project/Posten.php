<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Zeit.php');
require_once('Produkt.php');
require_once('Leistung.php');
require_once('Auftragsverlauf.php');
require_once('classes/DBAccess.php');

abstract class Posten {
	
	abstract protected function bekommePreis();
	abstract protected function bekommeEinzelPreis();
	abstract protected function getHTMLData();
	abstract protected function fillToArray($arr);
	abstract protected function getDescription();
	abstract protected function getEinheit();
	abstract protected function getQuantity();
	abstract protected function storeToDB($auftragsnummer);

	protected $postenTyp;
	protected $ohneBerechnung = false;

	public static function bekommeAllePosten($auftragsnummer) {
		$posten = array();
		
		$data = DBAccess::selectQuery("SELECT Postennummer, Posten, ohneBerechnung FROM posten WHERE Auftragsnummer = {$auftragsnummer}");
		foreach ($data as $step) {
			$element;

			switch($step['Posten']) {
				case 'zeit':
					$speziefischerPosten = DBAccess::selectQuery("SELECT ZeitInMinuten, Stundenlohn, Beschreibung FROM zeit WHERE zeit.Postennummer = {$step['Postennummer']}")[0];
					$element = new Zeit($speziefischerPosten['Stundenlohn'], $speziefischerPosten['ZeitInMinuten'], $speziefischerPosten['Beschreibung']);
					break;
				case 'produkt_posten':
					$query = "SELECT Preis, Bezeichnung, Beschreibung, produkt_posten.Produktnummer, Anzahl, produkt.Einkaufspreis FROM produkt_posten LEFT JOIN produkt ON ";
					$query .= "produkt_posten.Produktnummer = produkt.Produktnummer WHERE produkt_posten.Postennummer = posten.Postennummer";
					$speziefischerPosten = DBAccess::selectQuery($query)[0];
					$element = new ProduktPosten($speziefischerPosten['Preis'], $speziefischerPosten['Bezeichnung'], $speziefischerPosten['Beschreibung'], $speziefischerPosten['Anzahl'], $speziefischerPosten['Einkaufspreis']);
					break;
				case 'leistung':
					$speziefischerPosten = DBAccess::selectQuery("SELECT Leistungsnummer, Beschreibung, SpeziefischerPreis, Einkaufspreis FROM leistung_posten WHERE leistung_posten.Postennummer = {$step['Postennummer']}")[0];
					$element = new Leistung($speziefischerPosten['Leistungsnummer'], $speziefischerPosten['Beschreibung'], $speziefischerPosten['SpeziefischerPreis'], $speziefischerPosten['Einkaufspreis']);
					break;
			}

			$free = (int) $step['ohneBerechnung'];
			if ($free == 1) {
				$element->ohneBerechnung = true;
			}
			array_push($posten, $element);
		}

		return $posten;
	}

	public static function insertPosten($type, $data) {
		$auftragsnummer = $data['Auftragsnummer'];
		$postennummer = (int) DBAccess::selectQuery("SELECT MAX(Postennummer) FROM posten")[0]['MAX(Postennummer)'];
		$postennummer++;

		if ((int) $auftragsnummer != -1) {
			$auftragsverlauf = new Auftragsverlauf($_POST['auftrag']);
		}
		
		switch ($type) {
			case "zeit":
				$zeit = $data['ZeitInMinuten'];
				$lohn = $data['Stundenlohn'];
				$desc = $data['Beschreibung'];
				$fre = $data['ohneBerechnung'];
				DBAccess::insertQuery("INSERT INTO posten (Postennummer, Auftragsnummer, Posten, ohneBerechnung) VALUES ($postennummer, $auftragsnummer, '$type', $fre)");
				DBAccess::insertQuery("INSERT INTO zeit (Postennummer, ZeitInMinuten, Stundenlohn, Beschreibung) VALUES ($postennummer, $zeit, $lohn, '$desc')");
			break;
			case "leistung":
				$lei = $data['Leistungsnummer'];
				$bes = $data['Beschreibung'];
				$ekp = $data['Einkaufspreis'];
				$pre = $data['SpeziefischerPreis'];
				$fre = $data['ohneBerechnung'];
				DBAccess::insertQuery("INSERT INTO posten (Postennummer, Auftragsnummer, Posten, ohneBerechnung) VALUES ($postennummer, $auftragsnummer, '$type', $fre)");
				DBAccess::insertQuery("INSERT INTO leistung_posten (Leistungsnummer, Postennummer, Beschreibung, Einkaufspreis, SpeziefischerPreis) VALUES($lei, $postennummer, '$bes', '$ekp', '$pre')");
				Leistung::bearbeitungsschritteHinzufuegen($lei, $auftragsnummer);
			break;
			case "produkt":
				$amount = $data['amount'];
				$prodId = $data['prodId'];
				$fre = $data['ohneBerechnung'];
				DBAccess::insertQuery("INSERT INTO posten (Postennummer, Auftragsnummer, Posten, ohneBerechnung) VALUES ($postennummer, $auftragsnummer, '$type', $fre)");
				DBAccess::insertQuery("INSERT INTO produkt_posten (Produktnummer, Postennummer, Anzahl) VALUES ($prodId, $postennummer, $amount)");
			break;
			case "compact_prod":
			break;
		}

		if ((int) $auftragsnummer != -1) {
			$auftragsverlauf->addToHistory($postennummer, 1, "added");
		}
	}

	public static function deletePosten($postenId) {

	}

}

?>