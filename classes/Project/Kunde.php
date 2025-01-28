<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use Classes\Link;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Kunde implements StatisticsInterface
{

	private int $kundennummer = 0;
	private string $vorname = "";
	private string $nachname = "";
	private string $firmenname = "";
	private string $strasse = "";
	private string $hausnummer = "";
	private int $postleitzahl = 0;
	private string $ort = "";
	private string $email = "";
	private string $telefonFestnetz = "";
	private string $telefonMobil = "";
	private string $website = "";

	private $addresses = [];

	public function __construct(int $kundennummer)
	{
		$data = DBAccess::selectQuery("SELECT * FROM kunde, `address` WHERE Kundennummer = :customerId AND kunde.id_address_primary = address.id;", [
			"customerId" => $kundennummer,
		]);

		if (empty($data)) {
			throw new \Exception("Customer id does not exist or cannot be found");
		}

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
		$this->website = $data['Website'] ?? "";
	}

	public function getKundennummer(): int
	{
		return $this->kundennummer;
	}

	public function getVorname(): string
	{
		return $this->vorname;
	}

	public function getNachname(): string
	{
		return $this->nachname;
	}

	public function getFirmenname(): string
	{
		return $this->firmenname;
	}

	public function getAlternativeName(): string
	{
		if ($this->firmenname != "") {
			return $this->firmenname;
		}

		return $this->getName();
	}

	public function getStrasse(int $id = 0): string
	{
		if ($id != 0) {
			$this->loadAddresses();

			if (array_key_exists($id, $this->addresses)) {
				return $this->addresses[$id]->getStrasse();
			} else {
				return "";
			}
		}

		return $this->strasse;
	}

	public function getHausnummer($id = 0): string
	{
		if ($id != 0) {
			$this->loadAddresses();
			if (array_key_exists($id, $this->addresses)) {
				return $this->addresses[$id]->getHausnummer();
			} else {
				return "";
			}
		}

		return $this->hausnummer;
	}

	public function getPostleitzahl($id = 0): string
	{
		$plz = $this->postleitzahl;

		if ($id != 0) {
			$this->loadAddresses();
			if (array_key_exists($id, $this->addresses)) {
				$plz = (string) $this->addresses[$id]->getPostleitzahl();
			} else {
				$plz = "";
			}
		}

		if ($plz == 0) {
			return "";
		}

		return $plz;
	}

	public function getOrt($id = 0): string
	{
		if ($id != 0) {
			$this->loadAddresses();
			if (array_key_exists($id, $this->addresses)) {
				return $this->addresses[$id]->getOrt();
			} else {
				return "";
			}
		}
		return $this->ort;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getWebsite(): string
	{
		return $this->website;
	}

	public function getName(): string
	{
		return $this->getVorname() . " " . $this->getNachname();
	}

	public function getTelefonFestnetz(): string
	{
		return $this->telefonFestnetz;
	}

	public function getTelefonMobil(): string
	{
		return $this->telefonMobil;
	}

	public function getColors()
	{
		$query = "SELECT CONCAT(color_name, ' ', short_name, ' ', producer) AS color, Auftragsnummer, hex_value 
			FROM color, color_auftrag, auftrag 
			WHERE Kundennummer = :kdnr 
				AND color.id = color_auftrag.id_color 
				AND color_auftrag.id_auftrag = Auftragsnummer";

		$data = DBAccess::selectQuery($query, [
			"kdnr" => $this->kundennummer
		]);

		foreach ($data as $key => $value) {
			$data[$key]["hex_value"] = "<div class=\"farbe\" style=\"background-color: #" . $value["hex_value"] . "\"></div>";
		}

		$column_names = array(
			0 => array("COLUMN_NAME" => "color"),
			1 => array("COLUMN_NAME" => "hex_value"),
			2 => array("COLUMN_NAME" => "Auftragsnummer")
		);

		$table = new Table();
		$table->createByData($data, $column_names);

		return $table->getTable();
	}

	public function getOrderIds(): array
	{
		$query = "SELECT Auftragsnummer FROM auftrag WHERE Kundennummer = :kdnr ORDER BY Auftragsnummer DESC";
		$data = DBAccess::selectQuery($query, [
			"kdnr" => $this->kundennummer
		]);

		return $data;
	}

	public function getOrderCards()
	{
		$data = $this->getOrderIds();
		$orders = [];

		foreach ($data as $row) {
			$order = new Auftrag($row["Auftragsnummer"]);
			$orders[] = $order->getOrderCardData();
		}

		ob_start();
		insertTemplate('files/res/views/orderCardView.php', [
			"orders" => $orders,
		]);
		$content = ob_get_clean();

		return $content;
	}

	public function getContactPersons(): array
	{
		$query = "SELECT a.Nummer AS id, a.Vorname AS firstName, a.Nachname AS lastName, a.Email AS email
			FROM ansprechpartner a 
			WHERE a.Kundennummer = :kdnr";
		$data = DBAccess::selectQuery($query, [
			"kdnr" => $this->getKundennummer(),
		]);

		return $data;
	}

	public function getNotizen()
	{
		$data = DBAccess::selectQuery("SELECT notizen FROM kunde_extended WHERE kundennummer = :kdnr", [
			"kdnr" => $this->getKundennummer(),
		]);

		if ($data != null) {
			return $data[0]['notizen'];
		}
		return "";
	}

	public function getVehicles()
	{
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

	public function recalculate() {}

	private function loadAddresses()
	{
		if ($this->addresses != null) {
			return null;
		}

		$addresses = Address::loadAllAddresses($this->kundennummer);
		foreach ($addresses as $address) {
			$newAddress = Address::loadAddress($address["id"]);
			$this->addresses[$address["id"]] = $newAddress;
		}
	}

	public static function addAddress(int $id_customer, string $strasse, string $hausnummer, int $postleitzahl, string $ort, string $zusatz, string $land, int $art = 3)
	{
		return Address::createNewAddress($id_customer, $strasse, $hausnummer, $postleitzahl, $ort, $zusatz, $land, $art);
	}

	public function getHTMLShortSummary()
	{
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

	public static function getContacts()
	{
		$kdnr = (int) Tools::get("id");
		$data = DBAccess::selectQuery("SELECT Nummer AS id, Vorname AS firstName, Nachname AS lastName, Email AS email 
			FROM ansprechpartner 
			WHERE Kundennummer = :kdnr", [
			"kdnr" => $kdnr,
		]);

		JSONResponseHandler::sendResponse($data);
	}

	public static function addCustomer($data): int
	{
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
			"website" => $data["website"] ?? "",
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

	public static function addCustomerAjax()
	{
		$data = Tools::get("data");
		$data = json_decode($data, true);

		$customerId = self::addCustomer($data);
		$link = Link::getPageLink("kunde");
		$link .= "?id=" . $customerId;

		JSONResponseHandler::sendResponse([
			"status" => "success",
			"link" => $link,
		]);
	}

	public static function getAllCustomerOverviews()
	{
		$query = "SELECT Kundennummer FROM kunde ORDER BY CONCAT(Firmenname, Nachname);";
		$data = DBAccess::selectQuery($query);

		$customers = [];
		foreach ($data as $row) {
			$customers[] = new Kunde($row["Kundennummer"]);
		}

		return $customers;
	}
}
