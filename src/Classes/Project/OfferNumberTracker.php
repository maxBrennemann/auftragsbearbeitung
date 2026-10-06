<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class OfferNumberTracker
{
    public static function peekNextOfferNumber(): int
    {
        return self::getCurrentOfferNumber() + 1;
    }

    public static function getCurrentOfferNumber(): int
    {
        $query = "SELECT last_used_number FROM offer_number_tracker WHERE id = 1";
        $result = DBAccess::selectQuery($query);

        if (empty($result)) {
            return 0;
        }

        return (int) $result[0]["last_used_number"];
    }

    public static function completeOffer(int $offerId): int
    {
        $lastUsedNumber = self::getCurrentOfferNumber();
        $newOfferNumber = $lastUsedNumber + 1;

        /* REPLACE instead of UPDATE so this works even before the tracker row has ever been seeded */
        $query = "REPLACE INTO offer_number_tracker (id, last_used_number) VALUES (1, :lastUsedNumber)";
        DBAccess::insertQuery($query, [
            "lastUsedNumber" => $newOfferNumber
        ]);

        $query = "UPDATE offer SET offer_number = :offerNumber WHERE id = :offerId";
        DBAccess::updateQuery($query, [
            "offerNumber" => $newOfferNumber,
            "offerId" => $offerId
        ]);

        return $newOfferNumber;
    }
}
