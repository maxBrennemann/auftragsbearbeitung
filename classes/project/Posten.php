<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Zeit.php');
require_once('Produkt.php');
require_once('Leistung.php');
require_once('classes/DBAccess.php');

abstract class Posten {
	
	abstract protected function bekommePreis();
	abstract protected function getHTMLData();

	protected $postenTyp;

	public static function bekommeAllePosten($auftragsnummer) {
		$posten = array();

		$data = DBAccess::selectQuery("SELECT Postennummer, Posten FROM posten WHERE Auftragsnummer = {$auftragsnummer}");
		foreach ($data as $step) {
			$element;
			switch($step['Posten']) {
				case 'zeit':
					$speziefischerPosten = DBAccess::selectQuery("SELECT ZeitInMinuten, Stundenlohn FROM zeit WHERE zeit.Postennummer = {$step['Postennummer']}")[0];
					$element = new Zeit($speziefischerPosten['Stundenlohn'], $speziefischerPosten['ZeitInMinuten']);
					break;
				case 'produkt_posten':
					$query = "SELECT Preis, Bezeichnung, Beschreibung, produkt_posten.Produktnummer FROM produkt_posten LEFT JOIN produkt ON ";
					$query .= "produkt_posten.Produktnummer = produkt.Produktnummer WHERE produkt_posten.Postennummer = posten.Postennummer";
					$speziefischerPosten = DBAccess::selectQuery($query)[0];
					$element = new ProduktPosten($speziefischerPosten['Preis'], $speziefischerPosten['Bezeichnung'], $speziefischerPosten['Beschreibung']);
					break;
				case 'leistung':
					$speziefischerPosten = DBAccess::selectQuery("SELECT Bezeichnung, Beschreibung FROM leistung WHERE leistung.Postennummer = {$step['Postennummer']}")[0];
					$element = new Leistung($speziefischerPosten['Bezeichnung'], $speziefischerPosten['Beschreibung']);
					break;
			}
			array_push($posten, $element);
		}

		return $posten;
	}

}

?>