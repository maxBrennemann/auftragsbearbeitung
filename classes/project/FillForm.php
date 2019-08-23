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

	public function fill($nummer) {
		switch ($this->form_type) {
			case "Auftrag":
				$auftrags_daten = DBAccess::selectQuery("SELECT * FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Auftragsnummer = {$nummer}");
				$this->fillWithData($auftrags_daten);
				break;
			case "Rechnung":
				$rechnungs_daten = DBAccess::selectQuery("SELECT * FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Auftragsnummer = {$nummer}");

				$rechnung = new Rechnung($nummer);
				$gesamtNetto = $rechnung->preisBerechnen();
				$gesamtBrutto = $gesamtNetto * 1.19;
				$gesamtMwSt = $gesamtBrutto - $gesamtNetto;

				$additionalInfo = array("gesamtNetto" => $gesamtNetto, "gesamtBrutto" => $gesamtBrutto, "gesamtMwSt" => $gesamtMwSt);
				$rechnungs_daten[0] = array_merge($rechnungs_daten[0], $additionalInfo);

				$this->fillWithData($rechnungs_daten);
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

	public function show() {
		echo $this->file_content;
	}

	private function file_get_contents_utf8($fn) {
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
	}

}

?>