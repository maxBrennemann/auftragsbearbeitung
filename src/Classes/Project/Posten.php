<?php

namespace Src\Classes\Project;

use Src\Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

abstract class Posten
{
    abstract protected function bekommePreis(): float;
    abstract protected function bekommeEinzelPreis(): float;
    abstract protected function bekommePreis_formatted(): string;
    abstract protected function bekommeEinzelPreis_formatted(): string;
    abstract protected function bekommeDifferenz(): float;
    abstract protected function calculateDiscount(): float;
    abstract protected function getOhneBerechnung(): bool;

    /**
     * @param array<string, mixed> $arr
     * @return array<string, string>
     */
    abstract protected function fillToArray(array $arr): array;
    abstract protected function getDescription(): string;
    abstract protected function getEinheit(): string;
    abstract protected function getQuantity(): int|float;
    abstract protected function getQuantityFormatted(): string;
    abstract protected function isInvoice(): bool;
    abstract protected function storeToDB(int $auftragsnummer): void;

    protected string $postenTyp;
    protected bool $ohneBerechnung = false;
    protected int $postennummer;
    protected int $position = 0;

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getPostennummer(): int
    {
        return $this->postennummer;
    }

    /**
     * @param int $orderId
     * @param bool $isInvoice
     * @param int $status
     * @return array<Leistung|ProduktPosten|Zeit>
     */
    public static function getOrderItems(int $orderId, bool $isInvoice = false, int $status = 0): array
    {
        $items = [];

        $invoiceQuery = "";
        if ($isInvoice) {
            $invoiceQuery = "AND isInvoice = $status";
        }
        $query = "SELECT 
				p.Postennummer as id, 
				Posten as `type`, 
				ohneBerechnung as free_of_charge, 
				discount, 
				isInvoice as is_invoice, 
				position,
				l.Beschreibung as l_description,
				l.SpeziefischerPreis as l_price,
				l.Einkaufspreis as l_purchase_price,
				l.qty as l_qty,
				l.meh as l_unit,
				l.Leistungsnummer as l_number,
				pc.marke as p_brand,
				pc.price as p_price,
				pc.purchasing_price as p_purchase_price,
				pc.description as p_description,
				pc.name as p_name,
				pc.amount as p_amount,
				z.ZeitInMinuten as z_time,
				z.Stundenlohn as z_wage,
				z.Beschreibung as z_description
			FROM posten p
			LEFT JOIN leistung_posten l
				ON p.Postennummer = l.Postennummer
			LEFT JOIN produkt_posten pp
				ON p.Postennummer = pp.Postennummer
			LEFT JOIN zeit z
				ON p.Postennummer = z.Postennummer
			LEFT JOIN product_compact pc 
				ON p.Postennummer = pc.postennummer
			WHERE Auftragsnummer = :orderId
				$invoiceQuery
			ORDER BY p.position;";

        $data = DBAccess::selectQuery($query, [
            "orderId" => $orderId,
        ]);

        foreach ($data as $row) {
            $type = $row["type"];
            $item = null;
            switch ($type) {
                case "zeit":
                    $item = new Zeit(
                        (float) $row["z_wage"],
                        (int) $row["z_time"],
                        $row["z_description"],
                        (int) $row["discount"],
                        $row["is_invoice"] == "1",
                        $row["free_of_charge"] == "1",
                        (int) $row["position"],
                    );
                    break;
                case "leistung":
                    $item = new Leistung(
                        (int) $row["l_number"],
                        $row["l_description"],
                        (float) $row["l_price"],
                        (float) $row["l_purchase_price"],
                        (int) $row["l_qty"],
                        $row["l_unit"],
                        (int) $row["discount"],
                        $row["is_invoice"] == "1",
                        $row["free_of_charge"] == "1",
                        (int) $row["position"],
                    );
                    break;
                case "product":
                    $item = new ProduktPosten(
                        (float) $row["p_price"],
                        $row["p_name"],
                        $row["p_description"],
                        (int) $row["p_amount"],
                        (float) $row["p_purchase_price"],
                        $row["p_brand"],
                        (int) $row["discount"],
                        $row["is_invoice"] == "1",
                        $row["free_of_charge"] == "1",
                        (int) $row["position"],
                    );
                    break;
                case "compact":
                    $item = new ProduktPosten(
                        (float) $row["p_price"],
                        $row["p_name"],
                        $row["p_description"],
                        (int) $row["p_amount"],
                        (float) $row["p_purchase_price"],
                        $row["p_brand"],
                        (int) $row["discount"],
                        $row["is_invoice"] == "1",
                        $row["free_of_charge"] == "1",
                        (int) $row["position"],
                    );
                    break;
                default:
                    continue 2;
            }
            $item->postennummer = (int) $row["id"];
            $items[] = $item;
        }

        return $items;
    }

    protected static function getOrderItem(int $orderId, int $postenId): Leistung|ProduktPosten|Zeit|false
    {
        $data = Posten::getOrderItems($orderId);
        $data = array_filter($data, 
            fn($item) => $item->getPostennummer() == $postenId);
        return reset($data);
    }

