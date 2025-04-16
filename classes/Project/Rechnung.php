<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Rechnung
{

	private Kunde $kunde;
	private Auftrag $auftrag;
	private int $address = 0;

	private int $invoiceId = 0;
	private int $invoiceNumber = 0;
	private array $posten = [];
	private $texts = [];

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

		$this->invoiceNumber = $data[0]["invoice_number"];
		$this->creationDate = new \DateTime($data[0]["creation_date"]);
		$this->performanceDate = new \DateTime($data[0]["performance_date"]);
		$this->getTexts();
	}

	public static function create(int $orderId): Rechnung
	{
		$query = "SELECT id FROM invoice WHERE order_id = :orderId;";
		$data = DBAccess::selectQuery($query, [
			"orderId" => $orderId,
		]);

		if (!empty($data)) {
			$invoiceId = $data[0]["id"];
			return new Rechnung($invoiceId, $orderId);
		}

		$query = "INSERT INTO invoice (invoice_number, order_id, creation_date, performance_date, amount) VALUES (0, :orderId, :creationDate, :performanceDate, :amount)";
		$invoiceId = DBAccess::insertQuery($query, [
			"orderId" => $orderId,
			"creationDate" => date("Y-m-d"),
			"performanceDate" => date("Y-m-d"),
			"amount" => 0,
		]);

		$invoice = new Rechnung($invoiceId, $orderId);

		return $invoice;
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

	public function getId()
	{
		return $this->invoiceId;
	}

	public function getNumber()
	{
		return $this->invoiceNumber;
	}

	public function PDFgenerieren($store = false)
	{
		$pdf = new RechnungsPDF('p', 'mm', 'A4');

		/* header and footer */
		$pdf->setPrintHeader(false);
		//$pdf->setPrintFooter(false);

		$pdf->SetTitle('Rechnung für ' . $this->kunde->getFirmenname() . " " . $this->kunde->getName());
		$pdf->SetSubject('Angebot');
		$pdf->SetKeywords('pdf, angebot');
		//$pdf->SetAutoPageBreak(true, 35);

		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		$pdf->AddPage();

		$pdf->setCellPaddings(1, 1, 1, 1);
		$pdf->setCellMargins(0, 0, 0, 0);
		$pdf->setMargins(25, 45, 20, true);

		$pdf->SetFont("helvetica", "", 8);
		$address = "<p>" . $_ENV["COMPANY_IMPRINT"] . "</p>";
		$pdf->writeHTMLCell(0, 10, 25, 20, $address);

		$pdf->SetFont("helvetica", "", 12);
		$this->fillAddress($pdf);

		$pdf->Image("files/res/image/b-schriftung_logo.jpg", 120, 22, 60);

		$pdf->setXY(120, 30);
		$pdf->setFontStretching(200);
		$pdf->SetFont("helvetica", "B", 17);
		$pdf->Cell(60, 20, "RECHNUNG", 0, 0, 'L', 0, '', 2);
		$pdf->setFontStretching();

		$this->addTableHeader($pdf);

		/* iterates over all posten and adds lines */
		$lineheight = 10;
		$this->loadPostenFromAuftrag();
		$offset = 100;
		$pdf->setXY(25, $offset);
		$count = 1;

		foreach ($this->posten as $p) {
			$pdf->Cell(10, $lineheight, $count);
			$pdf->Cell(20, $lineheight, $p->getQuantity());
			$pdf->Cell(20, $lineheight, $p->getEinheit());

			$height = $pdf->getStringHeight(70, $p->getDescription());
			$addToOffset = $lineheight;

			if ($p->getOhneBerechnung() == true) {
				$addToOffset = $this->ohneBerechnungBtn($pdf, $height, $lineheight, $p);
			} else {
				if ($height >= $lineheight) {
					$pdf->MultiCell(70, $lineheight, $p->getDescription(), '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
					$addToOffset = ceil($height);
				} else {
					$pdf->Cell(70, $lineheight, $p->getDescription());
				}
			}

			$pdf->Cell(20, $lineheight, $p->bekommeEinzelPreis_formatted());
			$pdf->Cell(20, $lineheight, $p->bekommePreis_formatted(), 0, 0, 'R');

			$offset += $addToOffset;
			$pdf->ln($addToOffset);

			/* 297: Din A4 Seitenhöhe, 25: Abstand von unten für die Fußzeile */
			if ($pdf->GetY() + $addToOffset >= 297 - 25) {
				$pdf->AddPage();
				$this->addTableHeader($pdf, 25);
				$pdf->ln(10);
			}

			$count++;
		}

		foreach ($this->texts as $text) {
			if ($text["active"] == "0") {
				continue;
			}

			$pdf->Cell(50, $lineheight, "");

			$heigth = $pdf->getStringHeight(70, $text["text"]);
			$addToOffset = $lineheight;

			if ($heigth >= $lineheight) {
				$pdf->MultiCell(70, $lineheight, $text["text"], '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
				$addToOffset = ceil($heigth);
			} else {
				$pdf->Cell(70, $lineheight, $text["text"]);
			}
			$pdf->Cell(40, $lineheight, "");
			$offset += $addToOffset;
			$pdf->ln($addToOffset);

			/* 297: Din A4 Seitenhöhe, 25: Abstand von unten für die Fußzeile */
			if ($pdf->GetY() + $addToOffset >= 297 - 25) {
				$pdf->AddPage();
				$this->addTableHeader($pdf, 25);
				$pdf->ln(10);
			}

			$count++;
		}

		/* 297: Din A4 Seitenhöhe, 25: Abstand von unten für die Fußzeile, 55: bezieht sich auf die Zwischensumme und Rechnungssumme, damit diese immer auf einer Seite stehen */
		if ($pdf->GetY() + 55 >= 297 - 25) {
			$pdf->AddPage();
		}

		/* Code für Zwischensumme und Rechnungssumme */
		$summe = $this->auftrag->calcOrderSum();
		$zwischensumme = number_format($summe, 2, ',', '') . ' €';
		$mwst = number_format($summe * 0.19, 2, ',', '') . ' €';
		$rechnungssumme = number_format($summe * 1.19, 2, ',', '') . ' €';

		$pdf->ln();
		$pdf->Cell(80, 10, "");
		$pdf->SetFont("helvetica", "B", 12);
		$pdf->Cell(60, 10, 'Zwischensumme:', 'T');
		$pdf->SetFont("helvetica", "", 12);
		$pdf->Cell(20, 10, $zwischensumme, 'T', 0, 'R');

		$pdf->ln();
		$pdf->Cell(80, 10, "");
		$pdf->SetFont("helvetica", "B", 12);
		$pdf->Cell(60, 10, '19% MwSt.:', 'B');
		$pdf->SetFont("helvetica", "", 12);
		$pdf->Cell(20, 10, $mwst, 'B', 0, 'R');

		$pdf->ln();
		$pdf->Cell(80, 10, "");
		$pdf->SetFont("helvetica", "B", 12);
		$pdf->Cell(60, 10, 'Rechnungssumme:', 'B');
		$pdf->SetFont("helvetica", "", 12);
		$pdf->Cell(20, 10, $rechnungssumme, 'B', 0, 'R');

		$pdf->ln();
		$pdf->setCellMargins(0, 1, 0, 0);
		$pdf->Cell(80, 10, "");
		$pdf->Cell(60, 10, '', 'T');
		$pdf->Cell(20, 10, '', 'T');

		/* Code für "Zahlbar sofort ohne weitere Abzüge" */
		$pdf->ln();
		$pdf->setCellMargins(0, 0, 0, 0);
		$pdf->SetFont("helvetica", "", 10);
		$pdf->Cell(160, 10, "Zahlbar sofort ohne weitere Abzüge");

		$pdf->Output();
	}

	/**
	 * fügt einen Tabellenkopf und die allgemeinen Rechnungsdaten ein;
	 * @param \TCPDF &$pdf Refernz auf PDF Variable
	 * @param int $y Abstand zu oben, Standard ist 45, damit die erste Seite richtig generiert wird
	 */
	private function addTableHeader(&$pdf, $y = 45)
	{
		$pdf->SetFont("helvetica", "", 12);
		$pdf->setXY(120, $y);
		$pdf->Cell(30, 10, "Rechnungs-Nr:");
		$pdf->Cell(30, 10, $this->getNumber(), 0, 0, 'R');
		$pdf->setXY(120, $y + 6);
		$pdf->Cell(30, 10, "Auftrags-Nr:");
		$pdf->Cell(30, 10, $this->auftrag->getAuftragsnummer(), 0, 0, 'R');
		$pdf->setXY(120, $y + 12);
		$pdf->Cell(30, 10, "Datum:");
		$pdf->Cell(30, 10, $this->creationDate->format("d.m.Y"), 0, 0, 'R');
		$pdf->setXY(120, $y + 18);
		$pdf->Cell(30, 10, "Kunden-Nr.:");
		$pdf->Cell(30, 10, $this->kunde->getKundennummer(), 0, 0, 'R');
		$pdf->setXY(120, $y + 24);
		$pdf->Cell(30, 10, "Seite:");
		$pdf->Cell(30, 10, $pdf->getAliasRightShift() . $pdf->PageNo() . ' von ' . $pdf->getAliasNbPages(), 0, 0, 'R');

		$pdf->setXY(25, $y + 45);
		$pdf->SetFont("helvetica", "B", 12);
		$pdf->Cell(10, 10, 'Pos', 'B');
		$pdf->Cell(20, 10, 'Menge', 'B');
		$pdf->Cell(20, 10, 'MEH', 'B');
		$pdf->Cell(70, 10, 'Bezeichnung', 'B');
		$pdf->Cell(20, 10, 'E-Preis', 'B');
		$pdf->Cell(20, 10, 'G-Preis', 'B');
		$pdf->SetFont("helvetica", "", 12);
	}

	public function loadPostenFromAuftrag()
	{
		$orderId = $this->auftrag->getAuftragsnummer();
		$this->posten = Posten::getOrderItems($orderId, true);
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

		$this->texts = $data;
		return $data;
	}

	private function fillAddress(&$pdf)
	{
		$lineheight = 10;
		$pdf->setXY(25, 25);

		$firma = $this->kunde->getFirmenname();
		$name = $this->kunde->getName();
		$hausnr = $this->kunde->getHausnummer($this->address);
		$strasse = $this->kunde->getStrasse($this->address);
		$plz = $this->kunde->getPostleitzahl($this->address);
		$ort = $this->kunde->getOrt($this->address);

		if ($firma != null || $firma != "") {
			$pdf->Cell(85, $lineheight, $firma);
			$pdf->ln(5);
		}
		if ($this->kunde->getNachname() != "" && $this->kunde->getVorname() != "") {
			$pdf->Cell(85, $lineheight, $name);
			$pdf->ln(5);
		}
		$pdf->Cell(85, $lineheight, $strasse . " " . $hausnr);
		$pdf->ln(5);
		$pdf->Cell(85, $lineheight, $plz . " " . $ort);
	}

	public function setAddress($address)
	{
		if (Address::hasAddress($this->kunde->getKundennummer(), $address)) {
			$this->address = $address;
		}
	}

	private function ohneBerechnungBtn(&$pdf, &$height, &$lineheight, &$p)
	{
		$pdf->MultiCell(50, $lineheight, $p->getDescription(), '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
		$addToOffset = ceil($height);

		$pdf->SetFont("helvetica", "", 7);
		$pdf->MultiCell(20, $lineheight, "Ohne Berechnung", '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
		$pdf->SetFont("helvetica", "", 12);

		return $addToOffset;
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
		$invoice = new Rechnung($invoiceId, $orderId);

		$invoiceNumber = InvoiceNumberTracker::completeInvoice($invoice);

		$query = "UPDATE invoice SET invoice_number = :invoiceNumber WHERE id = :id";
		DBAccess::updateQuery($query, [
			"invoiceNumber" => $invoiceNumber,
			"id" => $invoice->getId(),
		]);

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
			auftrag.Datum,
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

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}
}
