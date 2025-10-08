<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Leistung extends Posten
{
    private float $preis;
    private float $einkaufspreis;
    private int $discount = -1;
    private string $bezeichnung;
    private string $beschreibung;
    private int $leistungsnummer = 0;
    private bool $isInvoice = false;
    protected string $postenTyp = "leistung";
    protected bool $ohneBerechnung = false;
    protected int $postennummer;

    private float $quantity;
    private string $meh;

    public function __construct(int $leistungsnummer, string $beschreibung, float $speziefischerPreis, float $einkaufspreis, int $quantity, string $meh, int $discount, bool $isInvoice, bool $freeOfCharge, int $position = 0)
    {
        $this->beschreibung = $beschreibung;
        $this->preis = (float) $speziefischerPreis;
        $this->einkaufspreis = (float) $einkaufspreis;
        $this->leistungsnummer = $leistungsnummer;
        $this->ohneBerechnung = $freeOfCharge;

        $this->isInvoice = $isInvoice == 0 ? false : true;

        $data = DBAccess::selectQuery("SELECT Bezeichnung FROM leistung WHERE Nummer = $leistungsnummer");
        if ($data == null) {
            $this->bezeichnung = "";
        } else {
            $this->bezeichnung =  $data[0]["Bezeichnung"];
        }

        if ($discount > 0 && $discount <= 100) {
            $this->discount = $discount;
        }

        /* quantity is now a float */
        $this->quantity = (float) $quantity;
        $this->meh = $meh;
        $this->position = $position;
    }

    /* fills array for Postentable */
    public function fillToArray(array $arr): array
    {
        $arr['Postennummer'] = $this->postennummer;
        $arr['Preis'] = $this->bekommePreisTabelle();
        $arr['Bezeichnung'] = "<button class=\"btn-primary-small\">Leistung</button><br><span>{$this->bezeichnung}</span>";
        $arr['Beschreibung'] = $this->beschreibung;
        $arr['Einkaufspreis'] = number_format($this->einkaufspreis * $this->quantity, 2, ',', '') . "€<br><span style=\"font-size: 0.7em\">Einzelpreis: " . number_format($this->einkaufspreis, 2, ',', '') . "€</span><br>" . $this->getFiles($this->postennummer);
        $arr['Gesamtpreis'] = $this->bekommePreis_formatted();
        $arr['Leistungsnummer'] = $this->leistungsnummer;
        $arr['Anzahl'] = $this->quantity;
        $arr['MEH'] = $this->meh;
        $arr['type'] = "addPostenLeistung";

        return $arr;
    }

    /* returns the price if no discount is applied, else calculates the discount and returns the according table */
    private function bekommePreisTabelle(): string
    {
        $price_formatted = number_format($this->preis, 2, ',', '') . "€";

        if ($this->discount != -1) {
            $discountedPrice = number_format($this->preis - $this->preis * ($this->discount / 100), 2, ',', '') . "€";
            $discount_table = "
				<table class=\"innerTable\">
					<tr>
						<td>Preis</td>
						<td>{$price_formatted}</td>
						<td>{$discountedPrice}</td>
					</tr>
					<tr>
						<td>Rabatt</td>
						<td colspan=\"2\">{$this->discount}%</td>
					</tr>
				</table>";

            return $discount_table;
        } else {
            return $price_formatted;
        }
    }

    public function storeToDB(int $auftragsNr): void
    {
        $data = $this->fillToArray([]);
        $data['ohneBerechnung'] = 1;
        $data['Auftragsnummer'] = $auftragsNr;
        Posten::insertPosten("leistung", $data);
    }

    public function bekommePreis(): float
    {
        if ($this->ohneBerechnung == true) {
            return 0;
        }

        /*
         * if discount needs to be applied
         */
        if ($this->discount != -1) {
            return (float) $this->preis * $this->quantity * (1 - ($this->discount / 100));
        }

        return (float) $this->preis * $this->quantity;
    }

    public function bekommeEinzelPreis(): float
    {
        return $this->preis;
    }

    public function bekommePreis_formatted(): string
    {
        return number_format($this->bekommePreis(), 2, ',', '') . ' €';
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
        return (float) ($this->bekommePreis() - $this->bekommeEKPreis());
    }

    public function getOhneBerechnung(): bool
    {
        return $this->ohneBerechnung;
    }

    public function bekommeEKPreis(): float
    {
        return $this->einkaufspreis * $this->quantity;
    }

    public function calculateDiscount(): float
    {
        return 0;
    }

    public function getDescription(): string
    {
        return $this->beschreibung;
    }

    public function getEinheit(): string
    {
        return $this->meh;
    }

    public static function bearbeitungsschritteHinzufuegen(int $leistungsnummer, int $auftragsnummer): void
    {
        $schritte = DBAccess::selectQuery("SELECT * FROM schritte_vordefiniert WHERE Leistungsnummer = $leistungsnummer");

        foreach ($schritte as $schritt) {
            $data = [];
            $data['Bezeichnung'] = $schritt['bez'];
            $data['Datum'] = date("Y-m-d");
            $data['Priority'] = 1;
            $data['Auftragsnummer'] = $auftragsnummer;
            Step::insertStep($data);
        }
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getQuantityFormatted(): string
    {
        return (string) $this->quantity;
    }

    public function isInvoice(): bool
    {
        return $this->isInvoice;
    }

    /**
     * @param int $postennummer
     * @return array<string, string>
     */
    public static function getPostenData(int $postennummer): array
    {
        $query = "SELECT Nummer, Beschreibung, `Einkaufspreis`, SpeziefischerPreis, meh, qty, Leistungsnummer, ohneBerechnung, discount, isInvoice FROM leistung_posten, posten WHERE leistung_posten.Postennummer = posten.Postennummer AND posten.Postennummer = $postennummer";
        $result = DBAccess::selectQuery($query)[0];

        $data =  [
            "buyingprice" => $result["Einkaufspreis"],
            "price" => $result["SpeziefischerPreis"],
            "unit" => $result["meh"],
            "quantity" => $result["qty"],
            "type" => $result["Leistungsnummer"],
            "description" => $result["Beschreibung"],
            "notcharged" => $result["ohneBerechnung"],
            "isinvoice" => $result["isInvoice"],
            "discount" => $result["discount"],
        ];

        return $data;
    }

    public static function add(): void
    {
        $orderId = (int) Tools::get("id");

        $data = [];
        $data['Leistungsnummer'] = (int) Tools::get("lei");
        $data['Beschreibung'] = (string) Tools::get("bes");
        $data['Auftragsnummer'] = $orderId;
        $data['ohneBerechnung'] = Tools::get("ohneBerechnung");
        $data['discount'] = (int) Tools::get("discount");
        $data['MEH'] = Tools::get("meh");
        $data['addToInvoice'] = (int) Tools::get("addToInvoice");

        $data['Einkaufspreis'] = (float) Tools::get("ekp");
        $data['SpeziefischerPreis'] = (float) Tools::get("pre");
        $data['anzahl'] = (float) Tools::get("anz");

        $ids = Posten::insertPosten("leistung", $data);

        $newOrder = new Auftrag($orderId);
        $price = $newOrder->preisBerechnen();

        /* TODO: simplify this by helper function */
        $data = Posten::getOrderItems($orderId);
        $data = array_filter($data, fn($item) => $item->getPostennummer() == $ids[0]);
        $data = reset($data);
        if ($data === false || !$data instanceof Leistung) {
            return;
        }

        $item = [];
        $item["position"] = $data->getPosition();
        $item["price"] = $data->bekommeEinzelPreis();
        $item["totalPrice"] = $data->bekommePreis();

        $data = $data->fillToArray([]);
        $item["id"] = $data["Postennummer"];
        $item["name"] = $data["Bezeichnung"];
        $item["description"] = $data["Beschreibung"];
        $item["price"] = $data["Preis"];
        $item["quantity"] = $data["Anzahl"];
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

        $query = "DELETE FROM leistung_posten WHERE Postennummer = :idItem;";
        DBAccess::deleteQuery($query, [
            "idItem" => $idItem,
        ]);
    }
}
