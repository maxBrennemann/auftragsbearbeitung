<?php

namespace Src\Classes\Mail\Templates;

use Src\Classes\Project\Settings;

class OfferMailTemplate
{
    /**
     * @param array{email: string, offerNumber: int, attachment?: array<string, string>} $offerData
     * @return array{html: string, plain: string, subject: string}
     */
    public static function build(array $offerData): array
    {
        $subject = "Ihr Angebot Nr. {$offerData["offerNumber"]}";
        $companyName = Settings::get("company.name");
        $htmlBody = "
            <p>Sehr geehrte Damen und Herren,</p>
            <p>anbei finden Sie unser Angebot <strong>#{$offerData["offerNumber"]}</strong>.</p>
            <p>Mit freundlichen Grüßen, <br>{$companyName}</p>
            <img src=\"cid:logo\" width=\"120\" alt=\"{$companyName} Logo\" style=\"margin-top:8px;\">
        ";

        return [
            "subject" => $subject,
            "html" => $htmlBody,
            "plain" => strip_tags($htmlBody),
        ];
    }
}
