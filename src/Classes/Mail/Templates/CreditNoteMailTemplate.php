<?php

namespace Src\Classes\Mail\Templates;

use Src\Classes\Project\Settings;

class CreditNoteMailTemplate
{
    /**
     * @param array{email: string, label: string, creditNumber: int, invoiceNumber: int, attachment?: array<string, string>} $creditNoteData
     * @return array{html: string, plain: string, subject: string}
     */
    public static function build(array $creditNoteData): array
    {
        $label = $creditNoteData["label"];
        $subject = "$label Nr. {$creditNoteData["creditNumber"]} zur Rechnung Nr. {$creditNoteData["invoiceNumber"]}";
        $companyName = htmlspecialchars((string) Settings::get("company.name"));
        $htmlBody = "
            <p>Sehr geehrte Damen und Herren,</p>
            <p>anbei erhalten Sie unsere $label <strong>#{$creditNoteData["creditNumber"]}</strong> zur Rechnung <strong>#{$creditNoteData["invoiceNumber"]}</strong>. Details entnehmen Sie bitte dem beigefügten Dokument.</p>
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
