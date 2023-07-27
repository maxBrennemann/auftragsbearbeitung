<?php

class Statistics {

	public static function getOrderSum($orderId) {
		$query = "SELECT ROUND(SUM(all_posten.price), 2) AS orderPrice 
			FROM (
					SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price 
					FROM zeit, posten 
					WHERE zeit.Postennummer = posten.Postennummer 
						AND posten.Auftragsnummer = $orderId
				UNION ALL
					SELECT leistung_posten.SpeziefischerPreis AS price 
					FROM leistung_posten, posten 
					WHERE leistung_posten.Postennummer = posten.Postennummer 
						AND posten.Auftragsnummer = $orderId)
			all_posten";

		return DBAccess::selectQuery($query);
	}

	public static function getAllOrdersSum() {
		$query = "SELECT ROUND(SUM(all_posten.price), 2) AS orderPrice, all_posten.id AS id 
			FROM (
					SELECT (zeit.ZeitInMinuten / 60) * zeit.Stundenlohn AS price, posten.Auftragsnummer as id 
					FROM zeit, posten 
					WHERE zeit.Postennummer = posten.Postennummer 
				UNION ALL 
					SELECT leistung_posten.SpeziefischerPreis AS price, posten.Auftragsnummer as id 
					FROM leistung_posten, posten 
					WHERE leistung_posten.Postennummer = posten.Postennummer) 
					all_posten 
			GROUP BY id";

		return DBAccess::selectQuery($query);
	}

	public static function getVolumeByMonth($startDate, $endDate) {
		$query = "SELECT CONCAT(MONTHNAME(Fertigstellung), ' ', YEAR(Fertigstellung)) AS `date`, SUM(orderPrice) AS `value`
			FROM auftragssumme
			WHERE Fertigstellung BETWEEN :startDate AND :endDate
			GROUP BY YEAR(Fertigstellung), MONTH(Fertigstellung)";

		return DBAccess::selectQuery($query, [
			"startDate" => $startDate,
			"endDate" => $endDate,
		]);
	}

	public static function getOrders($startDate, $endDate, $open) {
		$addition = "";
		if ($open == "getOpenOrders") {
			$addition = "AND Rechnungsnummer = 0 AND archiviert = 0";
		}

		$query = "SELECT COUNT(Auftragsnummer) as `value`, DATE_FORMAT(Datum,'%Y-%m') as `date` 
			FROM auftrag 
			WHERE Datum BETWEEN :startDate AND :endDate $addition
			GROUP BY DATE_FORMAT(Datum,'%Y-%m')";

		return DBAccess::selectQuery($query, [
			"startDate" => $startDate,
			"endDate" => $endDate,
		]);
	}

	public static function dispatcher() {
		$function = $_POST["function"];
		$startDate = $_POST["startDate"];
		$endDate = $_POST["endDate"];
		$dimension = $_POST["dimension"];
		$datatype = $_POST["datatype"];

		switch ($function) {
			case "getOrderSum":
				$data = self::getOrderSum(10);
				break;
			case "getAllOrdersSum":
				$data = self::getAllOrdersSum();
				break;
			case "getVolumeByMonth":
				$data = self::getVolumeByMonth($startDate, $endDate);
				break;
			case "getOrders":
			case "getOpenOrders":
				$data = self::getOrders($startDate, $endDate, $datatype);
				break;
			default:
				$data = [];
				break;
		}

		echo json_encode($data);
	}

}
