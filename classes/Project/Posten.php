<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use Classes\Upload;
use Classes\Link;

abstract class Posten
{

	abstract protected function bekommePreis();
	abstract protected function bekommeEinzelPreis();
	abstract protected function bekommePreis_formatted();
	abstract protected function bekommeEinzelPreis_formatted();
	abstract protected function bekommeDifferenz();
	abstract protected function calculateDiscount();
	abstract protected function getHTMLData();
	abstract protected function fillToArray($arr);
	abstract protected function getDescription();
	abstract protected function getEinheit();
	abstract protected function getQuantity();
	abstract protected function isInvoice();
	abstract protected function storeToDB($auftragsnummer);

	protected $postenTyp;
	protected $ohneBerechnung = false;
	protected $postennummer;

	/*
	 * function gets all posten data for an order
	 * second parameter is not necessary, it switches to the invoice mode
	 */
	public static function bekommeAllePosten($auftragsnummer, $invoice = false)
	{
		$posten = array();

		if ($invoice) {
			$data = DBAccess::selectQuery("SELECT Postennummer, Posten, ohneBerechnung, discount, isInvoice FROM posten WHERE Auftragsnummer = $auftragsnummer AND (rechnungsNr != 0 OR isInvoice = 1) ORDER BY position");
		} else {
			$data = DBAccess::selectQuery("SELECT Postennummer, Posten, ohneBerechnung, discount, isInvoice FROM posten WHERE Auftragsnummer = $auftragsnummer ORDER BY position");
		}
		foreach ($data as $step) {
			switch ($step['Posten']) {
				case 'zeit':
					$speziefischerPosten = DBAccess::selectQuery("SELECT Nummer, ZeitInMinuten, Stundenlohn, Beschreibung FROM zeit WHERE zeit.Postennummer = {$step['Postennummer']}")[0];
					$element = new Zeit($speziefischerPosten['Stundenlohn'], $speziefischerPosten['ZeitInMinuten'], $speziefischerPosten['Beschreibung'], $step['discount'], (int) $step['isInvoice']);
					$element->setSpecificNumber($speziefischerPosten['Nummer']);
					break;
				case 'produkt':
					$query = "SELECT Preis, Bezeichnung, Beschreibung, pp.Produktnummer, Anzahl, p.Einkaufspreis FROM produkt_posten AS pp, produkt AS p, posten AS po ";
					$query .= "WHERE pp.Produktnummer = p.Nummer AND pp.Postennummer = po.Nummer AND pp.Postennummer = {$step['Postennummer']}";
					$speziefischerPosten = DBAccess::selectQuery($query)[0];
					$element = new ProduktPosten($speziefischerPosten['Preis'], $speziefischerPosten['Bezeichnung'], $speziefischerPosten['Beschreibung'], $speziefischerPosten['Anzahl'], $speziefischerPosten['Einkaufspreis'], "", $step['discount'], (int) $step['isInvoice']);
					break;
				case 'leistung':
					$query = "SELECT Leistungsnummer, Beschreibung, SpeziefischerPreis, Einkaufspreis, qty, meh FROM leistung_posten WHERE leistung_posten.Postennummer = :postennummer";
					$data = DBAccess::selectQuery($query, array("postennummer" => $step['Postennummer']));

					if (count($data) != 0) {
						$speziefischerPosten = $data[0];
						$element = new Leistung($speziefischerPosten['Leistungsnummer'], $speziefischerPosten['Beschreibung'], $speziefischerPosten['SpeziefischerPreis'], $speziefischerPosten['Einkaufspreis'], $speziefischerPosten['qty'], $speziefischerPosten['meh'], $step['discount'], (int) $step['isInvoice']);
					} else {
						continue 2;
					}
					break;
				case 'compact':
					$speziefischerPosten = DBAccess::selectQuery("SELECT amount, marke, price, purchasing_price, `description`, `name` FROM product_compact WHERE product_compact.postennummer = {$step['Postennummer']}")[0];
					$element = new ProduktPosten($speziefischerPosten['price'], $speziefischerPosten['name'], $speziefischerPosten['description'], $speziefischerPosten['amount'], $speziefischerPosten['purchasing_price'], $speziefischerPosten['marke'], $step['discount'], (int) $step['isInvoice']);
					break;
			}

			$free = (int) $step['ohneBerechnung'];
			if ($free == 1) {
				$element->ohneBerechnung = true;
			}

			$element->postennummer = $step['Postennummer'];

			array_push($posten, $element);
		}

		return $posten;
	}

	public static function getOrderItems(int $orderId, string $itemType = "")
	{
		$items = [];
		$query = "SELECT Postennummer, Posten, ohneBerechnung, discount, isInvoice FROM posten WHERE Auftragsnummer = :orderId ORDER BY position;";

		if ($itemType == "invoice") {
			$query = "SELECT Postennummer, Posten, ohneBerechnung, discount, isInvoice FROM posten WHERE Auftragsnummer = :orderId AND isInvoice = 1 ORDER BY position";
		}

		$data = DBAccess::selectQuery($query, [
			"orderId" => $orderId,
		]);

		foreach ($data as $row) {
			$type = $row["Posten"];
			switch ($type) {
				case "zeit":
					break;
				case "leistung":
					break;
				case "product":
					break;
				case "compact":
					break;
			}
		}

		return $items;
	}

