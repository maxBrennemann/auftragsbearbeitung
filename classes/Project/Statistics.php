<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class Statistics
{

	public static function getOrderSum($orderId)
	{
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

	public static function getAllOrdersSum()
	{
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

	public static function getVolumeByMonth($startDate, $endDate)
	{
		$query = "SELECT CONCAT(MONTHNAME(Fertigstellung), ' ', YEAR(Fertigstellung)) AS `date`, SUM(orderPrice) AS `value`
			FROM auftragssumme
			WHERE Fertigstellung BETWEEN :startDate AND :endDate
			GROUP BY YEAR(Fertigstellung), MONTH(Fertigstellung)";

		return DBAccess::selectQuery($query, [
			"startDate" => $startDate,
			"endDate" => $endDate,
		]);
	}

	public static function getOrders($startDate, $endDate)
	{
		$query = "SELECT COUNT(Auftragsnummer) as `value`, DATE_FORMAT(Datum,'%Y-%m') as `date` 
			FROM auftrag 
			WHERE Datum BETWEEN :startDate AND :endDate
			GROUP BY DATE_FORMAT(Datum,'%Y-%m')";

		return DBAccess::selectQuery($query, [
			"startDate" => $startDate,
			"endDate" => $endDate,
		]);
	}

	public static function getOrdersByCustomer($startDate, $endDate)
	{
		$query = "SELECT COUNT(Auftragsnummer) as `value`, CONCAT(Firmenname, ' ', Vorname, ' ', Nachname) as `date` 
			FROM auftrag, kunde
			WHERE kunde.Kundennummer = auftrag.Kundennummer AND Datum BETWEEN :startDate AND :endDate
			GROUP BY `date`";

		return DBAccess::selectQuery($query, [
			"startDate" => $startDate,
			"endDate" => $endDate,
		]);
	}

	public static function getVolumeByOrderType($startDate, $endDate)
	{
		$query = "SELECT SUM(av.price) as `value`, `at`.Auftragstyp as `date` 
			FROM auftragssumme_view av, auftrag a, auftragstyp `at`
			WHERE av.id = a.Auftragsnummer
			AND at.id = a.Auftragstyp
			AND a.Fertigstellung BETWEEN :startDate AND :endDate
			GROUP BY `date`";

		return DBAccess::selectQuery($query, [
			"startDate" => $startDate,
			"endDate" => $endDate,
		]);
	}

	public static function dispatcher()
	{
		$diagramType = $_POST["diagramType"];
		$startDate = $_POST["startDate"];
		$endDate = $_POST["endDate"];
		$dimension = $_POST["dimension"];
		$datatype = $_POST["datatype"];

		switch ($diagramType) {
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
				$data = self::getOrders($startDate, $endDate);
				break;
			case "getOrdersByCustomer":
				$data = self::getOrdersByCustomer($startDate, $endDate);
				break;
			case "getVolumeByOrderType":
				$data = self::getVolumeByOrderType($startDate, $endDate);
				break;
			default:
				$data = [];
				break;
		}

		echo json_encode($data);
	}
}
