<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Auftrag.php');
require_once('StatisticsInterface.php');
require_once('classes/DBAccess.php');
require_once('classes/project/Address.php');

class Kunde implements StatisticsInterface {

    private $kundennummer = null;
	private $vorname = null;
	private $nachname = null;
	private $firmenname = null;
	private $strasse = null;
	private $hausnummer = null;
    private $postleitzahl = null;
    private $ort = null;
    private $email = null;
    private $telefonFestnetz = null;
	private $telefonMobil = null;
	private $website = null;

	/* new */
	private $addresses = array();

	function __construct($kundennummer) {
		$data = DBAccess::selectQuery("SELECT * FROM kunde, `address` WHERE Kundennummer = $kundennummer AND kunde.id_address_primary = address.id");
		if (!empty($data)) {
			$data = $data[0];
			$this->kundennummer = $data['Kundennummer'];
			$this->vorname = $data['Vorname'];
			$this->nachname = $data['Nachname'];
			$this->firmenname = $data['Firmenname'];
			$this->strasse = $data['strasse'];
			$this->hausnummer = $data['hausnr'];
			$this->postleitzahl = (int) $data['plz'];
			$this->ort = $data['ort'];
			$this->email = $data['Email'];
			$this->telefonFestnetz = $data['TelefonFestnetz'];
			$this->telefonMobil = $data['TelefonMobil'];
			$this->website = $data['Website'];
		} else {
			echo "<div class=\"defcont\"><form><input type=\"number\"><input type=\"submit\">Neue Kundennummer setzen</form></div><br>";
			
			throw new Exception("Kundennummer " . $kundennummer . " existiert nicht oder kann nicht gefunden werden");
		}
	}

	public function getKundennummer() {
		return $this->kundennummer;
	}

	public function getVorname() {
		return $this->vorname;
	}

	public function getNachname() {
		return $this->nachname;
	}

	public function getFirmenname() {
		return $this->firmenname;
	}

	public function getStrasse($id = 0) {
		if ($id != 0) {
			$this->loadAddresses();
			if (array_key_exists($id, $this->addresses))
				return $this->addresses[$id]->getStrasse();
			else
				return "";
		}
		return $this->strasse;
	}

	public function getHausnummer($id = 0) {
		if ($id != 0) {
			$this->loadAddresses();
			if (array_key_exists($id, $this->addresses))
				return $this->addresses[$id]->getHausnummer();
			else
				return "";
		}
		if ($this->hausnummer == 0 || $this->hausnummer == "0") 
			return "";
		return $this->hausnummer;
	}

	public function getPostleitzahl($id = 0) {
		if ($id != 0) {
			$this->loadAddresses();
			if (array_key_exists($id, $this->addresses))
				return $this->addresses[$id]->getPostleitzahl();
			else
				return "";
		}
		if ($this->postleitzahl == 0 || $this->postleitzahl == "0")
			return "";
		return $this->postleitzahl;
	}

