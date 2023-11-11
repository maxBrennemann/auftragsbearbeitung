<?php

require_once('vendor/autoload.php');

class RechnungsPDF extends TCPDF {

    public function Footer() {
         // Position at 15 mm from bottom
         $this->SetY(-25);
         // Set font
         $this->SetFont('helvetica', 'I', 8);
         // Page number
         $this->Cell(0, 00, 'Seite '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 1, 'C', 0, '', 0, false, 'T', 'M');

         $this->Cell(0, 0, $this->getImprint(), 0, 1, 'C', 0, '', 0, false, 'T', 'M');

         $this->Cell(0, 0, $this->getTel() . " " . $this->getUstIDNr(), 0, 1, 'C', 0, '', 0, false, 'T', 'M');

         $this->Cell(0, 0, $this->getBankverbindung(), 0, 1, 'C', 0, '', 0, false, 'T', 'M');	
         
         $this->Cell(0, 0, $this->getIBAN(), 0, 1, 'C', 0, '', 0, false, 'T', 'M');
         
         $this->Cell(0, 0, 'Es gelten unsere Allgemeinen Geschäftsbedingungen (siehe ' . $this->getWebsite() . ') | Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    }

    private function getBankverbindung() {
        return$_ENV["COMPANY_BANK"];
    }

    private function getIBAN() {
        return $_ENV["COMPANY_IBAN"];
    }

    private function getTel() {
        return $_ENV["COMPANY_TEL"];
    }

    private function getImprint() {
        return $_ENV["COMPANY_IMPRINT"];
    }

    private function getUstIDNr() {
        return $_ENV["COMPANY_USTIDNR"];
    }

    private function getWebsite() {
        return $_ENV["COMPANY_WEBSITE"];
    }

}

?>