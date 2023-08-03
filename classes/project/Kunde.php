<?php

require_once('Auftrag.php');
require_once('StatisticsInterface.php');
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
		$query = "SELECT CONCAT(Farbe, ' ', Bezeichnung, ' ', Hersteller) AS Farbe, Auftragsnummer, Farbwert FROM color, color_auftrag, auftrag WHERE Kundennummer = :kdnr AND color.id = color_auftrag.id_color AND color_auftrag.id_auftrag = Auftragsnummer";
		$data = DBAccess::selectQuery($query, [
			"kdnr" => $this->kundennummer
		]);

		foreach ($data as $key => $value) {
			$data[$key]["Farbwert"] = "<div class=\"farbe\" style=\"background-color: #" . $value["Farbwert"] . "\"></div>";
		}

		$column_names = array(
			0 => array("COLUMN_NAME" => "Farbe"), 
			1 => array("COLUMN_NAME" => "Farbwert"), 
			2 => array("COLUMN_NAME" => "Auftragsnummer"));

		$table = new Table();
		$table->createByData($data, $column_names);
	
		return $table->getTable();
	}

	public function getAuftraege() {
		$auftraege = DBAccess::selectQuery("SELECT Auftragsnummer, Auftragsbezeichnung, Auftragsbeschreibung, Datum, Termin, Fertigstellung, Rechnungsnummer, archiviert FROM auftrag WHERE Kundennummer = {$this->kundennummer}");
		$column_names = array(0 => array("COLUMN_NAME" => "Auftragsnummer"), 1 => array("COLUMN_NAME" => "Auftragsbezeichnung"), 
		2 => array("COLUMN_NAME" => "Auftragsbeschreibung"), 3 => array("COLUMN_NAME" => "Datum"), 4 => array("COLUMN_NAME" => "Termin"), 5 => array("COLUMN_NAME" => "Fertigstellung"), 6 => array("COLUMN_NAME" => "Rechnungsnummer"), 7 => array("COLUMN_NAME" => "archiviert"));

		$form = new FormGenerator("", "", "");
		return $form->createTableByDataRowLink($auftraege, $column_names, "auftrag", Link::getPageLink("auftrag"));
	}

	public function getOrderCards() {
		$query = "SELECT Auftragsnummer FROM auftrag WHERE Kundennummer = :kdnr ORDER BY Auftragsnummer DESC";
		$data = DBAccess::selectQuery($query, [
			"kdnr" => $this->kundennummer
		]);

		$orders = [];
		foreach ($data as $key => $value) {
			$order = new Auftrag($value["Auftragsnummer"]);
			$orders[] = $order->getOrderCardData();
		}

		ob_start();
		insertTemplate('files/res/views/orderCardView.php', [
			"orders" => $orders,
		]);
		$content = ob_get_clean();
		return $content;
	}

	public function getAnsprechpartner() {
		return "";
	}

	public function getNotizen() {
		$data = DBAccess::selectQuery("SELECT notizen FROM kunde_extended WHERE kundennummer = :kdnr", [
			"kdnr" => $this->getKundennummer(),
		]);

		if ($data != null) {
			return $data[0]['notizen'];
		}
		return "";
	}

	public function getFahrzeuge() {
		$query = "SELECT Kennzeichen, Fahrzeug, Nummer FROM fahrzeuge WHERE Kundennummer = :kdnr";
		$data = DBAccess::selectQuery($query, [
			"kdnr" => $this->getKundennummer(),
		]);

		$column_names = array(
			0 => array("COLUMN_NAME" => "Nummer"),
			1 => array("COLUMN_NAME" => "Kennzeichen"),
			2 => array("COLUMN_NAME" => "Fahrzeug")
		);

		$link = new Link();
		$link->addBaseLink("fahrzeug");
		$link->setIterator("id", $data, "Nummer");

		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addLink($link);
		return $t->getTable();
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

	/**
	 * @return int
	 */
	public static function addCustomer($data): int {
		/* insert customer data */
		$query = "INSERT INTO kunde (Firmenname, Anrede, Vorname, Nachname, Email, TelefonFestnetz, TelefonMobil, Website) VALUES (:firmenname, :anrede, :vorname, :nachname, :email, :telfestnetz, :telmobil, :website)";
		$customerId = DBAccess::insertQuery($query, [
			"firmenname" => $data["customerName"] ?? "",
			"anrede" => (int) $data["anrede"],
			"vorname" => $data["prename"] ?? "",
			"nachname" => $data["surname"] ?? "",
			"email" => $data["companyemail"],
			"telfestnetz" => $data["telfestnetz"],
			"telmobil" => $data["telmobil"],
			"website" => $data["website"],
		]);

		/* insert address data */
		$query = "INSERT INTO address (id_customer, strasse, hausnr, plz, ort, zusatz, country) VALUES (:id_customer, :strasse, :hausnr, :plz, :ort, :zusatz, :country)";
		$addressId = DBAccess::insertQuery($query, [
			"id_customer" => $customerId,
			"strasse" => $data["street"],
			"hausnr" => $data["houseNumber"],
			"plz" => (int) $data["plz"],
			"ort" => $data["city"],
			"zusatz" => $data["addressAddition"],
			"country" => $data["country"],
		]);

		/* update customer data */
		DBAccess::updateQuery("UPDATE kunde SET id_address_primary = :addressId WHERE Kundennummer = :customerId", [
			"addressId" => $addressId,
			"customerId" => $customerId,
		]);

		/* insert ansprechpartner data */
		if ($data["type"] == "company") {
			$query = "INSERT INTO ansprechpartner (Kundennummer, Vorname, Nachname, Email, Durchwahl, Mobiltelefonnummer) VALUES (:customerId, :vorname, :nachname, :email, :durchwahl, :mobiltelefonnummer)";
			DBAccess::insertQuery($query, [
				"customerId" => $customerId,
				"vorname" => $data["contactPrename"],
				"nachname" => $data["contactSurname"],
				"email" => $data["emailaddress"],
				"durchwahl" => $data["phoneExtension"],
				"mobiltelefonnummer" => $data["mobileNumber"],
			]);
		}

		if ($data["notes"] != "") {
			$query = "INSERT INTO kunde_extended (kundennummer, notizen) VALUES (:customerId, :notes);";
			DBAccess::insertQuery($query, [
				"customerId" => $customerId,
				"notes" => $data["notes"],
			]);
		}

		return $customerId;
	}

}
