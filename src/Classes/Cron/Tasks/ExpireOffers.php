<?php

namespace Src\Classes\Cron\Tasks;

use Src\Classes\Cron\Queueable;
use Src\Classes\Project\OfferState;
use Src\Classes\Project\OrderHistory;
use MaxBrennemann\PhpUtilities\DBAccess;

class ExpireOffers implements Queueable
{
    public static function handle(): void
    {
        $expired = DBAccess::selectQuery("SELECT id FROM offer WHERE `state` = :state AND valid_until IS NOT NULL AND valid_until < CURDATE();", [
            "state" => OfferState::Open->value,
        ]);

        foreach ($expired as $row) {
            $offerId = (int) $row["id"];

            DBAccess::updateQuery("UPDATE offer SET `state` = :state WHERE id = :offerId;", [
                "state" => OfferState::Expired->value,
                "offerId" => $offerId,
            ]);

            OrderHistory::add($offerId, $offerId, OrderHistory::TYPE_OFFER, OrderHistory::STATE_EXPIRED, "Angebot automatisch als abgelaufen markiert");
        }
    }
}
