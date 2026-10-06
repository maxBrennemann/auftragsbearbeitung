<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

/**
 * Kopfzeilen-Override für die Adresse auf Rechnungen und Angeboten. Gilt pro Kunde
 * (nicht pro Dokument), damit einmal erfasste Zeilen für alle Rechnungen/Angebote
 * dieses Kunden wiederverwendet werden, statt bei jedem neuen Dokument erneut
 * eingetippt werden zu müssen.
 */
class CustomerAltNames
{
    /**
     * @return array<int, array<string, string>>
     */
    public static function getForCustomer(int $customerId): array
    {
        $query = "SELECT id, `text` FROM customer_alt_names WHERE id_customer = :customerId ORDER BY id ASC";
        return DBAccess::selectQuery($query, [
            "customerId" => $customerId,
        ]);
    }

    public static function add(int $customerId, string $text): void
    {
        $query = "INSERT INTO customer_alt_names (id_customer, `text`) VALUES (:customerId, :text);";
        DBAccess::insertQuery($query, [
            "customerId" => $customerId,
            "text" => $text,
        ]);
    }

    public static function edit(int $customerId, int $id, string $text): void
    {
        $query = "UPDATE customer_alt_names SET `text` = :text WHERE id = :id AND id_customer = :customerId;";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "customerId" => $customerId,
            "text" => $text,
        ]);
    }

    public static function remove(int $customerId, int $id): void
    {
        $query = "DELETE FROM customer_alt_names WHERE id = :id AND id_customer = :customerId;";
        DBAccess::deleteQuery($query, [
            "id" => $id,
            "customerId" => $customerId,
        ]);
    }
}
