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

	function __construct() {
		if (isset($_SESSION['currentInvoice_orderId'])) {
			$currentOrder = $_SESSION['currentInvoice_orderId'];
			$this->auftrag = new Auftrag($currentOrder);
			$kdnr = $this->auftrag->getKundennummer();
			$this->kunde = new Kunde($kdnr);
		}
	}

    public function PDFgenerieren() {
        $pdf = new TCPDF('p', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetTitle('Angebot ' . $this->kunde->getKundennummer());
        $pdf->SetSubject('Angebot');
        $pdf->SetKeywords('pdf, angebot');

        $pdf->AddPage();

        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins(0, 0, 0, 0);

        $cAdress = "<p>{$this->kunde->getFirmenname()}<br>{$this->kunde->getName()}<br>{$this->kunde->getStrasse()} {$this->kunde->getHausnummer()}<br>{$this->kunde->getPostleitzahl()} {$this->kunde->getOrt()}</p>";
        $adress = "<p>b-schriftung Brennemann ***REMOVED***<br>***REMOVED***<br>***REMOVED***</p>";

        $pdf->writeHTMLCell(85, 40, 20, 45, $cAdress);
        $pdf->writeHTMLCell(85, 40, 120, 35, $adress);

        $pdf->setXY(20, 90);
        $pdf->Cell(20, 10, 'Menge', 'B');
        $pdf->Cell(20, 10, 'MEH', 'B');
        $pdf->Cell(80, 10, 'Bezeichnung', 'B');
        $pdf->Cell(20, 10, 'E-Preis', 'B');
		$pdf->Cell(20, 10, 'G-Preis', 'B');
		
		$pdf->Output();
    }

	public static function getNextNumber() {
		$number = DBAccess::selectQuery("SELECT MAX(Rechnungsnummer) FROM auftrag")[0]['MAX(Rechnungsnummer)'];
		$number = (int) $number;
		$number++;
		return $number;
	}

	public static function getOffeneRechnungen() {
		$column_names = array(0 => array("COLUMN_NAME" => "Auftragsnummer"), 1 => array("COLUMN_NAME" => "Kundennummer"), 2 => array("COLUMN_NAME" => "Firmenname"),
				3 => array("COLUMN_NAME" => "Auftragsbezeichnung"), 4 => array("COLUMN_NAME" => "Auftragsbeschreibung"), 5 => array("COLUMN_NAME" => "Datum"), 
				6 => array("COLUMN_NAME" => "Termin"), 7 => array("COLUMN_NAME" => "AngenommenDurch"));
		$data = DBAccess::selectQuery("SELECT auftrag.*, kunde.Firmenname FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Rechnungsnummer != 0 AND Bezahlt = 0");

		for ($i = 0; $i < sizeof($data); $i++) {
			$id = $data[$i]["AngenommenDurch"];
			$angenommenDurch = DBAccess::selectQuery("SELECT Vorname, Nachname FROM mitarbeiter WHERE id = $id");
			$data[$i]["AngenommenDurch"] = $angenommenDurch[0]["Vorname"] . " " . $angenommenDurch[0]["Nachname"];
		}

		/*$form = new FormGenerator("auftrag", "Datum", "Rechnungsnummer = 0");
		$table = $form->createTableByDataRowLink($data, $column_names, "auftrag", null);*/

		$form = new InteractiveFormGenerator("auftrag", "Datum", "Rechnungsnummer 0 ");
		$form->setRowDone(true);

		$table = $form->create($data, $column_names, "Auftragsnummer");
		return $table;
	}

	public static function getOffeneRechnungssumme() {
		$numbers = DBAccess::selectQuery("SELECT Auftragsnummer FROM auftrag WHERE Rechnungsnummer != 0");
		$summe = 0;
		foreach ($numbers as $auftrag) {
			$auftrag = new Auftrag($auftrag['Auftragsnummer']);
			$summe += $auftrag->preisBerechnen();
		}

		return $summe;
	}

}

?>