<?php

/**
 * Die 5 Standard-Textbausteine, die Zahlungsbedingungen und der §19-UStG-Hinweis
 * waren bisher als String-Literale in Invoice.php/InvoicePDF.php eingebrannt und
 * damit nicht ohne Code-Deployment änderbar. Diese Migration überführt sie in
 * `pdf_texts` (gleiches Muster wie die bereits dort verwalteten Fußzeilen-Texte),
 * damit sie über die Einstellungen-Seite editierbar sind. Die `type`-Werte sind
 * bewusst keine Dokumenttypen (invoice/offer/...), damit sie nicht versehentlich
 * über TransactionPDF::Footer() mitgerendert werden.
 */
return new class () {

    private $queries = [
        "INSERT INTO pdf_texts (type, status, text) VALUES
            ('invoice_default_text', 'active', 'Zu den Bilddaten: Bei der Benutzung von Daten aus fremden Quellen richten sich die Nutzungsbedingungen über Verwendung und Weitergabe nach denen der jeweiligen Anbieter.'),
            ('invoice_default_text', 'active', 'Bitte beachten Sie, dass wir keine Haftung für eventuell entstehende Schäden übernehmen, die auf Witterungseinflüsse zurückzuführen sind (zerrissene Banner, herausgerissen Ösen o. Ä.). Sie als Kunde müssen entscheiden, wie die Banner konfektioniert werden sollen. Für die Art der Konfektionierung übernehmen wir keine Haftung. Wir übernehmen außerdem keine Haftung für unfachgerechte Montage der Banner.'),
            ('invoice_default_text', 'active', 'Pflegehinweise beachten: Keine Bleichmittel und Weichspüler verwenden. Nicht in den Trockner geben. Links gewendet waschen. Nicht über den Transfer bügeln. Nicht chemisch reinigen.'),
            ('invoice_default_text', 'active', 'Wir weisen darauf hin, dass Logos eventuell Bildrechte anderer berühren und wir hierfür keine Haftung übernehmen. Der Kunde garantiert uns Straffreiheit gegenüber einer eventuell geschädigten Partei im Fall einer Verletzung des Rechts des geistigen Eigentums und/ oder des Bildrechts und/ oder den durch eine solche Verletzung verursachten Schadens. Für einen eventuellen Fall solch einer Verletzung willigt der Kunde ein, uns in Höhe aller entstandenen Kosten (inkl. Anwaltkosten) zu entschädigen.'),
            ('invoice_default_text', 'active', 'Für angelieferte Textilien wird keine Garantie übernommen.'),
            ('invoice_payment_terms', 'active', 'Zahlbar sofort ohne weitere Abzüge.'),
            ('invoice_small_business_notice', 'active', 'Kein Ausweis der Umsatzsteuer gem. §19 UStG.');",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
