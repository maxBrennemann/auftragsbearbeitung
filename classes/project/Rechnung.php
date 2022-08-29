<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('vendor/autoload.php');
require_once('classes/DBAccess.php');
require_once('classes/project/Auftrag.php');
require_once('classes/project/RechnungsPDF.php');
require_once('classes/project/EmptyPosten.php');

class Rechnung {

	private $kunde;
	private $address = 0;
	private $auftrag;

	private $tempId = 0;

	private $posten;
	private $texts = array();

	private $date = "00.00.0000";
	private $performanceDate = "00.00.0000";

	function __construct($invoiceId = null) {
		/* 
		 * session object currentInvoice_orderId stores the currently used invoice id;
		 * if no invoice id exists, a new invoice id is created
		 */
		$orderId = -1;
		if (isset($_SESSION['currentInvoice_orderId'])) {
			$orderId = $_SESSION['currentInvoice_orderId'];
		} else if ($invoiceId != null) {
			$order =  DBAccess::selectQuery("SELECT Auftragsnummer FROM auftrag WHERE Rechnungsnummer = " . (int) $invoiceId);
			if (!empty($order)) {
				$orderId = (int) $order[0]["Auftragsnummer"];
			}
 		}

		$this->auftrag = new Auftrag($orderId);
		$this->kunde = new Kunde($this->auftrag->getKundennummer());

		$this->invoiceId = (int) $invoiceId;
	}

	/*
	 * creates and stores a pdf if parameter is set to true
	*/
    public function PDFgenerieren($store = false) {
        $pdf = new RechnungsPDF('p', 'mm', 'A4');

		/* header and footer */
        $pdf->setPrintHeader(false);
        //$pdf->setPrintFooter(false);

        $pdf->SetTitle('Rechnung für ' . $this->kunde->getFirmenname() . " " . $this->kunde->getName());
        $pdf->SetSubject('Angebot');
        $pdf->SetKeywords('pdf, angebot');
		//$pdf->SetAutoPageBreak(true, 35);

		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->AddPage();

        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins(0, 0, 0, 0);
		$pdf->setMargins(25, 45, 20, true);

		$pdf->SetFont("helvetica", "", 8);
		$address = "<p>b-schriftung Brennemann Dietmar, Huberweg 31, 94522 Wallersdorf</p>";
		$pdf->writeHTMLCell(0, 40, 25, 25, $address);

		$pdf->SetFont("helvetica", "", 12);
        $cAddress = $this->fillAddress();
        $pdf->writeHTMLCell(85, 40, 25, 25, $cAddress);

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
		if ($this->posten != null) {
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
		}

		/* 297: Din A4 Seitenhöhe, 25: Abstand von unten für die Fußzeile, 55: bezieht sich auf die Zwischensumme und Rechnungssumme, damit diese immer auf einer Seite stehen */
		if ($pdf->GetY() + 55 >= 297 - 25) {
			$pdf->AddPage();
		}

		/* Code für Zwischensumme und Rechnungssumme */
		$summe = $this->auftrag->preisBerechnen();
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

		/* store performance and creation dates */
		$this->storeDates();

		/* Speicherung (aktuell nur Windows) */
		if ($store == true) {
			$filename= "{$this->kunde->getKundennummer()}_{$this->getInvoiceId()}.pdf"; 
            $filelocation = 'c:/xampp/htdocs/auftragsbearbeitung/files/generated/invoice/';
            $fileNL = $filelocation . $filename;
			echo WEB_URL . "/files/generated/invoice/" . $filename;
			self::addAllPosten($_SESSION['currentInvoice_orderId']);
			$pdf->Output($fileNL, 'F');
		} else {
			$_SESSION['tempInvoice'] = serialize($this);
			$pdf->Output();
		}
	}

