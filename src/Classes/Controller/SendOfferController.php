<?php

namespace Src\Classes\Controller;

use Src\Classes\Mail\Mailer;
use Src\Classes\Mail\Templates\OfferMailTemplate;

class SendOfferController
{
    /**
     * @param array{email: string, offerNumber: int, attachment?: array<string, string>} $offerData
     * @return bool
     */
    public static function handle(array $offerData): bool
    {
        $mailer = new Mailer();
        $template = OfferMailTemplate::build($offerData);
        $attachment = $offerData["attachment"] ?? null;

        return $mailer->send(
            $offerData["email"],
            $template["subject"],
            $template["html"],
            $attachment,
            $template["plain"]
        );
    }
}
