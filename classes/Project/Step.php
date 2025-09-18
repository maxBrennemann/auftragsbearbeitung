<?php

namespace Classes\Project;

use Classes\Notification\NotificationManager;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Step
{
    /* private $assignedTo = null;
    private $bezeichnung = null;
    private $datum = null;
    private $priority = null;
    private $istErledigt = null;
    private $auftragsnummer = null;
    private $schrittnummer = null; */

    public function __construct(/*$auftragsnummer, $schrittnummer, $bezeichnung, $datum, $priority, $istErledigt*/)
    {
        /* $this->auftragsnummer = $auftragsnummer;
        $this->bezeichnung = $bezeichnung;
        $this->schrittnummer = $schrittnummer;
        $this->datum = $datum;
        $this->priority = $priority;
        $this->istErledigt = $istErledigt; */
    }

    public function bearbeiten(): void {}

    public function erledigen(): void {}

    public static function insertStep(array $data): int
    {
        if ($data['Datum'] == null) {
            $data['Datum'] = "0000-00-00";
        }

        $postennummer = (int) DBAccess::insertQuery("INSERT INTO `schritte` (`Auftragsnummer`, `assignedTo`, `Bezeichnung`, `Datum`, `Priority`, `istErledigt`) VALUES (:auftragsnummer, :assignedTo, :bezeichnung, :datum, :priority, :status)", [
            "auftragsnummer" => $data["Auftragsnummer"],
            "assignedTo" => $data["assignedTo"] ?? 0,
            "bezeichnung" => $data["Bezeichnung"],
            "datum" => $data["Datum"],
            "priority" => $data["Priority"],
            "status" => $data["hide"],
        ]);

        OrderHistory::add($data["Auftragsnummer"], $postennummer, OrderHistory::TYPE_STEP, OrderHistory::STATE_ADDED, $data['Bezeichnung']);

        return $postennummer;
    }

    public static function insertStepAjax(): void
    {
        $data = [];
        $data["Bezeichnung"] = Tools::get("name");
        $data["Datum"] = Tools::get("date");
        $data["Priority"] = Tools::get("priority");
        $data["Auftragsnummer"] = Tools::get("orderId");
        $data["hide"] = Tools::get("hide") == "true" ? 1 : 0;
        $data["assignedTo"] = (int) Tools::get("assignedTo");

        $postenNummer = Step::insertStep($data);

        if ($data["assignedTo"] != 0) {
            NotificationManager::addNotification($data["assignedTo"], 1, Tools::get("name"), $postenNummer);
        }

        JSONResponseHandler::sendResponse([]);
    }

    public static function updateStep(array $data): void
    {
        OrderHistory::add($data["orderId"], $data['postennummer'], OrderHistory::TYPE_STEP, OrderHistory::STATE_FINISHED);
    }

    public static function deleteStep(): void {}

    public static function getSteps(): void
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
        }

        $data = DBAccess::selectQuery($query, [
            "id" => $id
        ]);

        JSONResponseHandler::sendResponse([
            "table" => $data,
            "status" => "success",
        ]);
    }

    public static function prepareData(array $data): void
    {
        foreach ($data["results"] as $key => $value) {
            $date = $data["results"][$key]["Datum"];
            if ($date == "0000-00-00") {
                $data["results"][$key]["Datum"] = "-";
            } else {
                $data["results"][$key]["Datum"] = date('d.m.Y', strtotime($date));
            }

            $prename = $data["results"][$key]["prename"];
            $lastname = $data["results"][$key]["lastname"];

            unset($data["results"][$key]["prename"]);
            unset($data["results"][$key]["lastname"]);

            $data["results"][$key]["name"] = trim("$prename $lastname");

            $data["results"][$key]["Priority"] = Priority::getPriorityLevel($data["results"][$key]["Priority"]);
        }
    }
}
