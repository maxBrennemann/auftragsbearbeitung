<?php

namespace Classes\Project;

class ProduktPosten extends Posten
{
    private $Preis = 0.0;
    private $Einkaufspreis = 0.0;
    private $discount = -1;
    private $Bezeichnung = null;
    private $Beschreibung = null;
    private $Anzahl = 0;
    private $Marke = "";
    private $isInvoice = false;

    protected $postenTyp = "produkt";
    protected $ohneBerechnung = false;
    protected int $postennummer;

    public function __construct($Preis, $Bezeichnung, $Beschreibung, $Anzahl, $Einkaufspreis, $Marke, $discount, $isInvoice, $freeOfCharge, int $position = 0)
    {
        $this->Preis = (float) $Preis;
        $this->Einkaufspreis = (float) $Einkaufspreis;
        $this->Bezeichnung = $Bezeichnung;
        $this->Beschreibung = $Beschreibung;
        $this->Anzahl = (int) $Anzahl;
        $this->Marke = $Marke;
        $this->ohneBerechnung = $freeOfCharge;

        $this->isInvoice = $isInvoice == 0 ? false : true;

        if ($discount != 0 && $discount > 0 && $discount <= 100) {
            $this->discount = $discount;
        }

        $this->position = $position;
    }

    public function fillToArray(array $arr): array
    {
        $arr['Postennummer'] = $this->postennummer;
        $arr['Preis'] = $this->bekommePreisTabelle();
        $arr['Bezeichnung'] = "<button class=\"btn-primary-small\">Produkt</button>" . $this->Bezeichnung;
        $arr['Beschreibung'] = $this->Beschreibung;
        $arr['Anzahl'] = $this->Anzahl;
        $arr['MEH'] = $this->getEinheit();
        $arr['Einkaufspreis'] = $this->bekommeEinkaufspreis_formatted();
        $arr['Gesamtpreis'] = $this->bekommePreis_formatted();
        $arr['type'] = "addPostenProdukt";

        return $arr;
    }

    /* returns the price if no discount is applied, else calculates the discount and returns the according table */
    private function bekommePreisTabelle(): string
    {
        if ($this->discount != -1) {
            $discountedPrice = number_format($this->bekommePreis(), 2, ',', '') . "€";
            $regularPrice = number_format($this->bekommePreis() + $this->calculateDiscount(), 2, ',', '') . "€";
            $discount_table = "
				<table class=\"innerTable\">
					<tr>
						<td>Preis</td>
						<td>{$regularPrice}</td>
						<td>{$discountedPrice}</td>
					</tr>
					<tr>
						<td>Rabatt</td>
						<td colspan=\"2\">{$this->discount}%</td>
					</tr>
				</table>";

            return $discount_table;
        } else {
            return $this->bekommeEinzelPreis_formatted();
        }
    }

    public function storeToDB(int $auftragsNr): void
    {
        $data = $this->fillToArray(array());
        $data['ohneBerechnung'] = 1;
        $data['Auftragsnummer'] = $auftragsNr;
        Posten::insertPosten("produkt", $data);
    }

    /* includes discount */
    public function bekommePreis()
    {
        if ($this->ohneBerechnung == true) {
            return 0;
        }
        return (float) $this->Preis * $this->Anzahl - $this->calculateDiscount();
    }

    public function bekommeEinzelPreis()
    {
        return $this->Preis;
    }

    public function bekommePreis_formatted(): string
    {
        return number_format($this->bekommePreis(), 2, ',', '') . ' €';
    }

    public function bekommeEinkaufspreis_formatted(): string
    {
        return number_format($this->Einkaufspreis, 2, ',', '') . ' €';
    }

    public function bekommeEinzelPreis_formatted(): string
    {
        return number_format($this->bekommeEinzelPreis(), 2, ',', '') . ' €';
    }

    public function bekommeDifferenz()
    {
        if ($this->ohneBerechnung == true) {
            return 0;
        }
        return (float) $this->bekommePreis() - $this->Einkaufspreis * $this->Anzahl;
    }

    public function getBrand()
    {
        return $this->Marke;
    }

    public function calculateDiscount()
    {
        if ($this->discount != -1) {
            return (float) $this->Preis * $this->Anzahl * $this->discount;
        }
        return 0;
    }

    public function getDescription()
    {
        return $this->Beschreibung;
    }

    public function getEinheit(): string
    {
        return "Stück";
    }

    public function getQuantity()
    {
        return $this->Anzahl;
    }

    public function isInvoice()
    {
        return $this->isInvoice;
    }
}