	/**
	 * fügt einen Tabellenkopf und die allgemeinen Rechnungsdaten ein;
	 * @param TCPDF &$pdf Refernz auf PDF Variable
	 * @param int $y Abstand zu oben, Standard ist 45, damit die erste Seite richtig generiert wird
	 */
	private function addTableHeader(&$pdf, $y = 45) {
		$pdf->SetFont("helvetica", "", 12);
		$pdf->setXY(120, $y);
		$pdf->Cell(30, 10, "Rechnungs-Nr:");
		$pdf->Cell(30, 10, $this->getInvoiceId(), 0, 0, 'R');
		$pdf->setXY(120, $y + 6);
		$pdf->Cell(30, 10, "Auftrags-Nr:");
		$pdf->Cell(30, 10, $this->auftrag->getAuftragsnummer(), 0, 0, 'R');
		$pdf->setXY(120, $y + 12);
		$pdf->Cell(30, 10, "Datum:");
		$pdf->Cell(30, 10, $this->getDate(), 0, 0, 'R');
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

	/*
	 * returns the invoice id by filtering for the order id
	*/
	private function getInvoiceId() {
		$orderId = $this->auftrag->getAuftragsnummer();
		$invoiceId = (int) DBAccess::selectQuery("SELECT Rechnungsnummer FROM auftrag WHERE Auftragsnummer = $orderId")[0]['Rechnungsnummer'];
		if ($invoiceId == 0 || $invoiceId == null) {
			$next =  self::getNextNumber();
			$this->tempId = $next;
			return $next;
		}
		return $invoiceId;
	}
	
	public function getOrderId() {
		return $this->auftrag->getAuftragsnummer();
	}

	public function setDate($date) {
		if (DateTime::createFromFormat('d.m.Y', $date) !== false) {
			$this->date = $date;
		}
	}

	private function getDate() {
		if ($this->date == null || $this->date == "00.00.0000")
			return date("d.m.Y");
		return $this->date;
	}
	
	public function loadPostenFromAuftrag() {
		$orderId = $this->auftrag->getAuftragsnummer();
		$this->posten = Posten::bekommeAllePosten($orderId, true);
		$this->posten = array_merge($this->posten, $this->texts);
	}

	public function addText($id, $text) {
		$empty = new EmptyPosten($id, $text);
		array_push($this->posten, $empty);
		$this->texts[$id] = $empty;
	}

	/* Code ist hier nicht nachvollziehbar.
	 * ich weiß nicht, wieso es nicht geht. Deswegen wird
	 * das Leistungsdatum als "addText" hinzugefügt.
	 * Es ist überschreibbar, wieso auch immer
	 */
	public function setDatePerformance($date) {
		$this->performanceDate = $date;
		$this->addText(-20, "Leistungsdatum: " . $date);
		return null;

		/* performance Date Key is -20 hardcoded */
		$dateLine = new EmptyPosten(-20, "Leistungsdatum: " . $date);

		$id = -20;
		$result = 0;
		foreach ($this->posten as $key => $p) {
			if (isset($p->id) && $p->id == $id) {
				$result = $key;
				break;
			}
		}

		if ($result == 0) {
			array_push($this->posten, $dateLine);
		} else {
			$this->posten[$result] = $dateLine;
		}
	}

	public function removeText($id) {
		$result = 0;
		foreach ($this->posten as $key => $p) {
			if (isset($p->id) && $p->id == $id) {
				$result = $key;
				break;
			}
		}

		unset($this->posten[$result]);
		unset($this->texts[$id]);
	}

	public function getTempInvoiceId() {
		return $this->tempId;
	}

	private function fillAddress() {
		$firma = $this->kunde->getFirmenname();
		$name = $this->kunde->getName();
		$hausnr = $this->kunde->getHausnummer($this->address);
		$strasse = $this->kunde->getStrasse($this->address);
		$plz = $this->kunde->getPostleitzahl($this->address);
		$ort = $this->kunde->getOrt($this->address);
		return "<p>$firma<br>$name<br>$strasse $hausnr<br>$plz $ort</p>";
	}
	
	public function setAddress($address) {
		if (Address::hasAddress($this->kunde->getKundennummer(), $address))
			$this->address = $address;
	}

	public function setInvoiceDAte($date) {

	}

	public function setLeistungsDAte($date) {

	}

	private function ohneBerechnungBtn(&$pdf, &$height, &$lineheight, &$p) {
		$pdf->MultiCell(50, $lineheight, $p->getDescription(), '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
		$addToOffset = ceil($height);
		
		$pdf->SetFont("helvetica", "", 7);
		$pdf->MultiCell(20, $lineheight, "Ohne Berechnung", '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
		$pdf->SetFont("helvetica", "", 12);

		return $addToOffset;
	}

	public static function getNextNumber() {
		$number = DBAccess::selectQuery("SELECT MAX(Rechnungsnummer) FROM auftrag")[0]['MAX(Rechnungsnummer)'];
		$number = (int) $number;
		$number++;
		return $number;
	}

	public static function getOffeneRechnungen() {
		$column_names = array(
			0 => array("COLUMN_NAME" => "Auftragsnr"),
			1 => array("COLUMN_NAME" => "Rechnungsnummer"),
			2 => array("COLUMN_NAME" => "Firmenname"),
			3 => array("COLUMN_NAME" => "Bezeichnung"),
			4 => array("COLUMN_NAME" => "Summe"),
			5 => array("COLUMN_NAME" => "Datum"));
		
		$data = DBAccess::selectQuery("SELECT auftrag.Auftragsnummer as Auftragsnr, auftrag.Auftragsbezeichnung as Bezeichnung, auftrag.Auftragsbeschreibung as Beschreibung, auftrag.AngenommenDurch, auftrag.Kundennummer, auftrag.Datum, auftrag.Termin, auftrag.Rechnungsnummer, kunde.Firmenname, CONCAT(FORMAT(auftragssumme.orderPrice, 2, 'de_DE'), ' €') AS Summe FROM auftrag, auftragssumme, kunde WHERE auftrag.Kundennummer = kunde.Kundennummer AND Rechnungsnummer != 0 AND auftrag.Bezahlt = 0 AND auftrag.Auftragsnummer = auftragssumme.id");

		for ($i = 0; $i < sizeof($data); $i++) {
			$id = $data[$i]["AngenommenDurch"];
			$angenommenDurch = DBAccess::selectQuery("SELECT Vorname, Nachname FROM mitarbeiter WHERE id = $id");
			$data[$i]["AngenommenDurch"] = $angenommenDurch[0]["Vorname"] . " " . $angenommenDurch[0]["Nachname"];
		}

		$table = new Table();
		$table->createByData($data, $column_names);
		$table->addActionButton("update", $identifier = "Auftragsnr", $update = "istErledigt = 0");
		
		return $table->getTable();
	}

	private function storeDates() {
		$orderId = $this->auftrag->getAuftragsnummer();
		$creationDate = DateTime::createFromFormat("d.m.Y", $this->getDate())->format("Y-m-d");
		$performanceDate = DateTime::createFromFormat("d.m.Y", $this->performanceDate)->format("Y-m-d");
		$payment_date = "0000-00-00";
		$payment_type = -1;
		$amount = (int) $this->auftrag->preisBerechnen() * 100;

		DBAccess::deleteQuery("DELETE FROM invoice WHERE order_id = $orderId");

		$query = "INSERT INTO invoice (order_id, creation_date, performance_date, payment_date, payment_type, amount) VALUES ($orderId, '$creationDate', '$performanceDate', '$payment_date', $payment_type, '$amount')";
		DBAccess::insertQuery($query);
	}

	/**
	 * berechnet die offene Rechnungssumme, Rechnung ist offen, wenn auftrag.Bezahlt = 0 gilt;
	 * 
	 * TODO für später:
	 * eventuell eigene Tabelle für Rechnungssummen, um Unveränderbarkeit zu garantieren
	 */
	public static function getOffeneRechnungssumme() {
		$query = "SELECT ROUND(SUM(all_posten.price), 2) AS summe
		FROM (SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price, posten.Auftragsnummer as id FROM zeit, posten WHERE zeit.Postennummer = posten.Postennummer
			  UNION ALL
			  SELECT leistung_posten.SpeziefischerPreis AS price, posten.Auftragsnummer as id FROM leistung_posten, posten WHERE leistung_posten.Postennummer = posten.Postennummer) all_posten, auftrag
			  WHERE auftrag.Auftragsnummer = all_posten.id AND auftrag.Rechnungsnummer != 0 AND auftrag.Bezahlt = 0";
		$summe = DBAccess::selectQuery($query)[0]['summe'];
		return $summe;
	}

	/*
	 * adds an invoice id to all posten for a specific order
	 * sql query only works if no invoice id has ever been added to that
	*/
	public static function addAllPosten($orderId) {
		$auftrag = new Auftrag($orderId);
		
		$nextNumber = Rechnung::getNextNumber();
		DBAccess::updateQuery("UPDATE auftrag SET Rechnungsnummer = $nextNumber WHERE Auftragsnummer = $orderId AND Rechnungsnummer = 0");
		DBAccess::updateQuery("UPDATE posten SET rechnungsNr = $nextNumber WHERE Auftragsnummer = $orderId AND isInvoice = 1");

		/* Fertigstellung wird eingetragen */
		DBAccess::updateQuery("UPDATE auftrag SET Fertigstellung = current_date() WHERE Auftragsnummer = $orderId");
	}

}

?>