	public function getOrt($id = 0) {
		if ($id != 0) {
			$this->loadAddresses();
			if (array_key_exists($id, $this->addresses))
				return $this->addresses[$id]->getOrt();
			else
				return "";
		}
		return $this->ort;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getWebsite() {
		return $this->website;
	}

	public function isPrivate() {
		
	}

	public function getName() {
		return $this->getVorname() . ' ' . $this->getNachname();
	}

	public function getTelefonFestnetz() {
		return $this->telefonFestnetz;
	}

	public function getTelefonMobil() {
		return $this->telefonMobil;
	}

	public function getFarben() {
		$farben = DBAccess::selectQuery("SELECT CONCAT(Farbe, ' ', Bezeichnung, ' ', Hersteller) AS Farbe, Auftragsnummer, Farbwert FROM color, color_auftrag, auftrag WHERE Kundennummer = {$this->kundennummer} AND color.id = color_auftrag.id_color AND color_auftrag.id_auftrag = Auftragsnummer");
		
		for ($i = 0; $i < sizeof($farben); $i++) {
			$farbe = $farben[$i]["Farbwert"];
			$farben[$i]["Farbwert"] = "<div class=\"farbe\" style=\"background-color: #$farbe\"></div>";
		}

		$column_names = array(
			0 => array("COLUMN_NAME" => "Farbe"), 
			1 => array("COLUMN_NAME" => "Farbwert"), 
			2 => array("COLUMN_NAME" => "Auftragsnummer"));

		$form = new FormGenerator("", "", "");
		return $form->createTableByData($farben, $column_names);
	}

	public function getAuftraege() {
		$auftraege = DBAccess::selectQuery("SELECT Auftragsnummer, Auftragsbezeichnung, Auftragsbeschreibung, Datum, Termin, Fertigstellung, Rechnungsnummer, archiviert FROM auftrag WHERE Kundennummer = {$this->kundennummer}");
		$column_names = array(0 => array("COLUMN_NAME" => "Auftragsnummer"), 1 => array("COLUMN_NAME" => "Auftragsbezeichnung"), 
		2 => array("COLUMN_NAME" => "Auftragsbeschreibung"), 3 => array("COLUMN_NAME" => "Datum"), 4 => array("COLUMN_NAME" => "Termin"), 5 => array("COLUMN_NAME" => "Fertigstellung"), 6 => array("COLUMN_NAME" => "Rechnungsnummer"), 7 => array("COLUMN_NAME" => "archiviert"));

		$form = new FormGenerator("", "", "");
		return $form->createTableByDataRowLink($auftraege, $column_names, "auftrag", Link::getPageLink("auftrag"));
	}

	public function getOrderCards() {
		$auftraege = DBAccess::selectQuery("SELECT Auftragsnummer FROM auftrag WHERE Kundennummer = {$this->kundennummer}");
		$html = "<div style=\"display: flex; flex-wrap: wrap;\">";
		foreach ($auftraege as $id) {
			$order = new Auftrag($id['Auftragsnummer']);
			$html .= $order->getOrderCard();
		}
		$html .= "</div";
		return $html;
	}

	public function getAnsprechpartner() {
		return "";
	}

	public function getNotizen() {
		$data = DBAccess::selectQuery("SELECT notizen FROM kunde_extended WHERE kundennummer = {$this->kundennummer}");
		if ($data != null) {
			return $data[0]['notizen'];
		}
		return "";
	}

	public function getFahrzeuge() {
		$fahrzeuge = DBAccess::selectQuery("SELECT Kennzeichen, Fahrzeug, Nummer FROM fahrzeuge WHERE Kundennummer = {$this->getKundennummer()}");
		$column_names = array(0 => array("COLUMN_NAME" => "Nummer"), 1 => array("COLUMN_NAME" => "Kennzeichen"), 2 => array("COLUMN_NAME" => "Fahrzeug"));
		$fahrzeugTable = new FormGenerator("fahrzeug", "", "");
		return $fahrzeugTable->createTableByDataRowLink($fahrzeuge, $column_names, "fahrzeug", "fahrzeug");
	}

	public function recalculate() {
	
	}

	private function loadAddresses() {
		if ($this->addresses != null)
			return null;

		$addresses = Address::loadAllAddresses($this->kundennummer);
		foreach ($addresses as $address) {
			$newAddress = Address::loadAddress($address["id"]);
			$this->addresses[$address["id"]] = $newAddress;
		}
	}

	public static function addAddress($id_customer, $strasse, $hausnummer, $postleitzahl, $ort, $zusatz, $land, $art = 3) {
		return Address::createNewAddress($id_customer, $strasse, $hausnummer, $postleitzahl, $ort, $zusatz, $land, $art);
	}

	public static function getNextAssignedKdnr($kdnr, $direction) {
		if ($direction == 1) {
			$result = DBAccess::selectQuery("SELECT Kundennummer FROM kunde WHERE kundennummer > $kdnr LIMIT 1");
		} else if ($direction == -1) {
			$result = DBAccess::selectQuery("SELECT Kundennummer FROM kunde WHERE kundennummer < $kdnr ORDER BY Kundennummer DESC LIMIT 1");
		}
		if ($result == null) {
			return -1;
		} else {
			return (int) $result[0]['Kundennummer'];
		}
	}

	public function getHTMLShortSummary() {
		$link = Link::getPageLink("kunde") . "?id=" . $this->kundennummer;
		$text = "<div class=\"shortSummary\"><div class=\"shortSummaryHeader\">";
		if ($this->firmenname == "") {
			$text .= "<a href=\"$link\">{$this->vorname} {$this->nachname}</a></div>";
		} else {
			$text .= "<a href=\"$link\">{$this->firmenname}</a></div>";
		}
		$text .= "<p>{$this->strasse} {$this->hausnummer}<br>{$this->postleitzahl} {$this->ort}<br>";
		
		if ($this->telefonFestnetz != null) {
			$text .= "☎ {$this->telefonFestnetz}<br>";
		} 
		
		if ($this->telefonMobil != null) {
			$text .= "✆ {$this->telefonMobil}<br>";
		}

		$text .= "<br>";

		if ($this->email != null) {
			$text .= "@ <a href=\"mailto:{$this->email}\">{$this->email}</a><br>";
		}

		if ($this->website != null) {
			$text .= "🔗 <a href=\"{$this->website}\">Zur Website</a></div>";
		} else {
			$text .= "</p></div>";
		}

		return $text;
	}

}

?>