<?php

namespace Classes\Project;

use Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\DBAccess;

class OrderHistory
{
    public const STATE_ADDED = "added";
    public const STATE_DELETED = "deleted";
    public const STATE_REMOVED = "removed";
    public const STATE_EDITED = "edited";
    public const STATE_FINISHED = "finished";
    public const STATE_PAYED = "payed";

    public const TYPE_ITEM = 1;
    public const TYPE_STEP = 2;
    public const TYPE_VEHICLE = 3;
    public const TYPE_FILE = 4;
    public const TYPE_ORDER = 5;
    public const TYPE_OFFER = 6;
    public const TYPE_NOTE = 7;

    public static function add(int $orderId, int $number, int $type, string $state, string $alternative_text = ""): void
    {
        $userId = User::getCurrentUserId();
        $query = "INSERT INTO history (orderid, `number`, `type`, `state`, member_id, alternative_text) VALUES (:orderId, :number, :type, :state, :userId, :alternative_text)";
        $params = array(
            ":orderId" => $orderId,
            ":number" => $number,
            ":type" => $type,
            ":state" => $state,
            ":userId" => $userId,
            ":alternative_text" => $alternative_text
        );
        DBAccess::insertQuery($query, $params);
    }

    public static function getHistory(int $orderId): array
    {
        $query = "SELECT history.id, history.insertstamp, history_type.name , CONCAT(COALESCE(history.alternative_text, ''), COALESCE(ids.descr, '')) AS Beschreibung, history.state, user.username, user.prename
            FROM history
            LEFT JOIN (
                (
                    SELECT CONCAT(fahrzeuge.Kennzeichen, ' ', fahrzeuge.Fahrzeug) AS `descr`, fahrzeuge_auftraege.id_fahrzeug AS id, 3 AS `type` 
                    FROM fahrzeuge, fahrzeuge_auftraege 
                    WHERE fahrzeuge.Nummer = fahrzeuge_auftraege.id_fahrzeug
                )
                UNION
                (
                    SELECT notes.note AS `descr`, notes.id AS id, 7 AS `type` 
                    FROM notes
                )
            ) ids ON history.number = ids.id 
                AND history.type = ids.type
            LEFT JOIN history_type ON history_type.type_id = history.type
            LEFT JOIN user ON user.id = history.member_id
            WHERE history.orderid = :auftragsnummer
            ORDER BY history.insertstamp DESC";

        return DBAccess::selectQuery($query, [
            "auftragsnummer" => $orderId,
        ]);
    }

    public static function representHistoryAsHTML(int $orderId): string
    {
        $history = self::getHistory($orderId);
        return TemplateController::getTemplate("orderHistory", [
            "historyElement" => $history,
        ]);
    }
}
