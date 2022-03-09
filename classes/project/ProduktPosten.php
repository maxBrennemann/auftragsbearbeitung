<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Posten.php');

class ProduktPosten extends Posten {
    
    private $Preis = 0.0;
	private $Einkaufspreis = 0.0;
	private $discount = -1;
    private $Bezeichnung = null;
	private $Beschreibung = null;
	private $Anzahl = 0;
	private $Marke = "";
	
	protected $postenTyp = "produkt";
	protected $ohneBerechnung = false;
	protected $postennummer;

	function __construct($Preis, $Bezeichnung, $Beschreibung, $Anzahl, $Einkaufspreis, $Marke, $discount) {
		$this->Preis = (float) $Preis;
		$this->Einkaufspreis = (float) $Einkaufspreis;
		$this->Bezeichnung = $Bezeichnung;
		$this->Beschreibung = $Beschreibung;
		$this->Anzahl = (int) $Anzahl;
		$this->Marke = $Marke;

		if ($discount != 0 && $discount > 0 && $discount <= 100) {
			$this->discount = $discount;
		}
	}

	public function getHTMLData() {
		$html = "<div><span>Typ: {$this->postenTyp} </span><span>Preis: {$this->bekommePreis()}� </span>";
		$html .= "<span>Bezeichnung: {$this->Bezeichnung} </span><span>Beschreibung: {$this->Beschreibung} </span></div>";
		return $html;
	}

	public function fillToArray($arr) {
		$arr['Postennummer'] = $this->postennummer;
		$arr['Preis'] = $this->bekommePreisTabelle();
		$arr['Bezeichnung'] = "<button class=\"postenButton\">Produkt</button>" . $this->Bezeichnung;
		$arr['Beschreibung'] = $this->Beschreibung;
		$arr['Anzahl'] = $this->Anzahl;
		$arr['Einkaufspreis'] = $this->Einkaufspreis;
		$arr['type'] = "addPostenProdukt";

		return $arr;
	}

	/* returns the price if no discount is applied, else calculates the discount and returns the according table */
	private function bekommePreisTabelle() {
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
			return number_format($this->bekommePreis(), 2, ',', '') . "€";
		}
	}

	public function storeToDB($auftragsNr) {
		$data = $this->fillToArray(array());
		$data['ohneBerechnung'] = 1;
		$data['Auftragsnummer'] = $auftragsNr;
		Posten::insertPosten("produkt", $data);
	}

	/* includes discount */
    public function bekommePreis() {
		if ($this->ohneBerechnung == true) {
			return 0;
		}
        return (float) $this->Preis * $this->Anzahl - $this->calculateDiscount();
	}

	public function bekommeEinzelPreis() {
		return $this->Preis;
	}
	
	public function bekommeDifferenz() {
		if ($this->ohneBerechnung == true) {
			return 0;
		}
        return (float) $this->bekommePreis() - $this->Einkaufspreis * $this->Anzahl;
	}

	public function calculateDiscount() {
		if ($this->discount != -1) {
			return (float) $this->Preis * $this->Anzahl * $this->discount;
		}
		return 0;
	}

	public function getDescription() {
		return $this->Beschreibung;
	}

	public function getEinheit() {
		return "Stk";
	}

	public function getQuantity() {
		return $this->Anzahl;
	}

}

?>