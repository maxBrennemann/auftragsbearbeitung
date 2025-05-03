<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

use Classes\Project\Modules\Pdf\TransactionPdf\InvoicePDF;

class Invoice
{

	private Kunde $kunde;
	private Auftrag $auftrag;
	private int $addressId = 0;
	private int $contactId = 0;

	private int $invoiceId = 0;
	private int $invoiceNumber = 0;
	private array $posten = [];

	private ?\DateTime $creationDate = null;
	private ?\DateTime $performanceDate = null;

	public function __construct(int $invoiceId, int $orderId)
	{
		$this->auftrag = new Auftrag($orderId);
		$customerId = $this->auftrag->getKundennummer();
		$this->kunde = new Kunde($customerId);

		$this->invoiceId = $invoiceId;

		$query = "SELECT * FROM invoice WHERE id = :invoiceId";
		$data = DBAccess::selectQuery($query, [
			"invoiceId" => $invoiceId,
		]);

		if (empty($data)) {
			throw new \Exception("Invoice not found.");
		}

		$this->invoiceNumber = $data[0]["invoice_number"] ?? 0;
		$this->creationDate = new \DateTime($data[0]["creation_date"]);
		$this->performanceDate = new \DateTime($data[0]["performance_date"]);
		$this->addressId = (int) $data[0]["address_id"];
		$this->contactId = (int) $data[0]["contact_id"];
		$this->getTexts();
	}

	public function getAddressId(): int
	{
		return $this->addressId;
	}

	public function getContactId(): int
	{
		return $this->contactId;
	}

	public static function getInvoice(int $orderId): Invoice
	{
		$query = "SELECT id FROM invoice WHERE order_id = :orderId;";
		$data = DBAccess::selectQuery($query, [
			"orderId" => $orderId,
		]);

		if (!empty($data)) {
			$invoiceId = $data[0]["id"];
			return new Invoice($invoiceId, $orderId);
		}

		$query = "INSERT INTO invoice (invoice_number, order_id, creation_date, performance_date, amount) VALUES (0, :orderId, :creationDate, :performanceDate, :amount)";
		$invoiceId = DBAccess::insertQuery($query, [
			"orderId" => $orderId,
			"creationDate" => date("Y-m-d"),
			"performanceDate" => date("Y-m-d"),
			"amount" => 0,
		]);

		$invoice = new Invoice($invoiceId, $orderId);

		return $invoice;
	}

	public function getOrder(): Auftrag
	{
		return $this->auftrag;
	}

	public function getCustomer(): Kunde
	{
		return $this->kunde;
	}

	public function getPerformanceDate(): string
	{
		if ($this->performanceDate == null) {
			$this->performanceDate = new \DateTime();
			$this->performanceDate->setTimezone(new \DateTimeZone("Europe/Berlin"));
		}
		return $this->performanceDate->format("Y-m-d");
	}

	public function getCreationDate(): string
	{
		if ($this->creationDate == null) {
			$this->creationDate = new \DateTime();
			$this->creationDate->setTimezone(new \DateTimeZone("Europe/Berlin"));
		}
		return $this->creationDate->format("Y-m-d");
	}

	public function getCreationDateUnformatted(): \DateTime
	{
		if ($this->creationDate == null) {
			$this->creationDate = new \DateTime();
			$this->creationDate->setTimezone(new \DateTimeZone("Europe/Berlin"));
		}
		return $this->creationDate;
	}

	public function getId()
	{
		return $this->invoiceId;
	}

	public function getNumber()
	{
		return $this->invoiceNumber;
	}

	public function loadPostenFromAuftrag(): array
	{
		$orderId = $this->auftrag->getAuftragsnummer();
		$this->posten = Posten::getOrderItems($orderId, true, 1);
		return $this->posten;
	}

