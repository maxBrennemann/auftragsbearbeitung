<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Zeit extends Posten
{
    private float $Stundenlohn;
    private int $ZeitInMinuten;
    private float $Kosten;
    private int $discount = -1;
    private string $beschreibung;
    private bool $isInvoice = false;

    protected string $postenTyp = "zeit";
    protected bool $ohneBerechnung = false;
    protected int $postennummer;

    public function __construct(float $hourlyWage, int $timeInMinutes, string $description, int $discount, bool $isInvoice, bool $freeOfCharge, int $position = 0)
    {
        $this->Stundenlohn = $hourlyWage;
        $this->ZeitInMinuten = $timeInMinutes;
        $this->beschreibung = $description;

        $this->isInvoice = $isInvoice == 0 ? false : true;
        $this->ohneBerechnung = $freeOfCharge;

        $this->Kosten = $this->kalkulierePreis();

        if ($discount != 0 && $discount > 0 && $discount <= 100) {
            $this->discount = $discount;
        }

        $this->position = $position;
    }

    /**
     * @param array<string, mixed> $timeData
     * @return array<string, mixed>
     */
    public function fillToArray(array $timeData): array
    {
        $timeData["Postennummer"] = $this->postennummer;
        $timeData["Preis"] = $this->bekommePreisTabelle();
        $timeData["Stundenlohn"] = number_format($this->Stundenlohn, 2, ',', '') . "€";

        $getExtendedTimes = $this->bekommeErweiterteZeiterfassungTabelle();
        if ($getExtendedTimes !== null) {
            $timeData["extraData"] = $getExtendedTimes;
        }

        $timeData["Anzahl"] = $getExtendedTimes ?
            "<div class=\"flex items-center justify-between gap-4\">
                <span class=\"flex-grow\">{$this->ZeitInMinuten}</span>
                <div class=\"flex-shrink-0 min-w-[50px] text-right\">
                    <button class=\"btn-primary-small additional-data-btn ml-1\" data-id=\"{$this->postennummer}\">Mehr</button>
                </div>
            </div>"
            : $this->ZeitInMinuten;
        $timeData["quantityAbsolute"] = $this->ZeitInMinuten;
        $timeData["MEH"] =  "min";
        $timeData["Beschreibung"] = $this->beschreibung;
        $timeData["Einkaufspreis"] = "-";
        $timeData["Gesamtpreis"] = $this->bekommePreis_formatted();
        $timeData["type"] = "addPostenZeit";
        $timeData["Bezeichnung"] = "<button class=\"btn-primary-small\">Zeit</button>";

        return $timeData;
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
        $times = DBAccess::selectQuery($query, [
                "idPosten" => $this->postennummer,
        ]);

        if (empty($times)) {
            return null;
        }

        $options = [
            "hideOptions" => ["all"],
        ];

        $header = [
            "columns" => [
                "from",
                "to",
                "date",
            ],
            "names" => [
                "Von",
                "Bis",
                "Datum",
            ],
        ];

        return TableGenerator::create($times, $options, $header);
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

    public function getDescription(): string
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

    public function getQuantity(): float
    {
        return round($this->ZeitInMinuten / 60, 2);
    }

    public function getQuantityFormatted(): string
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

    public function calculateDiscount(): float
    {
        return 0;
    }

    public function storeToDB(int $auftragsNr): void
    {
        $data = $this->fillToArray([]);
        $data['ohneBerechnung'] = 1;
        $data['Auftragsnummer'] = $auftragsNr;
        Posten::insertPosten("zeit", $data);
    }

    /**
     * @param int $postennummer
     * @return array<string, mixed>
     */
    public static function getPostenData(int $postennummer): array
    {
        $query = "SELECT Nummer, ZeitInMinuten, Stundenlohn, Beschreibung, ohneBerechnung, discount, isInvoice 
            FROM zeit, posten
            WHERE zeit.Postennummer = posten.Postennummer 
                AND posten.Postennummer = :postennummer";
        $result = DBAccess::selectQuery($query, [
            "postennummer" => $postennummer,
        ])[0];
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
            self::erweiterteZeiterfassung($zeiterfassung, $ids[1]);
        }

        $orderId = (int) Tools::get("id");
        if ($orderId == 0) {
            return;
        }

        $newOrder = new Auftrag($orderId);
        $price = $newOrder->preisBerechnen();

        $data = self::getOrderItem($orderId, $ids[0]);
        if ($data === false || !$data instanceof Zeit) {
            return;
        }

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
        $data = self::getPostenData($idItem);
        
        JSONResponseHandler::sendResponse($data);
    }

    public static function update(): void
    {
        $orderId = (int) Tools::get("id");
        $itemId = (int) Tools::get("itemId");
        $zeitInMinuten = (int) Tools::get("time");
        $stundenlohn = (int) Tools::get("wage");
        $beschreibung = (string) Tools::get("description");
        $ohneBerechnung = Tools::get("noPayment");
        $discount = (int) Tools::get("discount");
        $isInvoice = (int) Tools::get("addToInvoice");

        $query = "UPDATE posten SET 
                ohneBerechnung = :ohneBerechnung,
                discount = :discount,
                isInvoice = :addToInvoice
            WHERE Postennummer = :itemId";
        DBAccess::updateQuery($query, [
            "ohneBerechnung" => $ohneBerechnung,
            "discount" => $discount,
            "addToInvoice" => $isInvoice,
            "itemId" => $itemId,
        ]);

        $query = "UPDATE zeit SET
                ZeitInMinuten = :zeitInMinuten,
                Stundenlohn = :stundenlohn,
                Beschreibung = :beschreibung
            WHERE Postennummer = :itemId";
        DBAccess::updateQuery($query, [
            "zeitInMinuten" => $zeitInMinuten,
            "stundenlohn" => $stundenlohn,
            "beschreibung" => $beschreibung,
            "itemId" => $itemId,
        ]);

        /* erweiterte Zeiterfassung */
        $zeiterfassung = json_decode(Tools::get("times"), true);
        if (count($zeiterfassung) != 0) {
            $query = "DELETE FROM zeiterfassung WHERE id_zeit = :itemId";
            DBAccess::deleteQuery($query, ["itemId" => $itemId]);
            self::erweiterteZeiterfassung($zeiterfassung, $itemId);
        }

        if ($orderId == 0) {
            return;
        }

        $newOrder = new Auftrag($orderId);
        $price = $newOrder->preisBerechnen();

        $data = self::getOrderItem($orderId, $itemId);
        if ($data === false || !$data instanceof Zeit) {
            return;
        }

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
