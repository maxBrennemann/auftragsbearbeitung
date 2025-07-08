<?php

namespace Classes\Project;

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

abstract class Posten
{
    abstract protected function bekommePreis();
    abstract protected function bekommeEinzelPreis();
    abstract protected function bekommePreis_formatted();
    abstract protected function bekommeEinzelPreis_formatted();
    abstract protected function bekommeDifferenz();
    abstract protected function calculateDiscount();
    abstract protected function getHTMLData();
    abstract protected function fillToArray($arr);
    abstract protected function getDescription();
    abstract protected function getEinheit();
    abstract protected function getQuantity();
    abstract protected function isInvoice();
    abstract protected function storeToDB($auftragsnummer);

    protected $postenTyp;
    protected $ohneBerechnung = false;
    protected $postennummer;
    protected $position = 0;

    public function getPosition()
    {
        return $this->position;
    }

    public function getPostennummer()
    {
        return $this->postennummer;
    }

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
                        $row["z_wage"],
                        $row["z_time"],
                        $row["z_description"],
                        $row["discount"],
                        (int) $row["is_invoice"],
                        (int) $row["free_of_charge"],
                        (int) $row["position"],
                    );
                    break;
                case "leistung":
                    $item = new Leistung(
                        $row["l_number"],
                        $row["l_description"],
                        $row["l_price"],
                        $row["l_purchase_price"],
                        $row["l_qty"],
                        $row["l_unit"],
                        $row["discount"],
                        (int) $row["is_invoice"],
                        (int) $row["free_of_charge"],
                        (int) $row["position"],
                    );
                    break;
                case "product":
                    $item = new ProduktPosten(
                        $row["p_price"],
                        $row["p_name"],
                        $row["p_description"],
                        $row["p_amount"],
                        $row["p_purchase_price"],
                        $row["p_brand"],
                        $row["discount"],
                        (int) $row["is_invoice"],
                        (int) $row["free_of_charge"],
                        (int) $row["position"],
                    );
                    break;
                case "compact":
                    $item = new ProduktPosten(
                        $row["p_price"],
                        $row["p_name"],
                        $row["p_description"],
                        $row["p_amount"],
                        $row["p_purchase_price"],
                        $row["p_brand"],
                        $row["discount"],
                        (int) $row["is_invoice"],
                        (int) $row["free_of_charge"],
                        (int) $row["position"],
                    );
                    break;
                default:
                    continue 2;
            }
            $item->postennummer = $row["id"];
            $items[] = $item;
        }

        return $items;
    }

    public static function insertPosten(string $type, array $data): array
    {
        $auftragsnummer = (int) $data['Auftragsnummer'];

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

    public static function delete()
    {
        $idItem = (int) Tools::get("itemId");
        $query = "DELETE FROM posten WHERE Postennummer = :id;";
        DBAccess::deleteQuery($query, [
            "id" => $idItem,
        ]);
    }

    /*
     * https://stackoverflow.com/a/5207487/7113688
     */
    public static function addPosition($orderId)
    {
        $query = "SET @I = 0; UPDATE posten SET `position` = (@I := @I + 1) WHERE Auftragsnummer = $orderId;";
        DBAccess::updateQuery($query);
    }

    /* adds links to all attached files to the "Einkaufspreis" column */
    protected static function getFiles($postennummer)
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