	public static function toggleText()
	{
		$invoiceId = (int) Tools::get("invoiceId");
		$textId = (int) Tools::get("textId");

		/* Adds default text if not already present */
		if ($textId == 0) {
			$query = "INSERT INTO invoice_text (id_invoice, `text`, active) VALUES (:invoiceId, :text, 1);";
			DBAccess::insertQuery($query, [
				"text" => Tools::get("text"),
				"invoiceId" => $invoiceId,
			]);
			JSONResponseHandler::sendResponse([
				"status" => "success",
				"id" => DBAccess::getLastInsertId(),
			]);
			return;
		}

		$query = "UPDATE invoice_text SET active = IF(active = 0, 1, 0) WHERE id = :textId AND id_invoice = :invoiceId";
		DBAccess::updateQuery($query, [
			"textId" => $textId,
			"invoiceId" => $invoiceId,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function addText()
	{
		$invoiceId = (int) Tools::get("invoiceId");
		$text = Tools::get("text");

		$query = "INSERT INTO invoice_text (id_invoice, `text`, active) VALUES (:invoiceId, :text, 1);";
		$id = DBAccess::insertQuery($query, [
			"text" => $text,
			"invoiceId" => $invoiceId,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
			"id" => $id,
		]);
	}

	public function getTexts()
	{
		$query = "SELECT * FROM invoice_text WHERE id_invoice = :invoiceId";
		$data = DBAccess::selectQuery($query, [
			"invoiceId" => $this->invoiceId,
		]);

		$defaultTexts = [
			"Zu den Bilddaten: Bei der Benutzung von Daten aus fremden Quellen richten sich die Nutzungsbedingungen über Verwendung und Weitergabe nach denen der jeweiligen Anbieter.",
			"Bitte beachten Sie, dass wir keine Haftung für eventuell entstehende Schäden übernehmen, die auf Witterungseinflüsse zurückzufüren sind (zerrisene Banner, herausgerissen Ösen o. Ä.). Sie als Kunde müssen entscheiden, wie die Banner konfektioniert werden sollen. Für die Art der Konfektionierung übernehmen wir keine Haftung. Wir übernehmen außerdem keine Haftung für unfachgerechte Montage der Banner.",
			"Pflegehinweise beachten: Keine Bleichmittel und Weichspüler verwenden. Nicht in den Trockner geben. Links gewendet waschen. Nicht über den Transfer bügeln. Nicht chemisch reinigen.",
			"Wir weisen darauf hin, dass Logos eventuell Bildrechte anderer berühren und wir hierfür keine Haftung übernehmen. Der Kunde garantiert uns Straffreiheit gegenüber einer eventuell geschädigten Partei im Fall einer Verletzung des Rechts des geistigen Eigentums und/ oder des Bildrechts und/ oder den durch eine solche Verletzung verursachten Schadens. Für einen eventuellen Fall solch einer Verletzung willigt der Kunde ein, uns in Höhe aller entstandenen Kosten (inkl. Anwaltkosten) zu entschädigen."
		];

		/* add default texts to texts if not already present */
		foreach ($defaultTexts as $text) {
			$found = false;
			foreach ($data as $d) {
				if ($d["text"] == $text) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$data[] = [
					"id" => 0,
					"id_invoice" => $this->invoiceId,
					"text" => $text,
					"active" => 0,
				];
			}
		}

		return $data;
	}

	public static function getContacts(int $customerId)
	{
		$contacts = DBAccess::selectQuery("SELECT Nummer AS id, Vorname AS firstName, Nachname AS lastName, Email AS email 
			FROM ansprechpartner 
			WHERE Kundennummer = :kdnr", [
			"kdnr" => $customerId,
		]);
		$formattedContacts = [];

		foreach ($contacts as $contact) {
			$formattedContacts[$contact["id"]] = $contact["firstName"] . " " . $contact["lastName"] . ", " . $contact["email"];
		}

		return $formattedContacts;
	}

	public static function setAddress()
	{
		$invoiceId = (int) Tools::get("invoiceId");
		$addressId = (int) Tools::get("addressId");
		$query = "UPDATE invoice SET address_id = :addressId WHERE id = :invoiceId;";
		DBAccess::updateQuery($query, [
			"addressId" => $addressId,
			"invoiceId" => $invoiceId,
		]);

		JSONResponseHandler::returnOK();
	}

	public static function setContact()
	{
		$invoiceId = (int) Tools::get("invoiceId");
		$contactId = (int) Tools::get("contactId");
		$query = "UPDATE invoice SET contact_id = :contactId WHERE id = :invoiceId;";
		DBAccess::updateQuery($query, [
			"contactId" => $contactId,
			"invoiceId" => $invoiceId,
		]);

		JSONResponseHandler::returnOK();
	}

	public static function setInvoiceDate()
	{
		$invoiceId = (int) Tools::get("invoiceId");
		$date = Tools::get("date");

		$query = "UPDATE invoice SET creation_date = :date WHERE id = :invoiceId";
		DBAccess::updateQuery($query, [
			"date" => $date,
			"invoiceId" => $invoiceId,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function setServiceDate()
	{
		$invoiceId = (int) Tools::get("invoiceId");
		$date = Tools::get("date");

		$query = "UPDATE invoice SET performance_date = :date WHERE id = :invoiceId";
		DBAccess::updateQuery($query, [
			"date" => $date,
			"invoiceId" => $invoiceId,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function completeInvoice()
	{
		$invoiceId = (int) Tools::get("invoiceId");
		$orderId = (int) Tools::get("orderId");
		$invoice = new Invoice($invoiceId, $orderId);

		$invoiceNumber = InvoiceNumberTracker::completeInvoice($invoice);

		$query = "UPDATE auftrag SET Rechnungsnummer = :invoiceId WHERE Auftragsnummer = :orderId";
		DBAccess::updateQuery($query, [
			"invoiceId" => $invoiceId,
			"orderId" => $orderId,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
			"number" => $invoiceNumber,
			"id" => $invoiceId,
		]);
	}

	/**
	 * berechnet die offene Rechnungssumme, Rechnung ist offen, wenn auftrag.Bezahlt = 0 gilt;
	 * 
	 * TODO: für später:
	 * eventuell eigene Tabelle für Rechnungssummen, um Unveränderbarkeit zu garantieren
	 */
	public static function getOffeneRechnungssumme()
	{
		$query = "SELECT ROUND(SUM(all_posten.price), 2) AS summe
		FROM (SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price, posten.Auftragsnummer as id FROM zeit, posten WHERE zeit.Postennummer = posten.Postennummer
			  UNION ALL
			  SELECT leistung_posten.SpeziefischerPreis AS price, posten.Auftragsnummer as id FROM leistung_posten, posten WHERE leistung_posten.Postennummer = posten.Postennummer) all_posten, auftrag
			  WHERE auftrag.Auftragsnummer = all_posten.id AND auftrag.Rechnungsnummer != 0 AND auftrag.Bezahlt = 0";
		$summe = DBAccess::selectQuery($query)[0]['summe'];
		return $summe;
	}

	public static function getOpenInvoiceData()
	{
		$data = DBAccess::selectQuery("SELECT auftrag.Auftragsnummer AS Nummer,
			auftrag.Rechnungsnummer,
			auftrag.Auftragsbezeichnung AS Bezeichnung, 
			auftrag.Auftragsbeschreibung AS Beschreibung, 
			auftrag.Kundennummer,
			DATE_FORMAT(auftrag.Datum, '%d.%m.%Y') as Datum,
			kunde.Firmenname,
			CONCAT(FORMAT(auftragssumme.orderPrice, 2, 'de_DE'), ' €') AS Summe 
			FROM auftrag, auftragssumme, kunde 
			WHERE auftrag.Kundennummer = kunde.Kundennummer 
				AND Rechnungsnummer != 0 
				AND auftrag.Bezahlt = 0 
				AND auftrag.Auftragsnummer = auftragssumme.id");

		JSONResponseHandler::sendResponse([
			"data" => $data,
		]);
	}

	public static function setInvoicePaid()
	{
		$invoiceId = Tools::get("invoiceId");
		$query = "UPDATE auftrag SET Bezahlt = 1 
			WHERE Rechnungsnummer = :invoice";

		DBAccess::updateQuery($query, [
			"invoice" => $invoiceId,
		]);

		if (Tools::get("date") && Tools::get("paymentType")) {
			DBAccess::updateQuery("UPDATE invoice SET payment_date = :paymentDate, payment_type = :paymentType WHERE id = :invoice", [
				"paymentDate" => Tools::get("date"),
				"paymentType" => Tools::get("paymentType"),
				"invoice" => $invoiceId,
			]);
		}

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function getPDF()
	{
		$invoiceId = (int) Tools::get("invoiceId");
		$orderId = (int) Tools::get("orderId");
		$invoice = new InvoicePDF($invoiceId, $orderId);
		$invoice->generate();
	}
}
