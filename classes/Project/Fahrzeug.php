<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use Classes\Link;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Fahrzeug
{

    public static function getImages($fahrzeugId)
    {
        $html = "";
        $query = "SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ FROM dateien LEFT JOIN dateien_fahrzeuge ON dateien_fahrzeuge.id_datei  = dateien.id WHERE dateien_fahrzeuge.id_fahrzeug = $fahrzeugId";
        $data = DBAccess::selectQuery($query);

        foreach ($data as $f) {
            $link = Link::getResourcesShortLink($f['Datei'], "upload");
            $html .= "<img src=\"$link\" width=\"150px\">";
        }

        return $html;
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

    public static function returnCustomer($fahrzeugId)
    {
        $kundenId = DBAccess::selectQuery("SELECT Kundennummer FROM fahrzeuge WHERE Nummer = $fahrzeugId")[0]["Kundennummer"];
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

        $auftragsverlauf = new Auftragsverlauf($orderId);
        $auftragsverlauf->addToHistory($vehicleId, 3, "added");

        JSONResponseHandler::sendResponse(array(
            "message" => "Vehicle attached to order",
            "vehicleId" => $vehicleId,
            "orderId" => $orderId,
        ));
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

		$uploadHandler = new UploadHandler();
		$files = $uploadHandler->uploadMultiple();

		$queryOrder = "INSERT INTO dateien_auftraege (id_datei, id_auftrag) VALUES ";
        $queryVehicle = "INSERT INTO dateien_fahrzeuge (id_datei, id_fahrzeug) VALUES ";

		$valuesOrder = [];
        $valuesVehicle = [];

		foreach ($files as $file) {
			$valuesOrder[] = [(int) $file["id"], $orderId];
            $valuesVehicle[] = [(int) $file["id"], $idVehicle];

			$auftragsverlauf = new Auftragsverlauf($orderId);
            $auftragsverlauf->addToHistory($file["id"], 4, "added");
  		}

		DBAccess::insertMultiple($queryOrder, $valuesOrder);
        DBAccess::insertMultiple($queryVehicle, $valuesVehicle);
	}
}
