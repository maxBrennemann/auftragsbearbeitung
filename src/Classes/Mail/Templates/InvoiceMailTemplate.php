<?php

namespace Src\Classes\Mail\Templates;

use Src\Classes\Project\Settings;

class InvoiceMailTemplate
{
    /**
     * @param array<string, string> $invoiceData
     * @return array{html: string, plain: string, subject: string}
     */
    public static function build(array $invoiceData): array
    {
        $subject = "Ihre Rechnung Nr. {$invoiceData["invoiceNumber"]}";
        $companyName = Settings::get("company.name");
        $htmlBody = "
            <p>Sehr geehrte Damen und Herren,</p>
            <p>anbei finden Sie Ihre Rechnung <strong>#{$invoiceData["invoiceNumber"]}</p>
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
