<?php

require_once('vendor/autoload.php');

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
        $today = date('d.m.y');

        /* header with customer information */
        $table = "
        <table cellpadding=\"6\">
            <tr>
                <td class=\"noBottom\" colspan=\"6\"></td>
                <td colspan=\"1\"><b>Datum</b></td>
                <td colspan=\"1\">{$today}</td>
            </tr>
            <tr>
                <td colspan=\"2\"><b>von Fa.</b></td>
                <td colspan=\"6\">{$variables['Firmenname']}</td>
            </tr>
            <tr>
                <td colspan=\"2\"><b>PLZ / Ort</b></td>
                <td colspan=\"2\">{$variables['plz']} {$variables['ort']}</td>
                <td colspan=\"2\"><b>Straße</b></td>
                <td colspan=\"2\">{$variables['strasse']} {$variables['hausnr']}</td>
            </tr>
            <tr>
                <td colspan=\"2\"><b>Ansprechpartner</b></td>
                <td colspan=\"2\">{$variables['Ansprechpartner']}</td>
                <td colspan=\"2\"><b>Tel. (Durchwahl)</b></td>
                <td colspan=\"2\">{$variables['TelefonFestnetz']}</td>
            </tr>
            <tr>
                <td colspan=\"2\"><b>Email</b></td>
                <td colspan=\"6\">{$variables['Email']}</td>
            </tr>
            <tr>
                <td colspan=\"2\"><b>Mobil-Nr. (Handy)</b></td>
                <td colspan=\"6\">{$variables['TelefonMobil']}</td>
            </tr>
        </table>
        <style>
            td {
                border-bottom: 1px solid black;
            }

            .noBottom {
                border-bottom: none;
            }
        </style>
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
            <table cellpadding=\"3\">
                <tr>
                    <td colspan=\"4\"><b>Beschreibung:</b></td>
                    <td colspan=\"2\"><b>Angenommen von</b></td>
                    <td colspan=\"2\">{$variables['AngenommenDurch']}</td>
                </tr>
                <tr>
                    <td colspan=\"4\" rowspan=\"4\">{$variables['Auftragsbeschreibung']}</td>
                    <td colspan=\"2\"><b>Kundennummer</b></td>
                    <td colspan=\"2\">{$variables['Kundennummer']}</td>
                </tr>
                <tr>
                    <td colspan=\"2\"><b>Auftragsnummer</b></td>
                    <td colspan=\"2\">{$variables['Auftragsnummer']}</td>
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
        $auftrags_daten = DBAccess::selectQuery("SELECT * FROM auftrag, `address`, kunde  WHERE auftrag.Kundennummer = kunde.Kundennummer AND Auftragsnummer = $nummer AND `address`.id_customer = auftrag.Kundennummer AND `address`.art = 1");

        $id = $auftrags_daten[0]["AngenommenDurch"];
        $angenommenDurch = DBAccess::selectQuery("SELECT Vorname, Nachname FROM mitarbeiter WHERE id = $id");
        $auftrags_daten[0]["AngenommenDurch"] = $angenommenDurch[0]["Vorname"] . " " . $angenommenDurch[0]["Nachname"];

        if ($auftrags_daten[0]["Fertigstellung"] == '0000-00-00') {
            $auftrags_daten[0]["Fertigstellung"] = "";
        }

        if ((int) $auftrags_daten[0]["Ansprechpartner"] == 0) {
            $auftrags_daten[0]["Ansprechpartner"] = $auftrags_daten[0]["Vorname"] . " " . $auftrags_daten[0]["Nachname"];
        } else {
            $nummer = (int) $auftrags_daten[0]["Ansprechpartner"];
            $name = DBAccess::selectQuery("SELECT Vorname, Nachname FROM ansprechpartner WHERE Nummer = $nummer")[0];
            $name = $name["Vorname"] . " " . $name["Nachname"];
            $auftrags_daten[0]["Ansprechpartner"] = $name;
        }

        $auftrags_daten = $auftrags_daten[0];
        return $auftrags_daten;

        /* SELECT 
                Auftragsnummer, kunde.Kundennummer, Auftragsbezeichnung, Auftragsbeschreibung, Datum, Termin, Firmenname, Straße, Hausnummer, Postleitzahl, Ort, kunde.Email, TelefonFestnetz, TelefonMobil, 
                case auftrag.Ansprechpartner
                    when 0 then CONCAT(kunde.Vorname, " ", kunde.Nachname)
                    else CONCAT(ansprechpartner.Vorname, " ", ansprechpartner.Nachname)
                end as test
            FROM auftrag, kunde, ansprechpartner
            WHERE 
                auftrag.Kundennummer = kunde.Kundennummer
                AND Auftragsnummer = 6
        */
    }

}

?>