<?php 

require_once('classes/DBAccess.php');
require_once('classes/Link.php');
require_once('classes/project/Rechnung.php');

class FillForm {

	private $keywords;
	private $form_type;
	private $file_content;

	function __construct($form_type) {
		$this->form_type = $form_type;
		$this->receive_keywords();
		$this->get_file_contents();
	}

	private function receive_keywords() {
		$this->keywords = DBAccess::selectQuery("SELECT keyword, fieldname FROM keywords WHERE type = '{$this->form_type}'");
	}

	private function get_file_contents() {
		$file = Link::getResourcesLink($_POST['file'] . ".htm", "html", false);
		$this->file_content = file_get_contents_utf8($file);
	}

	private function get_file_contents_by_file_name($filename) {
		$file = Link::getResourcesLink($filename . ".htm", "html", false);
		return file_get_contents_utf8($file);
	}

	public function fill($nummer) {
		switch ($this->form_type) {
			case "Auftrag":
				$auftrags_daten = DBAccess::selectQuery("SELECT * FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Auftragsnummer = {$nummer}");

				$id = $auftrags_daten[0]["AngenommenDurch"];
				$angenommenDurch = DBAccess::selectQuery("SELECT Vorname, Nachname FROM mitarbeiter WHERE id = $id");
				$auftrags_daten[0]["AngenommenDurch"] = $angenommenDurch[0]["Vorname"] . " " . $angenommenDurch[0]["Nachname"];

				if ($auftrags_daten[0]["Fertigstellung"] == '0000-00-00') {
					$auftrags_daten[0]["Fertigstellung"] = "";
				}

				$this->fillWithData($auftrags_daten);
				break;
			case "Rechnung":
				$rechnungs_daten = DBAccess::selectQuery("SELECT * FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Rechnungsnummer = {$nummer}");

				if (!empty($rechnungs_daten)) {
					$rechnung = new Rechnung($nummer);
					$gesamtNetto = round($rechnung->preisBerechnen(), 2);
					$gesamtBrutto = round($gesamtNetto * 1.19, 2);
					$gesamtMwSt = $gesamtBrutto - $gesamtNetto;

					$additionalInfo = array("gesamtNetto" => $gesamtNetto . " €", "gesamtBrutto" => $gesamtBrutto . " €", "gesamtMwSt" => $gesamtMwSt . " €");
					$rechnungs_daten[0] = array_merge($rechnungs_daten[0], $additionalInfo);

					$this->fillWithData($rechnungs_daten);
					$this->fillPosten($rechnungs_daten[0]["Auftragsnummer"]);
				}
				
				break;
		}
	}

	private function fillWithData($daten) {
		if (!empty($daten)) {
			$daten = $daten[0];

			for ($i = 0; $i < sizeof($this->keywords); $i++) {
				$keyword = $this->keywords[$i]['keyword'];
				$fieldname = $this->keywords[$i]['fieldname'];
				if ($fieldname == null || $fieldname == "") {
					$replacement = "";
				} else {
					$replacement = $daten[$fieldname];
				}
				$this->file_content = str_replace($keyword, $replacement, $this->file_content);
			}
		}
	}

	private function fillPosten($auftragsnummer) {
		$posten = $this->getPosten($auftragsnummer);
		$arr = array("MENG", "STK", "BEZ", "EPR", "GPR");
		for ($n = 0; $n < sizeof($posten); $n++) {
			$content = $this->get_file_contents_by_file_name("Posten");
			$this->file_content = str_replace("PATT", $content, $this->file_content);

			$p = $posten[$n];
			$anzahl = $p["Anzahl"];
			$stk = "Stück";
			if ((int) $anzahl == 0) {
				$anzahl = 1;
			}

			$preis = (float) $p["Preis"];
			if ($p['Stundenlohn'] != "") {
				$anzahl = ((int) $p['ZeitInMinuten']) / 60;
				$preis = $p['Stundenlohn'];
				$stk = "Stunden";
			}

			$desc = $p["Bezeichnung"];
			if ($desc == "") {
				$desc = $p["Beschreibung"];
			}

			$this->file_content = str_replace($arr[0], round($anzahl, 2), $this->file_content);
			$this->file_content = str_replace($arr[1], $stk, $this->file_content);
			$this->file_content = str_replace($arr[2], $desc, $this->file_content);
			$this->file_content = str_replace($arr[3], $preis . " €", $this->file_content);
			$this->file_content = str_replace($arr[4], round($anzahl * $preis, 2)  . " €", $this->file_content);
		}

		$this->file_content = str_replace("PATT", "", $this->file_content);
	}

	private function getPosten($auftragsnummer) {
		$posten = Posten::bekommeAllePosten($auftragsnummer);

		$column_names = array(0 => array("COLUMN_NAME" => "Bezeichnung"), 1 => array("COLUMN_NAME" => "Beschreibung"), 
				2 => array("COLUMN_NAME" => "Stundenlohn"), 3 => array("COLUMN_NAME" => "ZeitInMinuten"), 4 => array("COLUMN_NAME" => "Preis"), 
				5 => array("COLUMN_NAME" => "Anzahl"), 6 => array("COLUMN_NAME" => "Einkaufspreis"));

		$subArr = array("Bezeichnung" => "", "Beschreibung" => "", "Stundenlohn" => "", "ZeitInMinuten" => "", "Preis" => "", "Anzahl" => "", "Einkaufspreis" => "");
		$data = array(sizeof($posten));

		if (sizeof($posten) == 0) {
			return "";
		}

		for ($i = 0; $i < sizeof($posten); $i++) {
			$data[$i] = $posten[$i]->fillToArray($subArr);
		}

		return $data;
	}

	public function show() {
		echo $this->file_content;
	}

	private function file_get_contents_utf8($fn) {
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
	}

}

?>