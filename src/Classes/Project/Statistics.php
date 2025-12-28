<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class Statistics
{

    /**
     * @return array<int, array<string, string>>
     */
    public static function getOrderSum(int $orderId): array
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

    /**
     * @return array<int, array<string, string>>
     */
    public static function getAllOrdersSum(): array
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

    /**
     * @param string $startDate
     * @param string $endDate
     * @return array<int, array<string, string>>
     */
    public static function getVolumeByMonth(string $startDate, string $endDate): array
    {
        $query = "SELECT CONCAT(MONTHNAME(finalized_date), ' ', YEAR(finalized_date)) AS `date`, SUM(amount) AS `value`
			FROM invoice
			WHERE finalized_date BETWEEN :startDate AND :endDate
			GROUP BY YEAR(finalized_date), MONTH(finalized_date)";

        return DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @return array<int, array<string, string>>
     */
    public static function getOrders(string $startDate, string $endDate): array
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

    /**
     * @param string $startDate
     * @param string $endDate
     * @return array<int, array<string, string>>
     */
    public static function getOrdersByCustomer(string $startDate, string $endDate): array
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

    /**
     * @param string $startDate
     * @param string $endDate
     * @return array<int, array<string, string>>
     */
    public static function getVolumeByOrderType(string $startDate, string $endDate): array
    {
        $query = "SELECT SUM(invoice.amount) as `value`, `at`.Auftragstyp as `date` 
			FROM invoice, auftrag a, auftragstyp `at`
			WHERE invoice.order_id = a.Auftragsnummer
			AND at.id = a.Auftragstyp
			AND a.Fertigstellung BETWEEN :startDate AND :endDate
			GROUP BY `date`";

        return DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    public static function dispatcher(): void
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

/**
 * Dimensionen und Parameter:
 * - Startdatum
 * - Enddatum
 * - Dimension: Alle, Auftragstyp, Monat, Dauer, Kunde, Umsatz, Gewinn
 * 
 */
