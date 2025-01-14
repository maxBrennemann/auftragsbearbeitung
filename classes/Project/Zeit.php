<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Zeit extends Posten
{

	private $Stundenlohn = null;
	private $ZeitInMinuten = null;
	private $Kosten = null;
	private $discount = -1;
	private $beschreibung = null;
	private $isInvoice = false;

	private $internalZeitNumber = 0;

	protected $postenTyp = "zeit";
	protected $ohneBerechnung = false;
	protected $postennummer;

	function __construct($Stundenlohn, $ZeitInMinuten, $beschreibung, $discount, $isInvoice)
	{
		$this->Stundenlohn = (float) $Stundenlohn;
		$this->ZeitInMinuten = (int) $ZeitInMinuten;
		$this->beschreibung = $beschreibung;

		$this->isInvoice = $isInvoice == 0 ? false : true;

		$this->Kosten = $this->kalkulierePreis();

		if ($discount != 0 && $discount > 0 && $discount <= 100) {
			$this->discount = $discount;
		}
	}

	public function getHTMLData()
	{
		$html = "<div><span>Typ: {$this->postenTyp} </span><span>Stundenlohn: {$this->Stundenlohn}€ </span>";
		$html .= "<span>Zeit in Minuten: {$this->ZeitInMinuten} </span><span>Preis: {$this->bekommePreis()}€ </span></div>";
		return $html;
	}

	public function fillToArray($arr)
	{
		$arr['Postennummer'] = $this->postennummer;
		$arr['Preis'] = $this->bekommePreisTabelle();
		$arr['Stundenlohn'] = number_format($this->Stundenlohn, 2, ',', '') . "€";
		$arr['Anzahl'] = $this->bekommeErweiterteZeiterfassungTabelle();
		$arr['MEH'] =  "min";
		$arr['Beschreibung'] = $this->beschreibung;
		$arr['Einkaufspreis'] = "-";
		$arr['Gesamtpreis'] = $this->bekommePreis_formatted();
		$arr['type'] = "addPostenZeit";
		$arr['Bezeichnung'] = "<button class=\"postenButton\">Zeit</button>";

		return $arr;
	}

	public function setSpecificNumber($number)
	{
		$this->internalZeitNumber = (int) $number;
	}

	private function bekommeErweiterteZeiterfassungTabelle()
	{
		$erwZeit = DBAccess::selectQuery("SELECT id FROM zeiterfassung WHERE id_zeit = $this->internalZeitNumber");
		if ($erwZeit == null) {
			return $this->ZeitInMinuten;
		} else {
			$zeiten = DBAccess::selectQuery("SELECT CONCAT(LPAD(FLOOR(`from_time` / 60), 2, '0'), ':', LPAD(`from_time` MOD 60, 2, '0')) AS von, CONCAT(LPAD(FLOOR(`to_time` / 60), 2, '0'), ':', LPAD(`to_time` MOD 60, 2, '0')) AS bis, IF(`date` IS NULL, 'kein Datum', `date`) AS datum FROM zeiterfassung WHERE id_zeit = $this->internalZeitNumber");
			$entries = "";
			foreach ($zeiten as $zeit) {
				$entries .=
					"<tr>
						<td>{$zeit["von"]}</td>
						<td>{$zeit["bis"]}</td>
						<td>{$zeit["datum"]}</td>
					</tr>";
			}

			$html = "
			<span>{$this->ZeitInMinuten}</span>
			<br>
			<table class=\"innerTable\">
				<tr>
					<th>Von</th>
					<th>Bis</th>
					<th>Datum</th>
				</tr>
				{$entries}
			</table>";
			return $html;
		}
	}

	/* returns the price if no discount is applied, else calculates the discount and returns the according table */
	private function bekommePreisTabelle()
	{
		if ($this->discount != -1) {
			$originalPrice = number_format($this->kalkulierePreis(), 2, ',', '') . "€";
			$discount_table = "
				<table class=\"innerTable\">
					<tr>
						<td>Preis</td>
						<td>{$originalPrice}</td>
						<td>{$this->bekommePreis_formatted()}</td>
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

	public function bekommeEinzelPreis()
	{
		return $this->Stundenlohn;
	}

	/*
	 * returns the price, discounts or other things are included
	 */
	public function bekommePreis()
	{
		if ($this->ohneBerechnung == true) {
			return 0;
		}

		$this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
		if ($this->discount != -1) {
			return round((float) $this->Kosten * (1 - ($this->discount / 100)), 2);
		}
		return round((float) $this->Kosten, 2);
	}

	public function bekommePreis_formatted()
	{
		return number_format($this->bekommePreis(), 2, ',', '') . ' €';
	}

	public function bekommeEinzelPreis_formatted()
	{
		return number_format($this->Stundenlohn, 2, ',', '') . ' €';
	}

	public function bekommeDifferenz()
	{
		return $this->bekommePreis();
	}

	/*
	 * calculated price by hour wage and time, no discounts included
	 */
	private function kalkulierePreis()
	{
		$this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
		return round((float) $this->Kosten, 2);
	}

	public function getDescription()
	{
		return $this->beschreibung;
	}

	public function getEinheit()
	{
		return "Stunden";
	}

	public function getWage()
	{
		return $this->Stundenlohn;
	}

	public function getQuantity()
	{
		$zeitInStunden = round($this->ZeitInMinuten / 60, 2);
		return number_format($zeitInStunden, 2, ',', '');
	}

	public function getOhneBerechnung()
	{
		return $this->ohneBerechnung;
	}

	public function isInvoice()
	{
		return $this->isInvoice;
	}

	public function calculateDiscount() {}

	public function storeToDB($auftragsNr)
	{
		$data = $this->fillToArray(array());
		$data['ohneBerechnung'] = 1;
		$data['Auftragsnummer'] = $auftragsNr;
		Posten::insertPosten("zeit", $data);
	}

	public static function getPostenData($postennummer)
	{
		$query = "SELECT Nummer, ZeitInMinuten, Stundenlohn, Beschreibung, ohneBerechnung, discount, isInvoice FROM zeit, posten WHERE zeit.Postennummer = posten.Postennummer AND posten.Postennummer = $postennummer";
		$result = DBAccess::selectQuery($query)[0];
		$zeitid = $result["Nummer"];
		$queryTimeTable = "SELECT from_time, to_time, `date` FROM zeiterfassung WHERE id_zeit =  $zeitid";
		$resultTimeTable = DBAccess::selectQuery($queryTimeTable);

		$data =  [
			"time" => $result["ZeitInMinuten"],
			"wage" => $result["Stundenlohn"],
			"description" => $result["Beschreibung"],
			"notcharged" => $result["ohneBerechnung"],
			"isinvoice" => $result["isInvoice"],
			"discount" => $result["discount"],
			"timetable" => $resultTimeTable
		];

		return $data;
	}

	public static function erweiterteZeiterfassung($data, $id)
	{
		$db_array = array();

		foreach ($data as $timeEntry) {
			$from = self::timeString_toInt($timeEntry["start"]);
			$to = self::timeString_toInt($timeEntry["end"]);
			$date = $timeEntry["date"];

			if ($date == "") {
				array_push($db_array, [$id, $from, $to, "null" => $date]);
			} else {
				array_push($db_array, [$id, $from, $to, $date]);
			}
		}

		DBAccess::insertMultiple("INSERT INTO zeiterfassung (id_zeit, from_time, to_time, `date`) VALUES ", $db_array);
	}

	private static function timeString_toInt($timeString)
	{
		$timeParts = explode(":", $timeString);
		if (sizeof($timeParts) != 2)
			return -1;

		$timeInInt = (int) $timeParts[0] * 60 + (int) $timeParts[1];

		return $timeInInt;
	}

	public static function add() {
		$data = [];
		$data['ZeitInMinuten'] = Tools::get("time");
		$data['Stundenlohn'] = Tools::get("wage");
		$data['Beschreibung'] = Tools::get("description");
		$data['Auftragsnummer'] = Tools::get("id");
		$data['ohneBerechnung'] = Tools::get("noPayment");
		$data['discount'] = (int) Tools::get("discount");
		$data['addToInvoice'] = (int) Tools::get("addToInvoice");

		$isOverwrite = Tools::get("overwrite");
		if (isset($isOverwrite) && (int) $isOverwrite == 1) {
			$_SESSION['overwritePosten'] = false;
		}

		$ids = Posten::insertPosten("zeit", $data);

		/* erweiterte Zeiterfassung */
		$zeiterfassung = json_decode(Tools::get("times"), true);
		if (count($zeiterfassung) != 0) {
			Zeit::erweiterteZeiterfassung($zeiterfassung, $ids[1]);
		}

		$_SESSION['overwritePosten'] = false;

		$newOrder = new Auftrag(Tools::get("id"));
		$price = $newOrder->preisBerechnen();

		JSONResponseHandler::sendResponse([
			"price" => $price,
		]);
	}

}
