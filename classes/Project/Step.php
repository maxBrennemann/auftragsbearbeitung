<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

use Classes\Notification\NotificationManager;

class Step
{

	private $istAllgemein = null;
	private $bezeichnung = null;
	private $datum = null;
	private $priority = null;
	private $istErledigt = null;
	private $auftragsnummer = null;
	private $schrittnummer = null;

	public function __construct($auftragsnummer, $schrittnummer, $bezeichnung, $datum, $priority, $istErledigt)
	{
		$this->auftragsnummer = $auftragsnummer;
		$this->bezeichnung = $bezeichnung;
		$this->schrittnummer = $schrittnummer;
		$this->datum = $datum;
		$this->priority = $priority;
		$this->istErledigt = $istErledigt;
	}

	public function bearbeiten() {}

	public function erledigen() {}

	public function getHTMLCode()
	{
		$htmlCode = "<div><span>{$this->bezeichnung}</span><br><span>Datum: {$this->datum}</span><br><span>{$this->priority}</span><br><span>{$this->istErledigt}</span></div>";
		return $htmlCode;
	}

	public static function insertStep($data): int
	{
		if ($data['Datum'] == null) {
			$data['Datum'] = "0000-00-00";
		}

		$postennummer = (int) DBAccess::insertQuery("INSERT INTO `schritte` (`Auftragsnummer`, `istAllgemein`, `Bezeichnung`, `Datum`, `Priority`, `istErledigt`) VALUES (:auftragsnummer, 1, :bezeichnung, :datum, :priority, :status)", [
			"auftragsnummer" => $data["Auftragsnummer"],
			"bezeichnung" => $data["Bezeichnung"],
			"datum" => $data["Datum"],
			"priority" => $data["Priority"],
			"status" => $data["hide"],
		]);

		OrderHistory::add($data["Auftragsnummer"], $postennummer, OrderHistory::TYPE_STEP, OrderHistory::STATE_ADDED, $data['Bezeichnung']);

		return $postennummer;
	}

	public static function insertStepAjax()
	{
		$data = [];
		$data["Bezeichnung"] = Tools::get("name");
		$data["Datum"] = Tools::get("date");
		$data["Priority"] = Tools::get("priority");
		$data["Auftragsnummer"] = Tools::get("orderId");
		$data["hide"] = Tools::get("hide") == "true" ? 1 : 0;

		$postenNummer = Step::insertStep($data);

		$assignedTo = (int) Tools::get("assignedTo");
		if ($assignedTo != 0) {
			NotificationManager::addNotification( $assignedTo, 1, Tools::get("name"), $postenNummer);
		}
	}

	public static function updateStep($data)
	{
		OrderHistory::add($data["orderId"], $data['postennummer'], OrderHistory::TYPE_STEP, OrderHistory::STATE_FINISHED);
	}

	public  static function deleteStep() {}

	public static function getSteps()
	{
		$id = Tools::get("id");
		$type = Tools::get("type");
		$query = "";

		switch ($type) {
			case "getAllSteps":
				$query = "SELECT Schrittnummer, Bezeichnung, Datum, `Priority`, finishingDate FROM schritte WHERE Auftragsnummer = :id ORDER BY `Priority` DESC";
				break;
			case "getOpenSteps":
				$query = "SELECT Schrittnummer, Bezeichnung, Datum, `Priority` FROM schritte WHERE Auftragsnummer = :id AND istErledigt = 1 ORDER BY `Priority` DESC";
				break;
			default:
				JSONResponseHandler::returnNotFound("unsupported type");
				return;
		}

		$data = DBAccess::selectQuery($query, [
			"id" => $id
		]);

		JSONResponseHandler::sendResponse([
			"table" => $data,
			"status" => "success",
		]);
	}

	public static function prepareData($data)
    {
        foreach ($data["results"] as $key => $value) {
			$date = $data["results"][$key]["Datum"];
			if ($date == "0000-00-00") {
				$data["results"][$key]["Datum"] = "-";
			} else {
				$data["results"][$key]["Datum"] = date('d.m.Y', strtotime($date));
			}

			$data["results"][$key]["Priority"] = Priority::getPriorityLevel($data["results"][$key]["Priority"]);
		}
    }
}
