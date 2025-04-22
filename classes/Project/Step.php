<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

use Classes\Project\Auftragsverlauf;

class Step {
    
	private $istAllgemein = null;
	private $bezeichnung = null;
	private $datum = null;
	private $priority = null;
	private $istErledigt = null;
	private $auftragsnummer = null;
	private $schrittnummer = null;

	public function __construct($auftragsnummer, $schrittnummer, $bezeichnung, $datum, $priority, $istErledigt) {
		$this->auftragsnummer = $auftragsnummer;
		$this->bezeichnung = $bezeichnung;
		$this->schrittnummer = $schrittnummer;
		$this->datum = $datum;
		$this->priority = $priority;
		$this->istErledigt = $istErledigt;
	}

    public function bearbeiten() {
        
    }
 
    public function erledigen() {
        
    }

	public function getHTMLCode() {
		$htmlCode = "<div><span>{$this->bezeichnung}</span><br><span>Datum: {$this->datum}</span><br><span>{$this->priority}</span><br><span>{$this->istErledigt}</span></div>";
		return $htmlCode;
	}

	public static function insertStep($data) {
		$bez = $data['Bezeichnung'];
		$dat = $data['Datum'];
		$pri = $data['Priority'];
		$auf = $data['Auftragsnummer'];
		$erl = $data['hide'];

		if ($dat == "0" || $dat == 0) {
			$dat = "0000-00-00";
		}

		$auftragsverlauf = new Auftragsverlauf($auf);
		$postennummer = DBAccess::insertQuery("INSERT INTO `schritte` (`Auftragsnummer`, `istAllgemein`, `Bezeichnung`, `Datum`, `Priority`, `istErledigt`) VALUES ($auf, 1, '$bez', '$dat', $pri, $erl)");
		$auftragsverlauf->addToHistory($postennummer, 2, "added", $bez);

		return $postennummer;
	}

	public static function updateStep($data) {
		$auftragsverlauf = new Auftragsverlauf($data['orderId']);
		$auftragsverlauf->addToHistory($data['postennummer'], 2, "finished");
	}

	public  static function deleteStep() {
		
	}

	public static function getSteps() {
		$id = Tools::get("id");
		$type = Tools::get("type");

		$order = new Auftrag($id);
		$table = "";

		switch ($type) {
			case "getAllSteps":
				$table = $order->getBearbeitungsschritteAsTable();
				break;
			case "getOpenSteps":
				$table = $order->getOpenBearbeitungsschritteTable();
				break;
			default:
				JSONResponseHandler::returnNotFound("unsupported type");
		}

		JSONResponseHandler::sendResponse([
			"table" => $table,
			"status" => "success",
		]);
	}

}
