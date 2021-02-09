<?php

require_once('.res/tcpdf/tcpdf.php');

class PDF_Auftrag {

    /*
     * creates empty pdf, then adds customer data to heading,
     * then vehicle info is loaded if available
     * after that basic information about the order is shown
     */
    public static function getPDF($id_auftrag) {
        $pdf = new TCPDF('p', 'mm', 'A4');
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor("Auftragsbearbeitung");
        $pdf->SetTitle("Auftragsblatt");
        $pdf->SetSubject("Auftragsblatt für Auftrag");
        
        //$pdf->AddFont('arial');
        $pdf->SetFont('dejavusans ', "", 10);
        $pdf->AddPage();

        $fileName = Link::getResourcesLink("auftrag_pdf.htm", "html", false);
        $html = file_get_contents_utf8($fileName);

        $variables = self::getData($id_auftrag);
        //var_dump($variables);

        /* header with customer information */
        $table = "
        <table>
            <tr>
                <td colspan=\"1\">von Fa.</td>
                <td colspan=\"5\">{$variables['Firmenname']}</td>
            </tr>
            <tr>
                <td colspan=\"1\">PLZ / Ort</td>
                <td colspan=\"2\">{$variables['Postleitzahl']} {$variables['Ort']}</td>
                <td colspan=\"1\">Straße</td>
                <td colspan=\"2\">{$variables['Straße']} {$variables['Hausnummer']}</td>
            </tr>
            <tr>
                <td>Ansprechpartner</td>
                <td>{$variables['Ansprechpartner']}</td>
                <td>Tel. (Durchwahl)</td>
                <td>{$variables['TelefonFestnetz']}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{$variables['Email']}</td>
            </tr>
            <tr>
                <td>Mobil-Nr. (Handy)</td>
                <td>{$variables['TelefonMobil']}</td>
            </tr>
        </table>
        ";

        $pdf->writeHTML($table);

        /* vehicle */
        $fahrzeug_query = "SELECT Fahrzeug, Kennzeichen FROM fahrzeuge, fahrzeuge_auftraege WHERE fahrzeuge.Nummer = fahrzeuge_auftraege.id_fahrzeug AND fahrzeuge_auftraege.id_auftrag = $id_auftrag";
        $fahrzeuge = DBAccess::selectQuery($fahrzeug_query);

        foreach ($fahrzeuge as $f) {
            $table = "
                <table>
                    <tr>
                        <td colspan=\"1\">Fahrzeug</td>
                        <td colspan=\"2\">{$f['Fahrzeug']}</td>
                        <td>Kennzeichen</td>
                        <td colspan=\"2\">{$f['Kennzeichen']}</td>
                    </tr>
                </table>
            ";

            $pdf->writeHTML($table);
        }

        /* order information */
        $table = "
            <table>
                <tr>
                    <td colspan=\"3\">Beschreibung:</td>
                    <td colspan=\"2\">Angenommen Durch</td>
                    <td colspan=\"1\">{$variables['AngenommenDurch']}</td>
                </tr>
                <tr>
                    <td rowspan=\"4\" colspan=\"3\">{$variables['Auftragsbeschreibung']}</td>
                    <td colspan=\"2\">Kundennummer</td>
                    <td colspan=\"1\">{$variables['Kundennummer']}</td>
                </tr>
                <tr>
                    <td colspan=\"2\">Auftragsnummer</td>
                    <td colspan=\"1\">{$variables['Auftragsnummer']}</td>
                </tr>
            </table>
        ";
        $pdf->writeHTML($table);

        $pdf->Output();
    }

    private static function file_get_contents_utf8($fn) {
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }
    
    /*
     * von FillForm.php kopiert, muss später überarbeitet werden
    */
    private static function getData($nummer) {
        $auftrags_daten = DBAccess::selectQuery("SELECT * FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Auftragsnummer = {$nummer}");

        $id = $auftrags_daten[0]["AngenommenDurch"];
        $angenommenDurch = DBAccess::selectQuery("SELECT Vorname, Nachname FROM mitarbeiter WHERE id = $id");
        $auftrags_daten[0]["AngenommenDurch"] = $angenommenDurch[0]["Vorname"] . " " . $angenommenDurch[0]["Nachname"];

        if ($auftrags_daten[0]["Fertigstellung"] == '0000-00-00') {
            $auftrags_daten[0]["Fertigstellung"] = "";
        }

        $auftrags_daten = $auftrags_daten[0];
        return $auftrags_daten;
    }

}

?>