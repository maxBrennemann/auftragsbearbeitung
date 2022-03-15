<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('classes/DBAccess.php');
require_once('classes/project/Auftrag.php');
require_once('classes/project/InteractiveFormGenerator.php');

class Rechnung {

	private $summeMwSt = 0;
	private $summe = 0;

	private $kunde;
	private $auftrag;

	private $posten;

	function __construct() {
		if (isset($_SESSION['currentInvoice_orderId'])) {
			$currentOrder = $_SESSION['currentInvoice_orderId'];
			$this->auftrag = new Auftrag($currentOrder);
			$kdnr = $this->auftrag->getKundennummer();
			$this->kunde = new Kunde($kdnr);
		}
	}

	/*
	 * creates and stores a pdf if parameter is set to true
	*/
    public function PDFgenerieren($store = false) {
        $pdf = new TCPDF('p', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetTitle('Angebot ' . $this->kunde->getKundennummer());
        $pdf->SetSubject('Angebot');
        $pdf->SetKeywords('pdf, angebot');

        $pdf->AddPage();

        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins(0, 0, 0, 0);

        $cAddress = "<p>{$this->kunde->getFirmenname()}<br>{$this->kunde->getName()}<br>{$this->kunde->getStrasse()} {$this->kunde->getHausnummer()}<br>{$this->kunde->getPostleitzahl()} {$this->kunde->getOrt()}</p>";
        $address = "<p>b-schriftung Brennemann Dietmar<br>Huberweg 31<br>94522 Wallersdorf</p>";

        $pdf->writeHTMLCell(85, 40, 20, 45, $cAddress);
        $pdf->writeHTMLCell(85, 40, 120, 35, $address);

        $pdf->setXY(20, 90);
        $pdf->Cell(20, 10, 'Menge', 'B');
        $pdf->Cell(20, 10, 'MEH', 'B');
        $pdf->Cell(80, 10, 'Bezeichnung', 'B');
        $pdf->Cell(20, 10, 'E-Preis', 'B');
		$pdf->Cell(20, 10, 'G-Preis', 'B');
		
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
				$pdf->Cell(20, 10, number_format($p->bekommePreis(), 2, ',', '') . ' €');
				$offset += 10;
			}
		}

		if ($store == true) {
            $filename= "{$this->kunde->getKundennummer()}_{$this->getInvoiceId()}.pdf"; 
            $filelocation = "C:\\xampp\htdocs\\auftragsbearbeitung\\files\\generated\\invoice";
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
			0 => array("COLUMN_NAME" => "Nummer"),
			1 => array("COLUMN_NAME" => "Kundennummer"),
			2 => array("COLUMN_NAME" => "Firmenname"),
			3 => array("COLUMN_NAME" => "Bezeichnung"),
			4 => array("COLUMN_NAME" => "Beschreibung"),
			5 => array("COLUMN_NAME" => "Datum"),
			6 => array("COLUMN_NAME" => "Termin"),
			7 => array("COLUMN_NAME" => "AngenommenDurch"));
		
		$data = DBAccess::selectQuery("SELECT auftrag.Auftragsnummer as Nummer, auftrag.Auftragsbezeichnung as Bezeichnung, auftrag.Auftragsbeschreibung as Beschreibung, auftrag.AngenommenDurch, auftrag.Kundennummer, auftrag.Datum, auftrag.Termin, kunde.Firmenname FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Rechnungsnummer != 0 AND Bezahlt = 0");

		for ($i = 0; $i < sizeof($data); $i++) {
			$id = $data[$i]["AngenommenDurch"];
			$angenommenDurch = DBAccess::selectQuery("SELECT Vorname, Nachname FROM mitarbeiter WHERE id = $id");
			$data[$i]["AngenommenDurch"] = $angenommenDurch[0]["Vorname"] . " " . $angenommenDurch[0]["Nachname"];
		}

		$table = new Table();
		$table->createByData($data, $column_names);
		$table->addActionButton("update", $identifier = "Nummer", $update = "istErledigt = 0");
		
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
		DBAccess::updateQuery("UPDATE auftrag SET Fertigstellung = current_date() $nextNumber WHERE Auftragsnummer = $orderId");
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