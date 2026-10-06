<?php

namespace Src\Classes\Project;

enum OfferState: string {
    case Open = "open";
    case Accepted = "accepted";
    case Rejected = "rejected";
    case Expired = "expired";

    public function isFinal(): bool
    {
        return match($this) {
            self::Accepted, self::Rejected, self::Expired => true,
            default => false,
        };
    }
}
