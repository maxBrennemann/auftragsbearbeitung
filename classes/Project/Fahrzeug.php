<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Fahrzeug
{

    public static function getImages($fahrzeugId)
    {
        $query = "SELECT DISTINCT dateiname AS `file`, originalname, 
                DATE_FORMAT(`date`, '%d.%m.%Y %h:%i:%s') AS `date`, typ 
            FROM dateien 
            LEFT JOIN dateien_fahrzeuge ON dateien_fahrzeuge.id_datei  = dateien.id 
            WHERE dateien_fahrzeuge.id_fahrzeug = $fahrzeugId";
        return DBAccess::selectQuery($query);
    }

    public static function getShowAllOrders($fahrzeugId) {}

    public static function getName($fahrzeugId)
    {
        return DBAccess::selectQuery("SELECT Fahrzeug FROM fahrzeuge WHERE Nummer = $fahrzeugId")[0]["Fahrzeug"];
    }

    public static function getKennzeichen($fahrzeugId)
    {
        return DBAccess::selectQuery("SELECT Kennzeichen FROM fahrzeuge WHERE Nummer = $fahrzeugId")[0]["Kennzeichen"];
    }

    public static function returnCustomer($fahrzeugId): Kunde|null
    {
        $data = DBAccess::selectQuery("SELECT Kundennummer FROM fahrzeuge WHERE Nummer = $fahrzeugId");
        if ($data == null) {
            return null;
        }

        $kundenId = $data[0]["Kundennummer"];
        return new Kunde($kundenId);
    }

    public static function getSelection($kundennummer)
    {
        return DBAccess::selectQuery("SELECT Nummer, Kennzeichen, Fahrzeug FROM fahrzeuge WHERE Kundennummer = $kundennummer");
    }

    public static function attachVehicle()
    {
        $orderId = (int) Tools::get("id");
        $vehicleId = (int) Tools::get("vehicleId");

        DBAccess::insertQuery("INSERT INTO fahrzeuge_auftraege (id_fahrzeug, id_auftrag) VALUES (:vehicleId, :orderId)", [
            "vehicleId" => $vehicleId,
            "orderId" => $orderId
        ]);

        OrderHistory::add($orderId, $vehicleId, OrderHistory::TYPE_VEHICLE, OrderHistory::STATE_ADDED);

        JSONResponseHandler::sendResponse([
            "message" => "Vehicle attached to order",
            "vehicleId" => $vehicleId,
            "orderId" => $orderId,
        ]);
    }

    public static function removeVehicle()
    {
        $orderId = (int) Tools::get("id");
        $vehicleId = (int) Tools::get("vehicleId");

        DBAccess::deleteQuery("DELETE FROM fahrzeuge_auftraege WHERE id_fahrzeug = :vehicleId AND id_auftrag = :orderId", [
            "vehicleId" => $vehicleId,
            "orderId" => $orderId
        ]);

        OrderHistory::add($orderId, $vehicleId, OrderHistory::TYPE_VEHICLE, OrderHistory::STATE_REMOVED);

        JSONResponseHandler::sendResponse([
            "message" => "Vehicle removed from order",
            "vehicleId" => $vehicleId,
            "orderId" => $orderId,
        ]);
    }

    public static function updateName()
    {
        $id = (int) Tools::get("vehicleId");
        $value = Tools::get("name");
        $query = "UPDATE fahrzeuge SET Fahrzeug = :name WHERE Nummer = :id;";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "name" => $value,
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function updateLicensePlate()
    {
        $id = Tools::get("vehicleId");
        $value = Tools::get("licensePlate");
        $query = "UPDATE fahrzeuge SET Kennzeichen = :licensePlate WHERE Nummer = :id;";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "licensePlate" => $value,
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function addFiles()
    {
        $idVehicle = Tools::get("vehicleId");
        $orderId = Tools::get("id");

        $uploadHandler = new UploadHandler("upload", [
            "image/png",
            "image/jpg",
            "image/jpeg",
        ]);
        $files = $uploadHandler->uploadMultiple();

        $queryOrder = "INSERT INTO dateien_auftraege (id_datei, id_auftrag) VALUES ";
        $queryVehicle = "INSERT INTO dateien_fahrzeuge (id_datei, id_fahrzeug) VALUES ";

        $valuesOrder = [];
        $valuesVehicle = [];

        foreach ($files as $file) {
            $valuesOrder[] = [(int) $file["id"], $orderId];
            $valuesVehicle[] = [(int) $file["id"], $idVehicle];

            OrderHistory::add($orderId, $file["id"], OrderHistory::TYPE_FILE, OrderHistory::STATE_ADDED);
        }

        DBAccess::insertMultiple($queryOrder, $valuesOrder);
        DBAccess::insertMultiple($queryVehicle, $valuesVehicle);
    }
}
