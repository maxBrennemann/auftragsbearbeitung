<?php

namespace Classes\Project;

class ProduktPosten extends Posten
{
    private float $Preis = 0.0;
    private float $Einkaufspreis = 0.0;
    private int $discount = -1;
    private string $Bezeichnung;
    private string $Beschreibung;
    private int $Anzahl = 0;
    private string $Marke = "";
    private bool $isInvoice = false;

    protected string $postenTyp = "produkt";
    protected bool $ohneBerechnung = false;
    protected int $postennummer;

    public function __construct(float $Preis, string $Bezeichnung, string $Beschreibung, int $Anzahl, float $Einkaufspreis, string $Marke, int $discount, bool $isInvoice, bool $freeOfCharge, int $position = 0)
    {
        $this->Preis = $Preis;
        $this->Einkaufspreis = $Einkaufspreis;
        $this->Bezeichnung = $Bezeichnung;
        $this->Beschreibung = $Beschreibung;
        $this->Anzahl = $Anzahl;
        $this->Marke = $Marke;
        $this->ohneBerechnung = $freeOfCharge;

        $this->isInvoice = $isInvoice == 0 ? false : true;

        if ($discount != 0 && $discount > 0 && $discount <= 100) {
            $this->discount = $discount;
        }

        $this->position = $position;
    }

    /**
     * @param array<string, string> $arr
     * @return array<string, string>
     */
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
    public function bekommePreis(): float
    {
        if ($this->ohneBerechnung == true) {
            return 0;
        }
        return (float) $this->Preis * $this->Anzahl - $this->calculateDiscount();
    }

    public function bekommeEinzelPreis(): float
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

    public function bekommeDifferenz(): float
    {
        if ($this->ohneBerechnung == true) {
            return 0;
        }
        return (float) $this->bekommePreis() - $this->Einkaufspreis * $this->Anzahl;
    }

    public function getBrand(): string
    {
        return $this->Marke;
    }

    public function calculateDiscount(): float
    {
        if ($this->discount != -1) {
            return (float) $this->Preis * $this->Anzahl * $this->discount;
        }
        return 0;
    }

    public function getDescription(): string
    {
        return $this->Beschreibung;
    }

    public function getEinheit(): string
    {
        return "Stück";
    }

    public function getQuantity(): int
    {
        return $this->Anzahl;
    }

    public function isInvoice(): bool
    {
        return $this->isInvoice;
    }
}
