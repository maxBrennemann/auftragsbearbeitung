<?php

namespace Src\Classes\Project;

use Src\Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
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
        $invoiceQuery = "";
        if ($isInvoice) {
            $invoiceQuery = "AND isInvoice = $status";
        }

        $query = self::itemSelectQuery("WHERE Auftragsnummer = :parentId $invoiceQuery");
        $data = DBAccess::selectQuery($query, [
            "parentId" => $orderId,
        ]);

        return self::mapRowsToPosten($data);
    }

    /**
     * @param int $offerId
     * @return array<Leistung|ProduktPosten|Zeit>
     */
    public static function getOfferItems(int $offerId): array
    {
        $query = self::itemSelectQuery("WHERE offer_id = :parentId");
        $data = DBAccess::selectQuery($query, [
            "parentId" => $offerId,
        ]);

        return self::mapRowsToPosten($data);
    }

    private static function itemSelectQuery(string $where): string
    {
        return "SELECT
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
			$where
			ORDER BY p.position;";
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return array<Leistung|ProduktPosten|Zeit>
     */
    private static function mapRowsToPosten(array $data): array
    {
        $items = [];

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
                        (float) $row["l_qty"],
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

    protected static function getOfferItem(int $offerId, int $postenId): Leistung|ProduktPosten|Zeit|false
    {
        $data = Posten::getOfferItems($offerId);
        $data = array_filter($data,
            fn($item) => $item->getPostennummer() == $postenId);
        return reset($data);
    }

    /**
     * formats loaded posten into the flat, padded structure the frontend items table expects
     *
     * @param array<Leistung|ProduktPosten|Zeit> $items
     * @return array<int, array<string, mixed>>
     */
    public static function formatItemsForTable(array $items): array
    {
        $parsedData = [];

        $mLenQuantity = 0;
        $mLenPrice = 0;
        $mLenTotalPrice = 0;
        $mLenPurchasePrice = 0;

        foreach ($items as $value) {
            $item = [];
            $item["type"] = "posten";

            if ($value instanceof Zeit) {
                $item["type"] = "time";
            } elseif ($value instanceof Leistung) {
                $item["type"] = "service";
            }

            $item["position"] = $value->getPosition();

            $value = $value->fillToArray([]);
            $item["id"] = $value["Postennummer"];
            $item["name"] = $value["Bezeichnung"];
            $item["description"] = $value["Beschreibung"];
            $item["quantity"] = $value["Anzahl"];
            $item["price"] = $value["Preis"];
            $item["unit"] = $value["MEH"];
            $item["totalPrice"] = $value["Gesamtpreis"];
            $item["purchasePrice"] = $value["Einkaufspreis"];
            $item["extraData"] = $value["extraData"] ?? [];

            if ($item["type"] == "time") {
                $mLenQuantity = max($mLenQuantity, strlen((string) $value["quantityAbsolute"]));
            } else {
                $mLenQuantity = max($mLenQuantity, strlen((string) $item["quantity"]));
            }

            $mLenPrice = max($mLenPrice, strlen((string) $item["price"]));
            $mLenTotalPrice = max($mLenTotalPrice, strlen((string) $item["totalPrice"]));
            $mLenPurchasePrice = max($mLenPurchasePrice, strlen((string) $item["purchasePrice"]));

            $parsedData[] = $item;
        }

        foreach ($parsedData as $key => $value) {
            $parsedData[$key]["quantity"] = str_pad((string) $value["quantity"], $mLenQuantity, " ", STR_PAD_LEFT);
            $parsedData[$key]["price"] = str_pad((string) $value["price"], $mLenPrice, " ", STR_PAD_LEFT);
            $parsedData[$key]["totalPrice"] = str_pad((string) $value["totalPrice"], $mLenTotalPrice, " ", STR_PAD_LEFT);
            $parsedData[$key]["purchasePrice"] = str_pad((string) $value["purchasePrice"], $mLenPurchasePrice, " ", STR_PAD_LEFT);
        }

        return $parsedData;
    }

    /**
     * @param string $type
     * @param array<string, mixed> $data
     * @param int|null $offerId when set, the posten is attached to an offer instead of an order
     * @return int[]
     */
    public static function insertPosten(string $type, array $data, ?int $offerId = null): array
    {
        $auftragsnummer = $offerId === null ? (int) $data['Auftragsnummer'] : null;
        $subPosten = 0;

        $ohneBerechnung = $data['ohneBerechnung'];
        $discount = $data['discount'] == null ? 0 : $data['discount'];
        $addToInvoice = $data['addToInvoice'] == null ? 0 : $data['addToInvoice'];

        if ($offerId !== null) {
            $postennummer = DBAccess::insertQuery("INSERT INTO posten (offer_id, Posten, ohneBerechnung, discount, isInvoice, position)
					SELECT :offerId, :type, :ohneBerechnung, :discount, :addToInvoice, count(*) + 1
					FROM posten
					WHERE offer_id = :offerId_check", [
                "offerId" => $offerId,
                "type" => $type,
                "ohneBerechnung" => $ohneBerechnung,
                "discount" => $discount,
                "addToInvoice" => $addToInvoice,
                "offerId_check" => $offerId,
            ]);
        } else {
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
        }

        switch ($type) {
            case "zeit":
                $zeit = $data['ZeitInMinuten'];
                $lohn = $data['Stundenlohn'];
                $desc = $data['Beschreibung'];

                $subPosten = DBAccess::insertQuery("INSERT INTO zeit (Postennummer, ZeitInMinuten, Stundenlohn, Beschreibung) VALUES (:postennummer, :zeit, :lohn, :desc)", [
                    "postennummer" => $postennummer,
                    "zeit" => $zeit,
                    "lohn" => $lohn,
                    "desc" => $desc,
                ]);
                break;
            case "leistung":
                $lei = $data['Leistungsnummer'];
                $bes = $data['Beschreibung'];
                $ekp = $data['Einkaufspreis'];
                $pre = $data['SpeziefischerPreis'];
                $anz = $data['anzahl'];
                $meh = $data['MEH'];

                $subPosten = DBAccess::insertQuery("INSERT INTO leistung_posten (Leistungsnummer, Postennummer, Beschreibung, Einkaufspreis, SpeziefischerPreis, meh, qty) VALUES (:lei, :postennummer, :bes, :ekp, :pre, :meh, :anz)", [
                    "lei" => $lei,
                    "postennummer" => $postennummer,
                    "bes" => $bes,
                    "ekp" => $ekp,
                    "pre" => $pre,
                    "meh" => $meh,
                    "anz" => $anz,
                ]);
                break;
            case "produkt":
                $amount = $data['amount'];
                $prodId = $data['prodId'];
                $subPosten = DBAccess::insertQuery("INSERT INTO produkt_posten (Produktnummer, Postennummer, Anzahl) VALUES (:prodId, :postennummer, :amount)", [
                    "prodId" => $prodId,
                    "postennummer" => $postennummer,
                    "amount" => $amount,
                ]);
                break;
            case "compact":
                $amount = $data['amount'];
                $marke = $data['marke'];
                $ekpreis = (float) $data['ekpreis'];
                $vkpreis = (float) $data['vkpreis'];
                $beschreibung = $data['beschreibung'];
                $name = $data['name'];

                $subPosten = DBAccess::insertQuery("INSERT INTO product_compact (postennummer, amount, marke, price, purchasing_price, description, name) VALUES (:postennummer, :amount, :marke, :vkpreis, :ekpreis, :beschreibung, :name)", [
                    "postennummer" => $postennummer,
                    "amount" => $amount,
                    "marke" => $marke,
                    "vkpreis" => $vkpreis,
                    "ekpreis" => $ekpreis,
                    "beschreibung" => $beschreibung,
                    "name" => $name,
                ]);
                break;
        }

        if ($offerId === null && $auftragsnummer != -1) {
            OrderHistory::add($auftragsnummer, $postennummer, OrderHistory::TYPE_ITEM, OrderHistory::STATE_ADDED, $data['Beschreibung']);
        }

        return [$postennummer, $subPosten];
    }

    /**
     * Deletes a posten of any type together with its type specific rows, closes the gap in the
     * positions and answers with the new order total, so the frontend can refresh its sum.
     */
    public static function delete(): void
    {
        $idItem = (int) Tools::get("itemId");

        $query = "SELECT Auftragsnummer, offer_id FROM posten WHERE Postennummer = :id;";
        $data = DBAccess::selectQuery($query, [
            "id" => $idItem,
        ]);

        if (empty($data)) {
            JSONResponseHandler::throwError(404, "Posten existiert nicht");
        }

        $orderId = (int) $data[0]["Auftragsnummer"];
        $offerId = (int) $data[0]["offer_id"];

        /* none of these tables has a foreign key on posten, so nothing is cleaned up implicitly */
        $params = ["id" => $idItem];
        $queries = [
            "DELETE FROM zeiterfassung WHERE id_zeit IN (SELECT Nummer FROM zeit WHERE Postennummer = :id)",
            "DELETE FROM zeit WHERE Postennummer = :id",
            "DELETE FROM leistung_posten WHERE Postennummer = :id",
            "DELETE FROM produkt_posten WHERE Postennummer = :id",
            "DELETE FROM product_compact WHERE postennummer = :id",
            "DELETE FROM dateien_posten WHERE id_posten = :id",
            "DELETE FROM posten WHERE Postennummer = :id",
        ];
        foreach ($queries as $query) {
            DBAccess::deleteQuery($query, $params);
        }

        $price = null;
        if ($orderId > 0) {
            self::addPosition($orderId);
            OrderHistory::add($orderId, $idItem, OrderHistory::TYPE_ITEM, OrderHistory::STATE_DELETED);

            $order = new Auftrag($orderId);
            $price = $order->preisBerechnen();
        } elseif ($offerId != 0) {
            self::addOfferPosition($offerId);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "price" => $price,
        ]);
    }

    public static function updateOrderPositions(): void
    {
        self::updatePositions("Auftragsnummer", (int) Tools::get("id"));
    }

    public static function updateOfferPositions(): void
    {
        self::updatePositions("offer_id", (int) Tools::get("id"));
    }

    /**
     * Stores a new order for the given posten ids (as dragged in the items table). The table may show
     * only a part of the posten (filter "Rechnungsposten ausblenden"), so the listed posten are
     * rearranged among the position slots they already occupy and all others keep their place.
     */
    private static function updatePositions(string $parentColumn, int $parentId): void
    {
        $ids = json_decode((string) Tools::get("positions"), true);
        if (!is_array($ids) || $parentId <= 0) {
            JSONResponseHandler::throwError(400, "Ungültige Reihenfolge");
        }
        $ids = array_values(array_unique(array_map("intval", $ids)));

        $rows = DBAccess::selectQuery("SELECT Postennummer FROM posten WHERE $parentColumn = :parentId ORDER BY position, Postennummer", [
            "parentId" => $parentId,
        ]);
        $current = array_map(fn($row) => (int) $row["Postennummer"], $rows);

        if (count(array_diff($ids, $current)) > 0) {
            JSONResponseHandler::throwError(400, "Die Reihenfolge enthält Posten, die nicht zu diesem Vorgang gehören");
        }

        /* walk the current order and fill every slot of a listed posten with the next id of the new order */
        $next = 0;
        $newOrder = [];
        foreach ($current as $id) {
            $newOrder[] = in_array($id, $ids, true) ? $ids[$next++] : $id;
        }

        foreach ($newOrder as $index => $id) {
            DBAccess::updateQuery("UPDATE posten SET position = :position WHERE Postennummer = :id", [
                "position" => $index + 1,
                "id" => $id,
            ]);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
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

    public static function addOfferPosition(int $offerId): void
    {
        $query = "UPDATE posten p
            JOIN (
                SELECT Postennummer,
                    ROW_NUMBER() OVER (ORDER BY position) AS new_position
                FROM posten
                WHERE offer_id = :offerId1
            ) AS sub
            ON p.Postennummer = sub.Postennummer
            SET p.position = sub.new_position
            WHERE p.offer_id = :offerId2;";

        DBAccess::updateQuery($query, [
            "offerId1" => $offerId,
            "offerId2" => $offerId,
        ]);
    }

    /**
     * Copies all posten of an offer (incl. their type specific rows, time tracking and file links)
     * to an order. The offer keeps its own rows, so an accepted offer can still be displayed and
     * reprinted exactly as it was offered, independent of later changes to the order.
     */
    public static function copyOfferItemsToOrder(int $offerId, int $orderId): void
    {
        $items = DBAccess::selectQuery("SELECT Postennummer FROM posten WHERE offer_id = :offerId ORDER BY position;", [
            "offerId" => $offerId,
        ]);

        foreach ($items as $item) {
            $sourceId = (int) $item["Postennummer"];

            $targetId = DBAccess::insertQuery("INSERT INTO posten (Auftragsnummer, position, angebotsNr, rechnungsNr, Posten, istStandard, ohneBerechnung, discount, isInvoice)
                SELECT :orderId, position, angebotsNr, rechnungsNr, Posten, istStandard, ohneBerechnung, discount, isInvoice
                FROM posten
                WHERE Postennummer = :sourceId", [
                "orderId" => $orderId,
                "sourceId" => $sourceId,
            ]);

            $times = DBAccess::selectQuery("SELECT Nummer FROM zeit WHERE Postennummer = :sourceId;", [
                "sourceId" => $sourceId,
            ]);
            foreach ($times as $time) {
                $timeId = DBAccess::insertQuery("INSERT INTO zeit (Postennummer, ZeitInMinuten, Stundenlohn, Beschreibung)
                    SELECT :targetId, ZeitInMinuten, Stundenlohn, Beschreibung FROM zeit WHERE Nummer = :timeId", [
                    "targetId" => $targetId,
                    "timeId" => (int) $time["Nummer"],
                ]);

                DBAccess::insertQuery("INSERT INTO zeiterfassung (id_zeit, from_time, to_time, `date`)
                    SELECT :newTimeId, from_time, to_time, `date` FROM zeiterfassung WHERE id_zeit = :timeId", [
                    "newTimeId" => $timeId,
                    "timeId" => (int) $time["Nummer"],
                ]);
            }

            $copies = [
                "INSERT INTO leistung_posten (Leistungsnummer, Postennummer, Beschreibung, Einkaufspreis, SpeziefischerPreis, meh, qty)
                    SELECT Leistungsnummer, :targetId, Beschreibung, Einkaufspreis, SpeziefischerPreis, meh, qty FROM leistung_posten WHERE Postennummer = :sourceId",
                "INSERT INTO produkt_posten (Produktnummer, Postennummer, Anzahl)
                    SELECT Produktnummer, :targetId, Anzahl FROM produkt_posten WHERE Postennummer = :sourceId",
                "INSERT INTO product_compact (postennummer, amount, marke, price, purchasing_price, description, name)
                    SELECT :targetId, amount, marke, price, purchasing_price, description, name FROM product_compact WHERE postennummer = :sourceId",
                "INSERT INTO dateien_posten (id_file, id_posten)
                    SELECT id_file, :targetId FROM dateien_posten WHERE id_posten = :sourceId",
            ];

            foreach ($copies as $query) {
                DBAccess::insertQuery($query, [
                    "targetId" => $targetId,
                    "sourceId" => $sourceId,
                ]);
            }
        }
    }

    /**
     * Removes all posten of an offer together with their type specific rows. None of these
     * tables has a foreign key on posten, so nothing is cleaned up implicitly.
     */
    public static function deleteOfferItems(int $offerId): void
    {
        $params = ["offerId" => $offerId];
        $offerItems = "SELECT Postennummer FROM posten WHERE offer_id = :offerId";

        $queries = [
            "DELETE FROM zeiterfassung WHERE id_zeit IN (SELECT Nummer FROM zeit WHERE Postennummer IN ($offerItems))",
            "DELETE FROM zeit WHERE Postennummer IN ($offerItems)",
            "DELETE FROM leistung_posten WHERE Postennummer IN ($offerItems)",
            "DELETE FROM produkt_posten WHERE Postennummer IN ($offerItems)",
            "DELETE FROM product_compact WHERE postennummer IN ($offerItems)",
            "DELETE FROM dateien_posten WHERE id_posten IN ($offerItems)",
            "DELETE FROM posten WHERE offer_id = :offerId",
        ];

        foreach ($queries as $query) {
            DBAccess::deleteQuery($query, $params);
        }
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
