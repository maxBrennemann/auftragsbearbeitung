<?php

namespace Src\Classes\Project;

use Src\Classes\Notification\NotificationManager;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;
use Src\Classes\Notification\NotificationType;

class Step
{
    public static function insertStep(string $name, string $date, int $priority, int $orderId, int $hidden, int $assignedTo): int
    {
        if ($date == null) {
            $date = "0000-00-00";
        }

        $postennummer = (int) DBAccess::insertQuery("INSERT INTO `schritte` (`Auftragsnummer`, `assignedTo`, `Bezeichnung`, `Datum`, `Priority`, `istErledigt`) VALUES (:auftragsnummer, :assignedTo, :bezeichnung, :datum, :priority, :status)", [
            "auftragsnummer" => $orderId,
            "assignedTo" => $assignedTo,
            "bezeichnung" => $name,
            "datum" => $date,
            "priority" => $priority,
            "status" => $hidden,
        ]);

        OrderHistory::add($orderId, $postennummer, OrderHistory::TYPE_STEP, OrderHistory::STATE_ADDED, $name);

        return $postennummer;
    }

    public static function insertStepAjax(): void
    {
        $name = Tools::get("name");
        $date = Tools::get("date");
        $priority = Tools::get("priority");
        $orderId = Tools::get("orderId");
        $hidden = Tools::get("hide") == "true" ? 1 : 0;
        $assigendTo = (int) Tools::get("assignedTo");

        $postenNummer = Step::insertStep($name, $date, $priority, $orderId, $hidden, $assigendTo);

        if ($assigendTo != 0) {
            NotificationManager::addNotification($assigendTo, NotificationType::TYPE_STEP, $name, $postenNummer);
        }

        JSONResponseHandler::sendResponse([
            "stepId" => $postenNummer,
            "priority" => Priority::getPriorityLevel($priority),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    public static function updateStep(array $data): void
    {
        OrderHistory::add($data["orderId"], $data["postennummer"], OrderHistory::TYPE_STEP, OrderHistory::STATE_FINISHED);
    }

    public static function deleteStep(): void
    {
        $id = Tools::get("id");
        $orderId = Tools::get("orderId");

        $query = "DELETE FROM schritte WHERE Schrittnummer = :id";
        DBAccess::deleteQuery($query, [
            "id" => $id,
        ]);

        OrderHistory::add($orderId, $id, OrderHistory::TYPE_STEP, OrderHistory::STATE_DELETED);
        NotificationManager::deleteById(NotificationType::TYPE_STEP, $id);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

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

    /**
     * @param array<string, mixed> $data
     * @return void
     */
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
