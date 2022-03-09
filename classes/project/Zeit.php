<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Posten.php');

class Zeit extends Posten {
    
    private $Stundenlohn = null;
    private $ZeitInMinuten = null;
	private $Kosten = null;
	private $discount = -1;
	private $beschreibung = null;

	protected $postenTyp = "zeit";
	protected $ohneBerechnung = false;
	protected $postennummer;

	function __construct($Stundenlohn, $ZeitInMinuten, $beschreibung, $discount) {
		$this->Stundenlohn = (float) $Stundenlohn;
		$this->ZeitInMinuten = (int) $ZeitInMinuten;
		$this->beschreibung = $beschreibung;

		$this->Kosten = $this->kalkulierePreis();

		if ($discount != 0 && $discount > 0 && $discount <= 100) {
			$this->discount = $discount;
		}
	}

	public function getHTMLData() {
		$html = "<div><span>Typ: {$this->postenTyp} </span><span>Stundenlohn: {$this->Stundenlohn}€ </span>";
		$html .= "<span>Zeit in Minuten: {$this->ZeitInMinuten} </span><span>Preis: {$this->bekommePreis()}€ </span></div>";
		return $html;
	}

	public function fillToArray($arr) {
		$arr['Postennummer'] = $this->postennummer;
		$arr['Preis'] = $this->bekommePreisTabelle();
		$arr['Stundenlohn'] = number_format($this->Stundenlohn, 2, ',', '') . "€";
		$arr['Anzahl'] = $this->ZeitInMinuten;
		$arr['MEH'] =  "min";
		$arr['Beschreibung'] = $this->beschreibung;
		$arr['Einkaufspreis'] = "-";
		$arr['type'] = "addPostenZeit";

		return $arr;
	}

	/* returns the price if no discount is applied, else calculates the discount and returns the according table */
	private function bekommePreisTabelle() {
		if ($this->discount != -1) {
			$originalPrice = number_format($this->kalkulierePreis(), 2, ',', '') . "€";
			$discount_table = "
				<table class=\"innerTable\">
					<tr>
						<td>Preis</td>
						<td>{$originalPrice}</td>
						<td>{$this->bekommePreis()}</td>
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
	
	public function bekommeEinzelPreis() {
		return $this->bekommePreis();
	}

	/*
	 * returns the price, discounts or other things are included
	 */
    public function bekommePreis() {
		if ($this->ohneBerechnung == true) {
			return 0;
		}

		$this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
		if ($this->discount != -1) {
			return round((float) $this->Kosten * (1 - ($this->discount / 100)), 2);
		}
		return round((float) $this->Kosten, 2);
    }

	public function bekommeDifferenz() {
		return $this->bekommePreis();
	}

	/*
	 * calculated price by hour wage and time, no discounts included
	 */
    private function kalkulierePreis() {
		$this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
        return round((float) $this->Kosten, 2);
	}
	
	public function getDescription() {
		return $this->beschreibung;
	}

	public function getEinheit() {
		return "min";
	}

	public function getWage() {
		return $this->Stundenlohn;
	}

	public function getQuantity() {
		return $this->ZeitInMinuten;
	}

	public function getOhneBerechnung() {
		return $this->ohneBerechnung;
	}

	public function calculateDiscount() {

	}

	public function storeToDB($auftragsNr) {
		$data = $this->fillToArray(array());
		$data['ohneBerechnung'] = 1;
		$data['Auftragsnummer'] = $auftragsNr;
		Posten::insertPosten("zeit", $data);
	}

}

?>