	public static function insertPosten($type, $data)
	{
		$auftragsnummer = $data['Auftragsnummer'];

		if ((int) $auftragsnummer != -1) {
			$auftragsverlauf = new Auftragsverlauf((int) $auftragsnummer);
		}

		$fre = $data['ohneBerechnung'];
		$dis = $data['discount'] == null ? 0 : $data['discount'];
		$inv = $data['addToInvoice'] == null ? 0 : $data['addToInvoice'];

		if (isset($_SESSION['overwritePosten']) && $_SESSION['overwritePosten'] == true) {
			$postennummer = (int) $_SESSION['overwritePosten_postennummer'];
			DBAccess::updateQuery("UPDATE posten SET ohneBerechnung = $fre, discount = $dis, isInvoice = $inv WHERE Postennummer = $postennummer");

			/* deletes zeiterfassung if it exists */
			$nummer = DBAccess::selectQuery("SELECT Nummer FROM zeit WHERE Postennummer = $postennummer");
			if ($nummer != null) {
				$deleteId = $nummer[0]["Nummer"];
				DBAccess::deleteQuery("DELETE FROM zeiterfassung WHERE id_zeit = $deleteId");
			}

			/* quick fixed for overwrite */
			DBAccess::deleteQuery("DELETE FROM zeit WHERE Postennummer = $postennummer");
			DBAccess::deleteQuery("DELETE FROM leistung_posten WHERE Postennummer = $postennummer");
			DBAccess::deleteQuery("DELETE FROM product_compact WHERE Postennummer = $postennummer");
		} else {
			$postennummer = DBAccess::insertQuery("INSERT INTO posten (Auftragsnummer, Posten, ohneBerechnung, discount, isInvoice, position) SELECT $auftragsnummer, '$type', $fre, $dis, $inv, count(*) + 1 FROM posten WHERE Auftragsnummer = $auftragsnummer");
		}

		switch ($type) {
			case "zeit":
				$zeit = $data['ZeitInMinuten'];
				$lohn = $data['Stundenlohn'];
				$desc = $data['Beschreibung'];

				$subPosten = DBAccess::insertQuery("INSERT INTO zeit (Postennummer, ZeitInMinuten, Stundenlohn, Beschreibung) VALUES ($postennummer, $zeit, $lohn, '$desc')");
				break;
			case "leistung":
				$lei = $data['Leistungsnummer'];
				$bes = $data['Beschreibung'];
				$ekp = $data['Einkaufspreis'];
				$pre = $data['SpeziefischerPreis'];
				$anz = $data['anzahl'];
				$meh = $data['MEH'];

				$subPosten = DBAccess::insertQuery("INSERT INTO leistung_posten (Leistungsnummer, Postennummer, Beschreibung, Einkaufspreis, SpeziefischerPreis, meh, qty) VALUES($lei, $postennummer, '$bes', '$ekp', '$pre', '$meh', '$anz')");
				//Leistung::bearbeitungsschritteHinzufuegen($lei, $auftragsnummer);
				/* adds invoice data and prices for payment section */
				//Payments::addPayment();
				break;
			case "produkt":
				$amount = $data['amount'];
				$prodId = $data['prodId'];
				$subPosten = DBAccess::insertQuery("INSERT INTO produkt_posten (Produktnummer, Postennummer, Anzahl) VALUES ($prodId, $postennummer, $amount)");
				break;
			case "compact":
				$amount = $data['amount'];
				$marke = $data['marke'];
				$ekpreis = (float) $data['ekpreis'];
				$vkpreis = (float) $data['vkpreis'];
				$beschreibung = $data['beschreibung'];
				$name = $data['name'];

				$subPosten = DBAccess::insertQuery("INSERT INTO product_compact (postennummer, amount, marke, price, purchasing_price, description, name) VALUES ($postennummer, $amount, '$marke', '$vkpreis', '$ekpreis', '$beschreibung', '$name')");
				break;
		}

		if ((int) $auftragsnummer != -1) {
			$auftragsverlauf->addToHistory($postennummer, 1, "added", $data['Beschreibung']);
		}

		return [$postennummer, $subPosten];
	}

	public static function deletePosten($postenId) {}

	/* 
	 * https://stackoverflow.com/a/5207487/7113688
	 */
	public static function addPosition($orderId)
	{
		$query = "SET @I = 0; UPDATE posten SET `position` = (@I := @I + 1) WHERE Auftragsnummer = $orderId;";
		DBAccess::updateQuery($query);
	}

	/* add files to posten for order table */
	public static function addFile($key, $table)
	{
		$postenId = Table::getIdentifierValue($table, $key);

		$upload = new Upload();
		$upload->uploadFilesPosten($postenId);
	}

	/* adds links to all attached files to the "Einkaufspreis" column */
	protected static function getFiles($postennummer)
	{
		$query = "SELECT dateiname FROM dateien, dateien_posten WHERE dateien.id = dateien_posten.id_file AND dateien_posten.id_posten = $postennummer";
		$data = DBAccess::selectQuery($query);

		$html = "";
		foreach ($data as $d) {
			$link = Link::getResourcesShortLink($d["dateiname"], "upload");
			$html .= "<a href=\"$link\" target=\"_blank\">🗎</a>";
		}

		return $html;
	}
}
