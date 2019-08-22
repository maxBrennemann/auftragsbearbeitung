<?php 

require_once('classes/DBAccess.php');
require_once('classes/Link.php');

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
		if ($this->form_type == "Auftrag") {
			$auftrags_daten = DBAccess::selectQuery("SELECT * FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Auftragsnummer = {$nummer}");
			if (!empty($auftrags_daten)) {
				$auftrags_daten = $auftrags_daten[0];

				for ($i = 0; $i < sizeof($this->keywords); $i++) {
					$keyword = $this->keywords[$i]['keyword'];
					$fieldname = $this->keywords[$i]['fieldname'];
					if ($fieldname == null || $fieldname == "") {
						$replacement = "";
					} else {
						$replacement = $auftrags_daten[$fieldname];
					}
					$this->file_content = str_replace($keyword, $replacement, $this->file_content);
				}
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