<?php

namespace Classes\Project;

use Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Zeit extends Posten
{
    private float $Stundenlohn;
    private int $ZeitInMinuten;
    private $Kosten = null;
    private $discount = -1;
    private string $beschreibung = null;
    private bool $isInvoice = false;

    protected string $postenTyp = "zeit";
    protected bool $ohneBerechnung = false;
    protected int $postennummer;

    public function __construct($Stundenlohn, $ZeitInMinuten, $beschreibung, $discount, $isInvoice, $freeOfCharge, int $position = 0)
    {
        $this->Stundenlohn = (float) $Stundenlohn;
        $this->ZeitInMinuten = (int) $ZeitInMinuten;
        $this->beschreibung = $beschreibung;

        $this->isInvoice = $isInvoice == 0 ? false : true;
        $this->ohneBerechnung = $freeOfCharge;

        $this->Kosten = $this->kalkulierePreis();

        if ($discount != 0 && $discount > 0 && $discount <= 100) {
            $this->discount = $discount;
        }

        $this->position = $position;
    }

    public function fillToArray(array $arr): array
    {
        $arr['Postennummer'] = $this->postennummer;
        $arr['Preis'] = $this->bekommePreisTabelle();
        $arr['Stundenlohn'] = number_format($this->Stundenlohn, 2, ',', '') . "€";

        $getExtendedTimes = $this->bekommeErweiterteZeiterfassungTabelle();
        if ($getExtendedTimes) {
            $arr['extraData'] = $getExtendedTimes;
        }

        $arr['Anzahl'] = $getExtendedTimes ?
            $this->ZeitInMinuten . '<button class="info-button ml-1" data-id="' . $this->postennummer . '"></button>'
            : $this->ZeitInMinuten;
        $arr['MEH'] =  "min";
        $arr['Beschreibung'] = $this->beschreibung;
        $arr['Einkaufspreis'] = "-";
        $arr['Gesamtpreis'] = $this->bekommePreis_formatted();
        $arr['type'] = "addPostenZeit";
        $arr['Bezeichnung'] = "<button class=\"btn-primary-small\">Zeit</button>";

        return $arr;
    }

    private function bekommeErweiterteZeiterfassungTabelle(): string|null
    {
        $query = "SELECT CONCAT(
					LPAD(FLOOR(`from_time` / 60), 2, '0'), ':', 
					LPAD(`from_time` MOD 60, 2, '0')) AS `from`, 
				CONCAT(
					LPAD(FLOOR(`to_time` / 60), 2, '0'), ':', 
					LPAD(`to_time` MOD 60, 2, '0')) AS `to`, 
				IF(`date` IS NULL, 'kein Datum', `date`) AS `date` 
			FROM zeiterfassung
			JOIN zeit ON zeit.Nummer = id_zeit
			WHERE zeit.Postennummer = :idPosten";
        $times = DBAccess::selectQuery(
            $query,
            [
                "idPosten" => $this->postennummer,
            ]
        );

        if (count($times) == 0) {
            return null;
        }

        return TemplateController::getTemplate("extendedTimes", [
            "times" => $times,
        ]);
    }

    /* returns the price if no discount is applied, else calculates the discount and returns the according table */
    private function bekommePreisTabelle(): string
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

    public function bekommeEinzelPreis(): float
    {
        return $this->Stundenlohn;
    }

    /*
     * returns the price, discounts or other things are included
     */
    public function bekommePreis(): float
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

    public function bekommePreis_formatted(): string
    {
        return number_format($this->bekommePreis(), 2, ',', '') . ' €';
    }

    public function bekommeEinzelPreis_formatted(): string
    {
        return number_format($this->Stundenlohn, 2, ',', '') . ' €';
    }

    public function bekommeDifferenz(): float
    {
        return $this->bekommePreis();
    }

    /*
     * calculated price by hour wage and time, no discounts included
     */
    private function kalkulierePreis(): float
    {
        $this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
        return round((float) $this->Kosten, 2);
    }

    public function getDescription()
    {
        return $this->beschreibung;
    }

    public function getEinheit(): string
    {
        return "Stunden";
    }

    public function getWage(): float
    {
        return $this->Stundenlohn;
    }

    public function getQuantity(): string
    {
        $zeitInStunden = round($this->ZeitInMinuten / 60, 2);
        return number_format($zeitInStunden, 2, ',', '');
    }

    public function getOhneBerechnung(): bool
    {
        return $this->ohneBerechnung;
    }

    public function isInvoice(): bool
    {
        return $this->isInvoice;
    }

    public function calculateDiscount(): void {}

    public function storeToDB(int $auftragsNr): void
    {
        $data = $this->fillToArray([]);
        $data['ohneBerechnung'] = 1;
        $data['Auftragsnummer'] = $auftragsNr;
        Posten::insertPosten("zeit", $data);
    }

    /**
     * @param int $postennummer
     * @return array{description: mixed, discount: mixed, isinvoice: mixed, notcharged: mixed, time: mixed, timetable: array, wage: mixed}
     */
    public static function getPostenData(int $postennummer): array
    {
        $query = "SELECT Nummer, ZeitInMinuten, Stundenlohn, Beschreibung, ohneBerechnung, discount, isInvoice FROM zeit, posten WHERE zeit.Postennummer = posten.Postennummer AND posten.Postennummer = $postennummer";
        $result = DBAccess::selectQuery($query)[0];
        $zeitid = $result["Nummer"];
        $queryTimeTable = "SELECT from_time, to_time, `date` FROM zeiterfassung WHERE id_zeit =  $zeitid";
        $resultTimeTable = DBAccess::selectQuery($queryTimeTable);

        $data = [
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

    /**
     * @param array<mixed, mixed> $values
     * @param int $id
     * @return void
     */
    public static function erweiterteZeiterfassung(array $values, int $id): void
    {
        $data = [];

        foreach ($values as $timeEntry) {
            $from = self::timeString_toInt($timeEntry["start"]);
            $to = self::timeString_toInt($timeEntry["end"]);
            $date = $timeEntry["date"];

            if ($from == -1 || $to == -1) {
                continue;
            }

            $data[] = [$id, $from, $to, $date == "" ? null : $date];
        }

        DBAccess::insertMultiple("INSERT INTO zeiterfassung (id_zeit, from_time, to_time, `date`) VALUES ", $data);
    }

    private static function timeString_toInt(string $timeString): int
    {
        $timeParts = explode(":", $timeString);
        if (sizeof($timeParts) != 2) {
            return -1;
        }

        $timeInInt = (int) $timeParts[0] * 60 + (int) $timeParts[1];

        return $timeInInt;
    }

    public static function add(): void
    {
        $data = [];
        $data["ZeitInMinuten"] = (int) Tools::get("time");
        $data["Stundenlohn"] = (int) Tools::get("wage");
        $data["Beschreibung"] = (string) Tools::get("description");
        $data["Auftragsnummer"] = Tools::get("id");
        $data["ohneBerechnung"] = Tools::get("noPayment");
        $data["discount"] = (int) Tools::get("discount");
        $data["addToInvoice"] = (int) Tools::get("addToInvoice");

        $ids = Posten::insertPosten("zeit", $data);

        /* erweiterte Zeiterfassung */
        $zeiterfassung = json_decode(Tools::get("times"), true);
        if (count($zeiterfassung) != 0) {
            Zeit::erweiterteZeiterfassung($zeiterfassung, $ids[1]);
        }

        $orderId = Tools::get("id");
        $newOrder = new Auftrag($orderId);
        $price = $newOrder->preisBerechnen();

        /* TODO: simplify this by helper function */
        $data = Posten::getOrderItems($orderId);
        $data = array_filter($data, fn($item) => $item->getPostennummer() == $ids[0]);
        $data = reset($data);

        $item = [];
        $item["position"] = $data->getPosition();
        $item["price"] = $data->bekommeEinzelPreis();
        $item["totalPrice"] = $data->bekommePreis();
        $item["quantity"] = $data->bekommeErweiterteZeiterfassungTabelle();

        $data = $data->fillToArray([]);
        $item["id"] = $data["Postennummer"];
        $item["name"] = $data["Bezeichnung"];
        $item["description"] = $data["Beschreibung"];
        $item["price"] = $data["Preis"];
        $item["unit"] = $data["MEH"];
        $item["totalPrice"] = $data["Gesamtpreis"];
        $item["purchasePrice"] = $data["Einkaufspreis"];

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "price" => $price,
            "data" => $item,
        ]);
    }

    public static function get(): void
    {
        $idItem = (int) Tools::get("itemId");
        // TODO: implement
    }

    public static function delete(): void
    {
        $idItem = (int) Tools::get("itemId");
        parent::delete();

        $query = "SELECT Nummer AS id FROM zeit WHERE Postennummer = :idItem;";
        $data = DBAccess::selectQuery($query, [
            "idItem" => $idItem,
        ]);

        if (empty($data)) {
            return;
        }

        $idTime = (int) $data[0]["id"];
        $query = "DELETE FROM zeiterfassung WHERE id_zeit = :idTime;";
        DBAccess::deleteQuery($query, [
            "idTime" => $idTime,
        ]);

        $query = "DELETE FROM zeit WHERE Postennummer = :idItem;";
        DBAccess::deleteQuery($query, [
            "idItem" => $idItem,
        ]);
    }
}
