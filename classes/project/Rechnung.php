<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('vendor/autoload.php');
require_once('classes/DBAccess.php');
require_once('classes/project/Auftrag.php');
require_once('classes/project/InteractiveFormGenerator.php');
require_once('classes/project/RechnungsPDF.php');

class Rechnung {

	private $summeMwSt = 0;
	private $summe = 0;

	private $invoiceId;

	private $kunde;
	private $auftrag;

	private $posten;

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

		/*$orderId = isset($_SESSION['currentInvoice_orderId']) ? (int) $_SESSION['currentInvoice_orderId'] : DBAccess::selectQuery("SELECT * FROM auftrag WHERE Rechnungsnummer = " . (int) $invoiceId)[0]["Auftragsnummer"];*/

		$this->auftrag = new Auftrag($orderId);
		$this->kunde = new Kunde($this->auftrag->getKundennummer());

		$this->invoiceId = (int) $invoiceId;
	}

	public function preisBerechnen() {
		$nr = $this->invoiceId;
		/* die SQL Query nimmt alle Posten, die unter dieser Rechnungsnummer gespeichert sind. 
		 * Aktuell werden die einzelnen Zahlen dann hier in PHP zusammengerechnet, 
		 * was aber auch anders möglich ist (siehe views auftragssumme etc.), 
		 * jedoch hier aus Zeitgründen nicht gemacht wurde 
		 */
		$query = "SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price FROM zeit, posten WHERE posten.rechnungsNr = $nr AND posten.Postennummer = zeit.postennummer UNION ALL SELECT leistung_posten.SpeziefischerPreis * leistung_posten.qty AS price FROM leistung_posten, posten WHERE posten.rechnungsNr = $nr AND posten.Postennummer = leistung_posten.postennummer UNION ALL SELECT (product_compact.price * product_compact.amount) AS price FROM product_compact, posten WHERE posten.rechnungsNr = $nr AND posten.Postennummer = product_compact.postennummer;
		";

		$posten = DBAccess::selectQuery($query);
		$amount = 0.0;
		foreach ($posten as $p) {
			$amount += (float) $p;
		}

		return $amount;
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

		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->AddPage();

        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins(0, 0, 0, 0);

		$pdf->SetFont("helvetica", "", 8);
		$address = "<p>b-schriftung Brennemann ***REMOVED***, ***REMOVED***, ***REMOVED***</p>";
		$pdf->writeHTMLCell(0, 40, 20, 25, $address);

		$pdf->SetFont("helvetica", "", 12);
        $cAddress = "<p>{$this->kunde->getFirmenname()}<br>{$this->kunde->getName()}<br>{$this->kunde->getStrasse()} {$this->kunde->getHausnummer()}<br>{$this->kunde->getPostleitzahl()} {$this->kunde->getOrt()}</p>";
        $pdf->writeHTMLCell(85, 40, 20, 25, $cAddress);

		$info = "";
		$pdf->writeHTMLCell(85, 40, 120, 35, $address);

        $pdf->setXY(20, 90);
		$pdf->SetFont("helvetica", "B", 12);
        $pdf->Cell(20, 10, 'Menge', 'B');
        $pdf->Cell(20, 10, 'MEH', 'B');
        $pdf->Cell(80, 10, 'Bezeichnung', 'B');
        $pdf->Cell(20, 10, 'E-Preis', 'B');
		$pdf->Cell(20, 10, 'G-Preis', 'B');
		$pdf->SetFont("helvetica", "", 12);
		
		/* iterates over all posten and adds lines */
		$this->loadPostenFromAuftrag();
		$offset = 10;
		if ($this->posten != null) {
			foreach ($this->posten as $p) {
				$pdf->setXY(20, 90 + $offset);
				$pdf->Cell(20, 10, $p->getQuantity());
				$pdf->Cell(20, 10, $p->getEinheit());
				$pdf->Cell(80, 10, $p->getDescription());
				$pdf->Cell(20, 10, number_format($p->bekommeEinzelPreis(), 2, ',', '') . ' €');
				$pdf->Cell(20, 10, number_format($p->bekommePreis(), 2, ',', '') . ' €', 0, 0, 'R');
				$offset += 10;
			}
		}

		/* Code für Zwischensumme und Rechnungssumme */
		$summe = $this->auftrag->preisBerechnen();
		$zwischensumme = number_format($summe, 2, ',', '') . ' €';
		$mwst = number_format($summe * 0.19, 2, ',', '') . ' €';
		$rechnungssumme = number_format($summe * 1.19, 2, ',', '') . ' €';

        $pdf->setXY(20, 90 + $offset);
		$pdf->Cell(80, 10, "");
		$pdf->SetFont("helvetica", "B", 12);
		$pdf->Cell(60, 10, 'Zwischensumme:', 'T');
		$pdf->SetFont("helvetica", "", 12);
		$pdf->Cell(20, 10, $zwischensumme, 'T', 0, 'R');

		$offset += 10;
		$pdf->setXY(20, 90 + $offset);
		$pdf->Cell(80, 10, "");
		$pdf->SetFont("helvetica", "B", 12);
		$pdf->Cell(60, 10, '19% MwSt.:', 'B');
		$pdf->SetFont("helvetica", "", 12);
		$pdf->Cell(20, 10, $mwst, 'B', 0, 'R');

		$offset += 10;
		$pdf->setXY(20, 90 + $offset);
		$pdf->Cell(80, 10, "");
		$pdf->SetFont("helvetica", "B", 12);
		$pdf->Cell(60, 10, 'Rechnungssumme:', 'B');
		$pdf->SetFont("helvetica", "", 12);
		$pdf->Cell(20, 10, $rechnungssumme, 'B', 0, 'R');

		$offset += 10;
		$pdf->setCellMargins(0, 1, 0, 0);
		$pdf->setXY(20, 90 + $offset);
		$pdf->Cell(80, 10, "");
		$pdf->Cell(60, 10, '', 'T');
		$pdf->Cell(20, 10, '', 'T');

		/* Code für "Zahlungsziel 8 Tage" */

		/* Speicherung */
		if ($store == true) {
            $filename= "{$this->kunde->getKundennummer()}_{$this->getInvoiceId()}.pdf"; 
            $filelocation = "files\\generated\\invoice";
            $fileNL = $filelocation . "\\" . $filename;
			$pdf->Output($fileNL, 'F');
		} else {
			$pdf->Output();
		}
	}

	/*
	 * returns the invoice id by filtering for the order id
	*/
	private function getInvoiceId() {
		$orderId = $this->auftrag->getAuftragsnummer();
		return DBAccess::selectQuery("SELECT Rechnungsnummer FROM auftrag WHERE Auftragsnummer = $orderId")[0]['Rechnungsnummer'];
	}
	
	private function loadPostenFromAuftrag() {
		$orderId = $this->auftrag->getAuftragsnummer();
		$this->posten = Posten::bekommeAllePosten($orderId, true);
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
		
		$data = DBAccess::selectQuery("SELECT auftrag.Auftragsnummer as Auftragsnr, auftrag.Auftragsbezeichnung as Bezeichnung, auftrag.Auftragsbeschreibung as Beschreibung, auftrag.AngenommenDurch, auftrag.Kundennummer, auftrag.Datum, auftrag.Termin, auftrag.Rechnungsnummer, kunde.Firmenname, CONCAT(FORMAT(auftragssumme.orderPrice, 2, 'de_DE'), ' €') AS Summe FROM auftrag, auftragssumme, kunde WHERE auftrag.Kundennummer = kunde.Kundennummer AND Rechnungsnummer != 0 AND Bezahlt = 0 AND auftrag.Auftragsnummer = auftragssumme.id");

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

	public static function getOffeneRechnungssumme() {
		$query = "SELECT ROUND(SUM(all_posten.price), 2) AS summe
		FROM (SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price, posten.Auftragsnummer as id FROM zeit, posten WHERE zeit.Postennummer = posten.Postennummer
			  UNION ALL
			  SELECT leistung_posten.SpeziefischerPreis AS price, posten.Auftragsnummer as id FROM leistung_posten, posten WHERE leistung_posten.Postennummer = posten.Postennummer) all_posten, auftrag
			  WHERE auftrag.Auftragsnummer = all_posten.id AND auftrag.Rechnungsnummer != 0";
		$summe = DBAccess::selectQuery($query)[0]['summe'];
		return $summe;
	}

	/*
	 * adds an invoice id to all posten for a specific order
	 * sql query only works if no invoice id has ever been added to that
	*/
	public static function addAllPosten($orderId) {
		$auftrag = new Auftrag($orderId);
		$posten = $auftrag->getAuftragspostenData();

		
		$nextNumber = Rechnung::getNextNumber();
		echo $nextNumber;
		DBAccess::updateQuery("UPDATE auftrag SET Rechnungsnummer = $nextNumber WHERE Auftragsnummer = $orderId AND Rechnungsnummer = 0");
		DBAccess::updateQuery("UPDATE posten SET rechnungsNr = $nextNumber WHERE Auftragsnummer = $orderId AND rechnungsNr = 0");

		/* Fertigstellung wird eingetragen */
		DBAccess::updateQuery("UPDATE auftrag SET Fertigstellung = current_date() WHERE Auftragsnummer = $orderId");
	}

	/*
	 * adds an invoice id to specific posten for a specific order
	 * sql query only works if no invoice id has ever been added to that
	*/
	public static function addPosten($orderId, $postenIds) {
		$nextNumber = Rechnung::getNextNumber();
		$query = "UPDATE posten SET rechnungsNr = $nextNumber WHERE ";
		foreach($postenIds as $id) {
			$query .= "Postennummer = $id AND ";
		}
		$query = substr($query, 0, -4);
	}

}

?>