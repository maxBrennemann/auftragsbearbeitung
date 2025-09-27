<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class Offer
{
    
    /**
     * @return array<int, array<string, string>>
     */
    public static function getOpenOffers(): array
    {
        $query = "SELECT o.id, o.creation_date, o.customer_id, k.Firmenname, CONCAT(k.Vorname, ' ', k.Nachname) AS name
            FROM offer o, kunde k 
            WHERE `state` = 'created' 
                AND k.Kundennummer = o.customer_id;";
        $data = DBAccess::selectQuery($query);

        return $data;
    }

    public static function getAllOffers(): void {}
}
