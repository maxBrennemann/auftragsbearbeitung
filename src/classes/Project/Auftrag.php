<?php

namespace Src\Classes\Project;

use Src\Classes\Link;
use Src\Classes\Models\Auftragstyp;
use Src\Classes\Notification\NotifiableEntity;
use Src\Classes\Notification\NotificationManager;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Auftrag implements StatisticsInterface, NotifiableEntity
{
    private int $Auftragsnummer;
    private string $Auftragsbezeichnung;
    private string $Auftragsbeschreibung;

    /** @var array<Leistung|ProduktPosten|Zeit> */
    private $Auftragsposten = [];

    ///** @var Step[] */
    //private $Bearbeitungsschritte = [];

    private int $auftragstyp = 0;
    private int $rechnungsnummer = 0;

    /* dates */
    public string $datum;
    public string $termin;
    public string $fertigstellung;

    private bool $isPaid = false;
    private OrderState $status = OrderState::Default;

    private int $customerId = 0;

    public function __construct(int $orderId)
    {
        if ($orderId <= 0) {
            return;
        }

        $this->Auftragsnummer = $orderId;
        $data = DBAccess::selectAllByCondition("auftrag", "Auftragsnummer", (string) $orderId);
        $data = $data[0] ?? [];

        if (!empty($data)) {
            $this->Auftragsbeschreibung = $data['Auftragsbeschreibung'];
            $this->Auftragsbezeichnung = $data['Auftragsbezeichnung'];
            $this->auftragstyp = (int) $data['Auftragstyp'];
            $this->rechnungsnummer = (int) $data['Rechnungsnummer'];

            $this->datum = (string) $data['Datum'];
            $this->termin = (string) $data['Termin'];
            $this->fertigstellung = (string) $data['Fertigstellung'];

            $this->isPaid = $data['Bezahlt'] == 1 ? true : false;
            $this->status = OrderState::tryFrom($data["status"]) ?? OrderState::Default;

            $data = DBAccess::selectQuery("SELECT * FROM schritte WHERE Auftragsnummer = {$orderId}");
            foreach ($data as $step) {
                //$this->Bearbeitungsschritte[] = new Step(/*$step['Auftragsnummer'], $step['Schrittnummer'], $step['Bezeichnung'], $step['Datum'], $step['Priority'], $step['istErledigt']*/);
            }

            $this->Auftragsposten = Posten::getOrderItems($orderId);
            $this->customerId = (int) DBAccess::selectQuery("SELECT Kundennummer FROM auftrag WHERE auftragsnummer = :orderId", [
                "orderId" => $orderId,
            ])[0]['Kundennummer'];
        } else {
            throw new \Exception("Auftragsnummer $orderId existiert nicht oder kann nicht gefunden werden.");
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getContactPersons(): array
    {
        $query = "SELECT ap.Nummer AS id, ap.Vorname AS firstName, ap.Nachname AS lastName, ap.Email AS email, 
				CASE
					WHEN a.Ansprechpartner = ap.Nummer THEN 1
					ELSE 0
				END AS isSelected
			FROM ansprechpartner ap 
				JOIN kunde k ON ap.Kundennummer = k.Kundennummer
				JOIN auftrag a ON a.Kundennummer = k.Kundennummer
			WHERE a.Auftragsnummer = :id;";
        $data = DBAccess::selectQuery($query, [
            "id" => $this->Auftragsnummer,
        ]);

        return $data;
    }

    public function getAuftragsbeschreibung(): string
    {
        return $this->Auftragsbeschreibung;
    }

    public function getAuftragsnummer(): int
    {
        return $this->Auftragsnummer;
    }

    public function isPaid(): bool
    {
        return $this->isPaid;
    }

    public function getPaymentDate(): ?string
    {
        if (!$this->isPaid()) {
            return null;
        }

        $query = "SELECT DATE_FORMAT(payment_date, '%d.%m.%Y') AS payment_date FROM invoice WHERE order_id = :orderId";
        $data = DBAccess::selectQuery($query, [
            "orderId" => $this->Auftragsnummer
        ]);

        if (empty($data)) {
            return null;
        }

        return $data[0]["payment_date"];
    }

    public function getPaymentType(): ?string
    {
        if (!$this->isPaid()) {
            return null;
        }

        $query = "SELECT payment_type FROM invoice WHERE order_id = :orderId";
        $data = DBAccess::selectQuery($query, ["orderId" => $this->Auftragsnummer]);

        if (empty($data)) {
            return null;
        }

        return $data[0]["payment_type"];
    }

    /**
     * @return array<Leistung|ProduktPosten|Zeit>
     */
    public function getAuftragspostenData(): array
    {
        return $this->Auftragsposten;
    }

    public function getAuftragstyp(): int
    {
        return $this->auftragstyp;
    }

    public function getAuftragstypBezeichnung(): string
    {
        $query = "SELECT `Auftragstyp` FROM `auftragstyp` WHERE `id` = :idAuftragstyp LIMIT 1;";
        $bez = DBAccess::selectQuery($query, ["idAuftragstyp" => $this->auftragstyp]);

        if ($bez != null) {
            return $bez[0]["Auftragstyp"];
        } else {
            return "";
        }
    }

    /**
     * @return string[][]
     */
    public static function getAllOrderTypes(): array
    {
        return Auftragstyp::all();
    }

    public function getAuftragsbezeichnung(): string
    {
        return $this->Auftragsbezeichnung;
    }

    public function getDate(): string
    {
        return $this->datum;
    }

    public function getDeadline(): string
    {
        if ($this->termin == "0000-00-00" || $this->termin == null) {
            return "";
        }
        return $this->termin;
    }

    /**
     * calculates the sum of all items in the order
     */
    public function calcOrderSum(): float
    {
        $price = 0;
        foreach ($this->Auftragsposten as $posten) {
            if ($posten->isInvoice() == 1) {
                $price += $posten->bekommePreis();
            }
        }
        return $price;
    }

    public function preisBerechnen(): float
    {
        $price = 0;
        foreach ($this->Auftragsposten as $posten) {
            $price += $posten->bekommePreis();
        }
        return $price;
    }

    public function gewinnBerechnen(): float
    {
        $price = 0;
        foreach ($this->Auftragsposten as $posten) {
            $price += $posten->bekommeDifferenz();
        }
        return $price;
    }

    public function getKundennummer(): int
    {
        return $this->customerId;
    }

    public static function getOrderItems(): void
    {
        $id = (int) Tools::get("id");
        $data = Posten::getOrderItems($id, ClientSettings::getFilterOrderPosten(), 0);

        $parsedData = [];
        foreach ($data as $key => $value) {
            $item = [];
            $item["type"] = "posten";

            if ($value instanceof Zeit) {
                $item["type"] = "time";
            } elseif ($value instanceof Leistung) {
                $item["type"] = "service";
            }

            $item["position"] = $value->getPosition();
            $item["price"] = $value->bekommeEinzelPreis();
            $item["totalPrice"] = $value->bekommePreis();

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

            $parsedData[] = $item;
        }

        JSONResponseHandler::sendResponse($parsedData);
    }

    public static function getOrderItem(int $id): void {}

    public function getInvoicePostenTable(): string
    {
        $data = [];
        foreach ($this->Auftragsposten as $p) {
            if (!$p->isInvoice()) {
                continue;
            }

            $data[] = [
                "quantity" => (string) $p->getQuantity(),
                "unit" => $p->getEinheit(),
                "name" => $p->getDescription(),
                "item_price" => $p->bekommeEinzelPreis_formatted(),
                "total_price" => $p->bekommePreis_formatted(),
            ];
        }

        $header = [
            "columns" => [
                "quantity",
                "unit",
                "name",
                "item_price",
                "total_price"
            ],
            "primaryKey" => "",
            "names" => [
                "Menge",
                "MEH",
                "Bezeichnung",
                "E-Preis",
                "G-Preis",
            ],
        ];
        
        $options = [];
        $options["styles"]["table"]["className"] = [
            "table-auto", "overflow-x-scroll", "w-full"
        ];

        return TableGenerator::create($data, $options, $header);
    }

    public static function getInvoicePostenTableAjax(): void
    {
        $orderId = (int) Tools::get("id");
        $order = new Auftrag($orderId);

        JSONResponseHandler::sendResponse([
            "invoicePostenTable" => $order->getInvoicePostenTable(),
        ]);
    }

    public function getIsArchiviert(): bool
    {
        return $this->status == OrderState::Archived;
    }

    public static function getReadyOrders(): string
    {
        $query = "SELECT Auftragsnummer, IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Kunde, Auftragsbezeichnung FROM auftrag, kunde WHERE status = :status AND kunde.Kundennummer = auftrag.Kundennummer AND Rechnungsnummer = 0";
        $data = DBAccess::selectQuery($query, ["status" => OrderState::Finished->value]);

        $column_names = array(
            0 => array("COLUMN_NAME" => "Auftragsnummer"),
            1 => array("COLUMN_NAME" => "Kunde"),
            2 => array("COLUMN_NAME" => "Auftragsbezeichnung")
        );

        //$link = new Link();
        //$link->addBaseLink("auftrag");
        //$link->setIterator("id", $data, "Auftragsnummer");

        //$t = new Table();
        //$t->createByData($data, $column_names);
        //$t->addLink($link);
        //return $t->getTable();

        return "";
    }

    /**
     * @param array<int> $ids
     * @return string
     */
    public static function getAuftragsliste(array $ids): string
    {
        $header = [
            "columns" => [
                "Auftragsnummer",
                "Datum",
                "Termin",
                "Kunde",
                "Auftragsbezeichnung",
            ],
            "names" => [
                "Nr.",
                "Datum",
                "Termin",
                "Kunde",
                "Auftragsbezeichnung",
            ],
            "primaryKey" => "Auftragsnummer",
        ];

        $query = "SELECT Auftragsnummer, DATE_FORMAT(Datum, '%d.%m.%Y') as Datum, IF(kunde.Firmenname = '', 
				CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Kunde, 
				Auftragsbezeichnung, IF(auftrag.Termin IS NULL OR auftrag.Termin = '0000-00-00', 'kein Termin', DATE_FORMAT(auftrag.Termin, '%d.%m.%Y')) AS Termin 
			FROM auftrag 
			LEFT JOIN kunde 
				ON auftrag.Kundennummer = kunde.Kundennummer 
			WHERE ";

        if (count($ids) != 0) {
            $query .= "Auftragsnummer IN (" . implode(",", $ids) . ")";
        } else {
            $query .= "Rechnungsnummer = 0 AND `status` != '" . OrderState::Default->value . "'";
        }

        $data = DBAccess::selectQuery($query);

        $options = [
            "link" => "/auftrag?id=",
            "primaryKey" => "id",
        ];
        $options["styles"]["table"]["className"] = [
            "table-auto", "overflow-x-scroll", "w-full"
        ];
        $options["styles"]["key"]["Datum"] = ["nowrap"];
        $options["styles"]["key"]["Termin"] = ["nowrap"];

        return TableGenerator::create($data, $options, $header);
    }

    public static function getOpenOrders(): void
    {
        $query = "SELECT Auftragsnummer, DATE_FORMAT(Datum, '%d.%m.%Y') AS Datum, 
				IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), 
				kunde.Firmenname) AS Kunde, Auftragsbezeichnung, 
				IF(auftrag.Termin IS NULL OR auftrag.Termin = '0000-00-00', 'kein Termin', 
				DATE_FORMAT(auftrag.Termin, '%d.%m.%Y')) AS Termin 
			FROM auftrag 
			LEFT JOIN kunde 
				ON auftrag.Kundennummer = kunde.Kundennummer 
			WHERE Rechnungsnummer = 0 AND `status` = '" . OrderState::Default->value . "'";

        $data = DBAccess::selectQuery($query);
        JSONResponseHandler::sendResponse($data);
    }

    public function istRechnungGestellt(): bool
    {
        return $this->rechnungsnummer == 0 ? false : true;
    }

    public function getInvoiceId(): int
    {
        $invoice = Invoice::getInvoiceByOrderId($this->Auftragsnummer);
        return $invoice->getId();
    }

    public function getInvoiceNumber(): int
    {
        $invoice = Invoice::getInvoiceByOrderId($this->Auftragsnummer);
        return $invoice->getNumber();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getLinkedVehicles(): array
    {
        return DBAccess::selectQuery("SELECT Nummer, Kennzeichen, Fahrzeug FROM fahrzeuge LEFT JOIN fahrzeuge_auftraege ON fahrzeuge_auftraege.id_fahrzeug = Nummer WHERE fahrzeuge_auftraege.id_auftrag = :idOrder", [
            "idOrder" => $this->getAuftragsnummer(),
        ]);
    }

    public function getColors(): bool|string
    {
        $query = "SELECT color_name, hex_value, id, producer, short_name 
			FROM color, color_auftrag 
			WHERE id_color = id 
				AND id_auftrag = :orderId";

        $colors = DBAccess::selectQuery($query, [
            "orderId" => $this->getAuftragsnummer()
        ]);

        ob_start();
        insertTemplate('../public/views/colorView.php', [
            "colors" => $colors,
        ]);

        $content = ob_get_clean();
        return $content;
    }

    /**
     * @return array{archived: string, date: mixed, deadline: mixed, finished: mixed, id: int, invoice: mixed, orderDescription: mixed, orderTitle: mixed, status: OrderState, summe: float|int}
     */
    public function getOrderCardData(): array
    {
        $query = "SELECT DATE_FORMAT(Datum, '%d.%m.%Y') AS Datum,
				DATE_FORMAT(Termin, '%d.%m.%Y') AS Termin, 
				DATE_FORMAT(Fertigstellung , '%d.%m.%Y') AS Fertigstellung,
                invoice.invoice_number
			FROM auftrag
            LEFT JOIN invoice ON invoice.order_id = auftrag.Auftragsnummer
			WHERE Auftragsnummer = :orderId";
        $data = DBAccess::selectQuery($query, [
            "orderId" => $this->getAuftragsnummer(),
        ]);

        $date = $data[0]["Datum"] == "00.00.0000" ? "-" : $data[0]["Datum"];
        $deadline = $data[0]["Termin"] == "00.00.0000" ? "-" : $data[0]["Termin"];
        $finished = $data[0]["Fertigstellung"] == "00.00.0000" ? "-" : $data[0]["Fertigstellung"];
        $invoiceNumber = $data[0]["invoice_number"] == "NULL" ? 0 : $data[0]["invoice_number"];

        return [
            "id" => $this->Auftragsnummer,
            "archived" => $this->status->value,
            "orderTitle" => $this->Auftragsbezeichnung,
            "orderDescription" => $this->Auftragsbeschreibung,
            "date" => $date,
            "deadline" => $deadline,
            "finished" => $finished,
            "invoice" => $invoiceNumber,
            "summe" => $invoiceNumber != 0 ? $this->preisBerechnen() : 0,
            "status" => $this->status,
        ];
    }

    public static function getNotes(): void
    {
        $orderId = (int) Tools::get("orderId");

        $notes = DBAccess::selectQuery("SELECT id, note, title, creation_date as `date` FROM notes WHERE orderId = :id ORDER BY creation_date DESC", [
            "id" => $orderId,
        ]);

        foreach ($notes as $key => $note) {
            if ($notes[$key]["date"] == date("Y-m-d")) {
                $notes[$key]["date"] = "Heute";
            } elseif ($notes[$key]["date"] == date("Y-m-d", strtotime("-1 day"))) {
                $notes[$key]["date"] = "Gestern";
            } else {
                $timestamp = strtotime($note["date"]);
                if ($timestamp === false) {
                    $timestamp = null;
                }
                $notes[$key]["date"] = date("d.m.Y", $timestamp);
            }
        }

        JSONResponseHandler::sendResponse($notes);
    }

    public function recalculate(): void {}

    public static function archive(): void
    {
        $orderId = (int) Tools::get("id");
        $status = Tools::get("status") == "archive" ? OrderState::Archived : OrderState::Default;

        $query = "UPDATE auftrag SET `status` = :archiveStatus WHERE Auftragsnummer = :orderId";
        DBAccess::updateQuery($query, [
            "orderId" => $orderId,
            "archiveStatus" => $status->value,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "archiveStatus" => $status->value,
        ]);
    }

    public static function finish(): void
    {
        $orderId = (int) Tools::get("id");
        DBAccess::updateQuery("UPDATE auftrag SET `status` = :orderStatus WHERE Auftragsnummer = :orderId", [
            "orderId" => $orderId,
            "orderStatus" => OrderState::Finished,
        ]);

        NotificationManager::addNotification(null, 4, "Auftrag $orderId wurde abgeschlossen", $orderId);

        JSONResponseHandler::returnOK();
    }

    /**
     * adds a new order to the database by using the data from the form,
     * which is sent by the client;
     * the function echos a json object with the response link and the order id
     */
    public static function addOrder(): void
    {
        $bezeichnung = Tools::get("name");
        $beschreibung = Tools::get("description");
        $typ = Tools::get("type");
        $deadline = Tools::get("deadline");
        $angenommenVon = Tools::get("acceptedBy");
        $kdnr = Tools::get("customerId");
        $angenommenPer = Tools::get("acceptedVia");
        $ansprechpartner = (int) Tools::get("contactperson");

        $date = date("Y-m-d");
        if ($deadline == "") {
            $deadline = null;
        }

        $query = "INSERT INTO auftrag (Kundennummer, Auftragsbezeichnung, Auftragsbeschreibung, Auftragstyp, Datum, Termin, AngenommenDurch, AngenommenPer, Ansprechpartner) VALUES (:kdnr, :bezeichnung, :beschreibung, :typ, :datum, :termin, :angenommenVon, :angenommenPer, :ansprechpartner);";
        $parameters = [
            "kdnr" => $kdnr,
            "bezeichnung" => $bezeichnung,
            "beschreibung" => $beschreibung,
            "typ" => $typ,
            "datum" => $date,
            "termin" => $deadline,
            "angenommenVon" => $angenommenVon,
            "angenommenPer" => $angenommenPer,
            "ansprechpartner" => $ansprechpartner
        ];
        $orderId = DBAccess::insertQuery($query, $parameters);

        $data = [
            "success" => true,
            "responseLink" => Link::getPageLink("auftrag") . "?id=$orderId",
            "orderId" => $orderId
        ];

        NotificationManager::addNotification(null, 4, "Auftrag $orderId wurde angelegt", $orderId);

        OrderHistory::add($orderId, $orderId, OrderHistory::TYPE_ORDER, OrderHistory::STATE_ADDED, "Neuer Auftrag");
        JSONResponseHandler::sendResponse($data);
    }

    public static function getFiles(int $orderId): string
    {
        $query = "SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ 
            FROM dateien 
            LEFT JOIN dateien_auftraege 
                ON dateien_auftraege.id_datei = dateien.id
            WHERE dateien_auftraege.id_auftrag = :orderId";
        $files = DBAccess::selectQuery($query, [
            "orderId" => $orderId,
        ]);

        for ($i = 0; $i < sizeof($files); $i++) {
            $link = Link::getResourcesShortLink($files[$i]['Datei'], "upload");

            $filePath = "storage/upload/" . $files[$i]['Datei'];

            if (file_exists($filePath) 
                && (@exif_imagetype($filePath) != false)
                && getimagesize($filePath) != false
            ) {
                $html = '<a target="_blank" rel="noopener noreferrer" href="' . $link . '"><img src="" width="40px"><p class="img_prev">' . $files[$i]["originalname"] . '</p></a>';
            } else {
                $html = '<span><a target="_blank" rel="noopener noreferrer" href="' . $link . '">' . $files[$i]["originalname"] . '</a></span>';
            }

            $files[$i]["Datei"] = $html;
        }

        $header = [
            "columns" => [
                "Datei",
                "Typ",
                "Datum",
            ],
            "names" => [
                "Datei",
                "Typ",
                "Datum",
            ],
        ];

        $options = [];
        return TableGenerator::create($files, $options, $header); // TODO: add delete option
    }

    public static function deleteOrder(): void
    {
        $id = (int) Tools::get("id");
        $query = "DELETE FROM auftrag WHERE Auftragsnummer = :id;";
        DBAccess::deleteQuery($query, ["id" => $id]);

        if (DBAccess::getAffectedRows() == 0) {
            JSONResponseHandler::throwError(404, "Auftrag existiert nicht");
        }

        JSONResponseHandler::sendResponse([
            "success" => true,
            "home" => Link::getPageLink(""),
        ]);
    }

    public function addNewNote(): void
    {
        $title = (string) Tools::get("title");
        $note = (string) Tools::get("note");
        $date = Tools::get("date") ?? date("Y-m-d");

        $noteId = DBAccess::insertQuery("INSERT INTO notes (orderId, title, note, creation_date) VALUES (:orderId, :title, :note, :creationDate)", [
            "orderId" => $this->Auftragsnummer,
            "title" => $title,
            "note" => $note,
            "creationDate" => $date,
        ]);

        OrderHistory::add($this->Auftragsnummer, $noteId, OrderHistory::TYPE_NOTE, OrderHistory::STATE_ADDED, $note);

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            $timestamp = null;
        }

        JSONResponseHandler::sendResponse([
            "success" => true,
            "date" => date("d.m.Y", $timestamp),
            "id" => $noteId,
        ]);
    }

    public static function addNote(): void
    {
        $orderId = (int) Tools::get("orderId");
        $order = new Auftrag($orderId);
        $order->addNewNote();
    }

    public static function updateNote(): void
    {
        $id = (int) Tools::get("id");
        $type = (string) Tools::get("type");
        $data = (string) Tools::get("data");

        DBAccess::updateQuery("UPDATE notes SET $type = :data WHERE id = :id", [
            "id" => $id,
            "data" => $data,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function deleteNote(): void
    {
        $note = (int) Tools::get("id");
        DBAccess::deleteQuery("DELETE FROM notes WHERE id = :note", [
            "note" => $note,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function updateOrderType(): void
    {
        $idOrderType = (int) Tools::get("type");
        $idOrder = (int) Tools::get("id");

        $query = "UPDATE `auftrag` SET `Auftragstyp` = :idOrderType WHERE `Auftragsnummer` = :idOrder";
        DBAccess::updateQuery($query, [
            "idOrder" => $idOrder,
            "idOrderType" => $idOrderType,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function updateOrderTitle(): void
    {
        $orderTitle = (string) Tools::get("title");
        $idOrder = (int) Tools::get("id");

        $query = "UPDATE auftrag SET Auftragsbezeichnung = :title WHERE Auftragsnummer = :idOrder";
        DBAccess::updateQuery($query, [
            "idOrder" => $idOrder,
            "title" => $orderTitle,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function updateContactPerson(): void
    {
        $idContact = (string) Tools::get("idContact");
        $idOrder = (int) Tools::get("id");

        $query = "UPDATE auftrag SET Ansprechpartner = :idContact WHERE Auftragsnummer = :idOrder";
        DBAccess::updateQuery($query, [
            "idOrder" => $idOrder,
            "idContact" => $idContact,
        ]);
        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function updateDate(): void
    {
        $order = (int) Tools::get("id");
        $date =  Tools::get("date");
        $type = (int)  Tools::get("type");

        $types = [
            1 => "Datum",
            2 => "Termin",
            3 => "Fertigstellung"
        ];

        if (!isset($types[$type])) {
            JSONResponseHandler::throwError(400, "Type does not exist");
        }

        $type = $types[$type];

        if ($date == "unset") {
            DBAccess::updateQuery("UPDATE auftrag SET $type = NULL WHERE Auftragsnummer = :order;", [
                "order" => $order,
            ]);
        } else {
            DBAccess::updateQuery("UPDATE auftrag SET $type = :setDate WHERE Auftragsnummer = :order;", [
                "setDate" => $date,
                "order" => $order,
            ]);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function addColors(): void
    {
        $id = (int) Tools::get("id");
        $colors = Tools::get("colors");
        $colors = json_decode($colors, false);

        $query = "INSERT INTO color_auftrag (id_auftrag, id_color) VALUES ";
        $data = [];

        foreach ($colors as $colorId) {
            $data[] = [$id, (int) $colorId];
        }

        DBAccess::insertMultiple($query, $data);

        $order = new Auftrag($id);
        $response = $order->getColors();
        JSONResponseHandler::sendResponse([
            "colors" => $response,
        ]);
    }

    public static function addColor(): void
    {
        $orderId = (int) Tools::get("id");
        $colorName = (string) Tools::get("colorName");
        $hexValue = (string) Tools::get("hexValue");
        $shortName = (string) Tools::get("shortName");
        $producer = (string) Tools::get("producer");

        $color = new Color($colorName, $hexValue, $shortName, $producer);
        $colorId = $color->save();

        DBAccess::insertQuery("INSERT INTO color_auftrag (id_color, id_auftrag) VALUES (:colorId, :orderId)", [
            "colorId" => $colorId,
            "orderId" => $orderId,
        ]);

        $order = new Auftrag($orderId);
        $response = $order->getColors();
        JSONResponseHandler::sendResponse([
            "colors" => $response,
        ]);
    }

    public static function deleteColor(): void
    {
        $orderId = (int) Tools::get("id");
        $colorId = (int) Tools::get("colorId");

        DBAccess::deleteQuery("DELETE FROM color_auftrag WHERE id_color = :colorId AND id_auftrag = :orderId", [
            "colorId" => $colorId,
            "orderId" => $orderId,
        ]);

        $order = new Auftrag($orderId);
        $response = $order->getColors();
        JSONResponseHandler::sendResponse([
            "colors" => $response,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    public static function resetAnsprechpartner(array $data): void
    {
        $customerId = Tools::get("customerId");
        $query = "UPDATE auftrag SET Ansprechpartner = 0 WHERE Kundennummer = :customerId AND Ansprechpartner = :contactPerson;";

        DBAccess::updateQuery($query, [
            "customerId" => $customerId,
            "contactPerson" => $data["Nummer"],
        ]);
    }

    public static function getOverview(): bool|string
    {
        $query = "SELECT Auftragsnummer FROM auftrag WHERE `status` != '" . OrderState::Default->value . "'AND Rechnungsnummer = 0;";
        $data = DBAccess::selectQuery($query);
        $orders = [];

        foreach ($data as $row) {
            $order = new Auftrag((int) $row["Auftragsnummer"]);
            $orders[] = $order->getOrderCardData();
        }

        ob_start();
        insertTemplate('files/views/orderCardView.php', [
            "orders" => $orders,
        ]);
        $content = ob_get_clean();

        return $content;
    }

    public static function addFiles(): void
    {
        $uploadHandler = new UploadHandler();
        $files = $uploadHandler->uploadMultiple();
        $orderId = (int) Tools::get("id");

        $query = "INSERT INTO dateien_auftraege (id_datei, id_auftrag) VALUES ";
        $values = [];
        foreach ($files as $file) {
            $values[] = [(int) $file["id"], $orderId];

            OrderHistory::add($orderId, $file["id"], OrderHistory::TYPE_FILE, OrderHistory::STATE_ADDED);
        }

        DBAccess::insertMultiple($query, $values);
    }

    public static function resetInvoice(): void
    {
        $orderId = Tools::get("id");
        $query = "UPDATE auftrag SET Rechnungsnummer = 0 WHERE Auftragsnummer = :idOrder";
        DBAccess::updateQuery($query, [
            "idOrder" => $orderId,
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function editDescription(): void
    {
        $text = (string) Tools::get("text");
        $orderId = (int) Tools::get("id");
        DBAccess::updateQuery("UPDATE auftrag SET Auftragsbeschreibung = :text WHERE Auftragsnummer = :orderId", [
            "text" => $text,
            "orderId" => $orderId,
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function changeCustomer(): void
    {
        $orderId = (int) Tools::get("orderId");
        $newCustomerId = (int) Tools::get("newCustomerId");

        $query = "SELECT * FROM kunde WHERE Kundennummer = :customerId;";
        $data = DBAccess::selectQuery($query, [
            "customerId" => $newCustomerId,
        ]);

        if (count($data) == 0) {
            JSONResponseHandler::throwError(400, "Invalid request");
        }

        $query = "UPDATE auftrag SET Kundennummer = :customerId, Ansprechpartner = -1 WHERE Auftragsnummer = :orderId;";
        DBAccess::updateQuery($query, [
            "orderId" => $orderId,
            "customerId" => $newCustomerId,
        ]);

        $query = "UPDATE angebot SET id_customer = :customerId WHERE order_id = :orderId;";
        DBAccess::updateQuery($query, [
            "orderId" => $orderId,
            "customerId" => $newCustomerId,
        ]);

        $query = "DELETE FROM fahrzeuge_auftraege WHERE id_auftrag = :orderId;";
        DBAccess::deleteQuery($query, [
            "orderId" => $orderId,
        ]);

        $query = "UPDATE invoice SET contact_id = NULL, address_id = NULL WHERE order_id = :orderId;";
        DBAccess::updateQuery($query, [
            "orderId" => $orderId,
        ]);

        JSONResponseHandler::returnOK();
    }

    public function getNotificationContent(): string
    {
        return "";
    }

    public function getNotificationType(): int
    {
        return 0;
    }

    public function getNotificationLink(): string
    {
        return "";
    }

    public function getNotificationSpecificId(): int
    {
        return 0;
    }
}
