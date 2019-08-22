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

	public static function bekommeAllePosten($auftragsnummer) {
		$posten = array();

		$data = DBAccess::selectQuery("SELECT Postennummer, Posten FROM posten WHERE Auftragsnummer = {$auftragsnummer}");
		foreach ($data as $step) {
			$element;
			switch($step['Posten']) {
				case: 'zeit':
					$speziefischerPosten = DBAccess::selectQuery("SELECT ZeitInMinuten, Stundenlohn FROM zeit WHERE zeit.Postennummer = posten.Postennummer")[0];
					$element = new Zeit($speziefischerPosten['Stundenlohn'], $speziefischerPosten['ZeitInMinuten']);
					break;
				case: 'produkt_posten':
					$element = new ProduktPosten();
					break;
				case: 'leistung':
					$elemen = new Leistung();
					break;
			}
			array_push($posten, $element);
		}

		return $posten;
	}

}

?>