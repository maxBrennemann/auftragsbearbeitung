<?php

namespace Src\Classes\Mail\Templates;

use Src\Classes\Project\Settings;

class PaymentReminderMailTemplate
{
    /**
     * @param array{email: string, invoiceNumber: int, level: int, attachment?: array<string, string>} $reminderData
     * @return array{html: string, plain: string, subject: string}
     */
    public static function build(array $reminderData): array
    {
        $title = match ($reminderData["level"]) {
            1 => "Zahlungserinnerung",
            2 => "1. Mahnung",
            default => "2. Mahnung",
        };

        $subject = "$title zu Rechnung Nr. {$reminderData["invoiceNumber"]}";
        $companyName = Settings::get("company.name");
        $htmlBody = "
            <p>Sehr geehrte Damen und Herren,</p>
            <p>anbei erhalten Sie eine $title zu unserer Rechnung <strong>#{$reminderData["invoiceNumber"]}</strong>. Details entnehmen Sie bitte dem beigefügten Dokument.</p>
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
