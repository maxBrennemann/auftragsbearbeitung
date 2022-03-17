<?php

class RechnungsPDF extends TCPDF {

    public function Footer() {
         // Position at 15 mm from bottom
         $this->SetY(-25);
         // Set font
         $this->SetFont('helvetica', 'I', 8);
         // Page number
         $this->Cell(0, 00, 'Seite '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 1, 'C', 0, '', 0, false, 'T', 'M');

         $this->Cell(0, 0, 'b-schriftung Brennemann ***REMOVED***, ***REMOVED***, ***REMOVED***', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

         $this->Cell(0, 0, 'Tel.: 09933/8474 Ust-ID-Nr. DE 127 788 188', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

         $this->Cell(0, 0, 'Bankverbindung: Sparkasse Niederbayern Mitte', 0, 1, 'C', 0, '', 0, false, 'T', 'M');	
         
         $this->Cell(0, 0, 'IBAN: DE36742500000100424589 BIC: BYLADEM1SRG', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
         
         $this->Cell(0, 0, 'Es gelten unsere Allgemeinen Geschäftsbedingungen (siehe www.b-schriftung.de) | Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    }

}

?>