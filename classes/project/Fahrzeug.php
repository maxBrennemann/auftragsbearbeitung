<?php 

require_once('classes/DBAccess.php');
require_once('Kunde.php');

class Fahrzeug {

    public static function getImages($fahrzeugId) {
        $html = "";
        $query = "SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ FROM dateien LEFT JOIN dateien_fahrzeuge ON dateien_fahrzeuge.id_datei  = dateien.id WHERE dateien_fahrzeuge.id_fahrzeug = $fahrzeugId";
        $data = DBAccess::selectQuery($query);
        
        foreach ($data as $f) {
            $link = Link::getResourcesShortLink($f['Datei'], "upload");
            $html .= "<img src=\"$link\" width=\"150px\">";
        }

        return $html;
    }

    public static function getShowAllOrders($fahrzeugId) {

    }

    public static function getName($fahrzeugId) {
        return DBAccess::selectQuery("SELECT Fahrzeug FROM fahrzeuge WHERE Nummer = $fahrzeugId")[0]["Fahrzeug"];
    }

    public static function getKennzeichen($fahrzeugId) {
        return DBAccess::selectQuery("SELECT Kennzeichen FROM fahrzeuge WHERE Nummer = $fahrzeugId")[0]["Kennzeichen"];
    }

    public static function returnCustomer($fahrzeugId) {
        $kundenId = DBAccess::selectQuery("SELECT Kundennummer FROM fahrzeuge WHERE Nummer = $fahrzeugId")[0]["Kundennummer"];
        return new Kunde($kundenId);
    }

    public static function getSelection($kundennummer) {
        return DBAccess::selectQuery("SELECT Nummer, Kennzeichen, Fahrzeug FROM fahrzeuge WHERE Kundennummer = $kundennummer");
    }

    public static function attachVehicle() {
        $orderId = (int) Tools::get("id");
        $vehicleId = (int) Tools::get("vehicleId");

        DBAccess::insertQuery("INSERT INTO fahrzeuge_auftraege (id_fahrzeug, id_auftrag) VALUES (:vehicleId, :orderId)", [
            "vehicleId" => $vehicleId,
            "orderId" => $orderId
        ]);
        
        $auftragsverlauf = new Auftragsverlauf($orderId);
		$auftragsverlauf->addToHistory($vehicleId, 3, "added");
        $table = (new Auftrag($orderId))->getFahrzeuge();

        JSONResponseHandler::sendResponse(array(
            "message" => "Vehicle attached to order",
            "vehicleId" => $vehicleId,
            "orderId" => $orderId,
            "table" => $table,
        ));
    }

}