    /**
     * @param string $type
     * @param array<string, mixed> $data
     * @return int[]
     */
    public static function insertPosten(string $type, array $data): array
    {
        $auftragsnummer = (int) $data['Auftragsnummer'];
        $subPosten = 0;

        $ohneBerechnung = $data['ohneBerechnung'];
        $discount = $data['discount'] == null ? 0 : $data['discount'];
        $addToInvoice = $data['addToInvoice'] == null ? 0 : $data['addToInvoice'];

        $postennummer = DBAccess::insertQuery("INSERT INTO posten (Auftragsnummer, Posten, ohneBerechnung, discount, isInvoice, position) 
			SELECT :auftragsnummer, :type, :ohneBerechnung, :discount, :addToInvoice, count(*) + 1 
			FROM posten 
			WHERE Auftragsnummer = :auftragsnummer_check", [
            "auftragsnummer" => $auftragsnummer,
            "type" => $type,
            "ohneBerechnung" => $ohneBerechnung,
            "discount" => $discount,
            "addToInvoice" => $addToInvoice,
            "auftragsnummer_check" => $auftragsnummer,
        ]);

        switch ($type) {
            case "zeit":
                $zeit = $data['ZeitInMinuten'];
                $lohn = $data['Stundenlohn'];
                $desc = $data['Beschreibung'];

                $subPosten = DBAccess::insertQuery("INSERT INTO zeit (Postennummer, ZeitInMinuten, Stundenlohn, Beschreibung) VALUES ($postennummer, $zeit, $lohn, '$desc')");
                break;
            case "leistung":
                $lei = $data['Leistungsnummer'];
                $bes = $data['Beschreibung'];
                $ekp = $data['Einkaufspreis'];
                $pre = $data['SpeziefischerPreis'];
                $anz = $data['anzahl'];
                $meh = $data['MEH'];

                $subPosten = DBAccess::insertQuery("INSERT INTO leistung_posten (Leistungsnummer, Postennummer, Beschreibung, Einkaufspreis, SpeziefischerPreis, meh, qty) VALUES($lei, $postennummer, '$bes', '$ekp', '$pre', '$meh', '$anz')");
                break;
            case "produkt":
                $amount = $data['amount'];
                $prodId = $data['prodId'];
                $subPosten = DBAccess::insertQuery("INSERT INTO produkt_posten (Produktnummer, Postennummer, Anzahl) VALUES ($prodId, $postennummer, $amount)");
                break;
            case "compact":
                $amount = $data['amount'];
                $marke = $data['marke'];
                $ekpreis = (float) $data['ekpreis'];
                $vkpreis = (float) $data['vkpreis'];
                $beschreibung = $data['beschreibung'];
                $name = $data['name'];

                $subPosten = DBAccess::insertQuery("INSERT INTO product_compact (postennummer, amount, marke, price, purchasing_price, description, name) VALUES ($postennummer, $amount, '$marke', '$vkpreis', '$ekpreis', '$beschreibung', '$name')");
                break;
        }

        if ($auftragsnummer != -1) {
            OrderHistory::add($auftragsnummer, $postennummer, OrderHistory::TYPE_ITEM, OrderHistory::STATE_ADDED, $data['Beschreibung']);
        }

        return [$postennummer, $subPosten];
    }

    public static function delete(): void
    {
        $idItem = (int) Tools::get("itemId");

        $query = "SELECT Auftragsnummer FROM posten WHERE Postennummer = :id;";
        $data = DBAccess::selectQuery($query, [
            "id" => $idItem,
        ]);
        $orderId = (int) $data[0]["Auftragsnummer"];

        $query = "DELETE FROM posten WHERE Postennummer = :id;";
        DBAccess::deleteQuery($query, [
            "id" => $idItem,
        ]);

        self::addPosition($orderId);
    }

    public static function addPosition(int $orderId): void
    {
        $query = "UPDATE posten p
            JOIN (
                SELECT Postennummer,
                    ROW_NUMBER() OVER (ORDER BY position) AS new_position
                FROM posten
                WHERE Auftragsnummer = :orderId1
            ) AS sub
            ON p.Postennummer = sub.Postennummer
            SET p.position = sub.new_position
            WHERE p.Auftragsnummer = :orderId2;";
        
        DBAccess::updateQuery($query, [
            "orderId1" => $orderId,
            "orderId2" => $orderId,
        ]);
    }

    /* adds links to all attached files to the "Einkaufspreis" column */
    protected static function getFiles(int $postennummer): string
    {
        $query = "SELECT dateiname FROM dateien, dateien_posten WHERE dateien.id = dateien_posten.id_file AND dateien_posten.id_posten = $postennummer";
        $data = DBAccess::selectQuery($query);

        $html = "";
        foreach ($data as $d) {
            $link = Link::getResourcesShortLink($d["dateiname"], "upload");
            $html .= "<a href=\"$link\" target=\"_blank\">🗎</a>";
        }

        return $html;
    }
}
