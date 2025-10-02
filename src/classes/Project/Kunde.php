<?php

namespace Src\Classes\Project;

use Src\Classes\Link;
use Src\Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Kunde implements StatisticsInterface
{
    private int $kundennummer = 0;
    private string $vorname = "";
    private string $nachname = "";
    private string $firmenname = "";
    private string $strasse = "";
    private string $hausnummer = "";
    private int $postleitzahl = 0;
    private string $ort = "";
    private string $email = "";
    private string $invoiceEmail = "";
    private bool $autoSendInvoice = false;
    private string $telefonFestnetz = "";
    private string $telefonMobil = "";
    private string $website = "";
    private string $fax = "";
    private string $note = "";

    /** @var Address[] */
    private array $addresses = [];

    public function __construct(int $kundennummer)
    {
        $data = DBAccess::selectQuery("SELECT * 
			FROM kunde
			LEFT JOIN `address` ON address.id = kunde.id_address_primary 
			WHERE Kundennummer = :customerId;", [
            "customerId" => $kundennummer,
        ]);

        if (empty($data)) {
            throw new \Exception("Customer id does not exist or cannot be found");
        }

        $data = $data[0];

        $this->kundennummer = $data["Kundennummer"];
        $this->vorname = $data["Vorname"] ?? "";
        $this->nachname = $data["Nachname"] ?? "";
        $this->firmenname = $data["Firmenname"] ?? "";
        $this->strasse = $data["strasse"] ?? "";
        $this->hausnummer = $data["hausnr"] ?? "";
        $this->postleitzahl = (int) $data["plz"];
        $this->ort = $data["ort"] ?? "";
        $this->email = $data["Email"] ?? "";
        $this->invoiceEmail = $data["invoice_email"] ?? "";
        $this->autoSendInvoice = $data["auto_send_mail"] == "1";
        $this->telefonFestnetz = $data["TelefonFestnetz"] ?? "";
        $this->telefonMobil = $data["TelefonMobil"] ?? "";
        $this->website = $data["Website"] ?? "";
        $this->fax = $data["fax"] ?? "";
        $this->note = $data["note"] ?? "";
    }

    public function getKundennummer(): int
    {
        return $this->kundennummer;
    }

    public function getVorname(): string
    {
        return $this->vorname;
    }

    public function getNachname(): string
    {
        return $this->nachname;
    }

    public function getFirmenname(): string
    {
        return $this->firmenname;
    }

    public function getAlternativeName(): string
    {
        if ($this->firmenname != "") {
            return $this->firmenname;
        }

        return $this->getName();
    }

    public function getFrontOfficeName(): string
    {
        $name = $this->getAlternativeName();
        $name = trim($name);
        if ($name == "") {
            return "Zum Kunden";
        }
        return $name;
    }

    public function getStrasse(int $id = 0): string
    {
        if ($id != 0) {
            $this->loadAddresses();

            if (array_key_exists($id, $this->addresses)) {
                return $this->addresses[$id]->getStrasse();
            } else {
                return "";
            }
        }

        return $this->strasse;
    }

    public function getHausnummer(int $id = 0): string
    {
        if ($id != 0) {
            $this->loadAddresses();
            if (array_key_exists($id, $this->addresses)) {
                return $this->addresses[$id]->getHausnummer();
            } else {
                return "";
            }
        }

        return $this->hausnummer;
    }

    public function getPostleitzahl(int $id = 0): string
    {
        $plz = $this->postleitzahl;

        if ($id != 0) {
            $this->loadAddresses();
            if (array_key_exists($id, $this->addresses)) {
                $plz = (string) $this->addresses[$id]->getPostleitzahl();
            } else {
                $plz = "";
            }
        }

        if ($plz == 0) {
            return "";
        }

        return $plz;
    }

    public function getOrt(int $id = 0): string
    {
        if ($id != 0) {
            $this->loadAddresses();
            if (array_key_exists($id, $this->addresses)) {
                return $this->addresses[$id]->getOrt();
            } else {
                return "";
            }
        }
        return $this->ort;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getInvoiceEmail(): string
    {
        return $this->invoiceEmail;
    }

    public function getAutoSendInvoice(): bool
    {
        return $this->autoSendInvoice;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function getName(): string
    {
        return $this->getVorname() . " " . $this->getNachname();
    }

    public function getTelefonFestnetz(): string
    {
        return $this->telefonFestnetz;
    }

    public function getTelefonMobil(): string
    {
        return $this->telefonMobil;
    }

    public function getFax(): string
    {
        return $this->fax;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getOrderIds(): array
    {
        $query = "SELECT Auftragsnummer FROM auftrag WHERE Kundennummer = :kdnr ORDER BY Auftragsnummer DESC";
        $data = DBAccess::selectQuery($query, [
            "kdnr" => $this->kundennummer
        ]);

        return $data;
    }

    public function getOrderCards(): string
    {
        $data = $this->getOrderIds();
        $orders = [];

        foreach ($data as $row) {
            $order = new Auftrag((int) $row["Auftragsnummer"]);
            $orders[] = $order->getOrderCardData();
        }

        ob_start();
        insertTemplate("public/views/orderCardView.php", [
            "orders" => $orders,
        ]);
        $content = ob_get_clean();

        return $content;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getContactPersons(): array
    {
        $query = "SELECT a.Nummer AS id, a.Vorname AS firstName, a.Nachname AS lastName, a.Email AS email
			FROM ansprechpartner a 
			WHERE a.Kundennummer = :kdnr";
        $data = DBAccess::selectQuery($query, [
            "kdnr" => $this->getKundennummer(),
        ]);

        return $data;
    }

    public function getNotizen(): string
    {
        return $this->note;
    }

    public function recalculate(): void {}

    private function loadAddresses(): void
    {
        if ($this->addresses != null) {
            return;
        }

        $addresses = Address::loadAllAddresses($this->kundennummer);
        foreach ($addresses as $address) {
            $newAddress = Address::loadAddress((int) $address["id"]);
            $this->addresses[$address["id"]] = $newAddress;
        }
    }

    public static function addAddress(int $id_customer, string $strasse, string $hausnummer, int $postleitzahl, string $ort, string $zusatz, string $land, int $art = 3): Address
    {
        return Address::createNewAddress($id_customer, $strasse, $hausnummer, $postleitzahl, $ort, $zusatz, $land, $art);
    }

    public static function addAddressAjax(): void
    {
        $kdnr = (int) $_POST["customer"];
        $plz = (int) $_POST["plz"];
        $ort = $_POST["ort"];
        $strasse = $_POST["strasse"];
        $hnr = $_POST["hnr"];
        $zusatz = $_POST["zusatz"];
        $land = $_POST["land"];
        Kunde::addAddress($kdnr, $strasse, $hnr, $plz, $ort, $zusatz, $land);
        echo json_encode(Address::loadAllAddresses($kdnr));
    }

    public function getHTMLShortSummary(): string
    {
        $link = Link::getPageLink("kunde") . "?id=" . $this->kundennummer;
        $text = "<div class=\"shortSummary\"><div class=\"shortSummaryHeader\">";
        if ($this->firmenname == "") {
            $text .= "<a href=\"$link\">{$this->vorname} {$this->nachname}</a></div>";
        } else {
            $text .= "<a href=\"$link\">{$this->firmenname}</a></div>";
        }
        $text .= "<p>{$this->strasse} {$this->hausnummer}<br>{$this->postleitzahl} {$this->ort}<br>";

        if ($this->telefonFestnetz != null) {
            $text .= "☎ {$this->telefonFestnetz}<br>";
        }

        if ($this->telefonMobil != null) {
            $text .= "✆ {$this->telefonMobil}<br>";
        }

        $text .= "<br>";

        if ($this->email != null) {
            $text .= "@ <a href=\"mailto:{$this->email}\">{$this->email}</a><br>";
        }

        if ($this->website != null) {
            $text .= "🔗 <a href=\"{$this->website}\">Zur Website</a></div>";
        } else {
            $text .= "</p></div>";
        }

        return $text;
    }

    public static function getContacts(): void
    {
        $kdnr = (int) Tools::get("id");
        $data = DBAccess::selectQuery("SELECT Nummer AS id, Vorname AS firstName, Nachname AS lastName, Email AS email 
			FROM ansprechpartner 
			WHERE Kundennummer = :kdnr", [
            "kdnr" => $kdnr,
        ]);

        JSONResponseHandler::sendResponse($data);
    }

    public static function addCustomer(): void
    {
        /* insert customer data */
        $query = "INSERT INTO kunde (Firmenname, Anrede, Vorname, Nachname, Email, TelefonFestnetz, TelefonMobil, Website, note) VALUES (:firmenname, :anrede, :vorname, :nachname, :email, :telfestnetz, :telmobil, :website, :note)";

        $customerId = DBAccess::insertQuery($query, [
            "firmenname" => Tools::get("customerName") ?? "",
            "anrede" => (int) Tools::get("anrede"),
            "vorname" => Tools::get("prename") ?? "",
            "nachname" => Tools::get("surname") ?? "",
            "email" => Tools::get("companyemail") ?? "",
            "telfestnetz" => Tools::get("telfestnetz") ?? "",
            "telmobil" => Tools::get("telmobil") ?? "",
            "website" => Tools::get("website") ?? "",
            "note" => Tools::get("notes"),
        ]);

        /* insert address data */
        $query = "INSERT INTO `address` (id_customer, strasse, hausnr, plz, ort, zusatz, country) VALUES (:id_customer, :strasse, :hausnr, :plz, :ort, :zusatz, :country)";

        $addressId = DBAccess::insertQuery($query, [
            "id_customer" => $customerId,
            "strasse" => Tools::get("street"),
            "hausnr" => Tools::get("houseNumber"),
            "plz" => (int) Tools::get("plz"),
            "ort" => Tools::get("city"),
            "zusatz" => Tools::get("addressAddition"),
            "country" => Tools::get("country"),
        ]);

        /* update customer data */
        DBAccess::updateQuery("UPDATE kunde SET id_address_primary = :addressId WHERE Kundennummer = :customerId", [
            "addressId" => $addressId,
            "customerId" => $customerId,
        ]);

        /* insert ansprechpartner data */
        if (Tools::get("type") == "company") {
            $query = "INSERT INTO ansprechpartner (Kundennummer, Vorname, Nachname, Email, Durchwahl, Mobiltelefonnummer) VALUES (:customerId, :vorname, :nachname, :email, :durchwahl, :mobiltelefonnummer)";
            DBAccess::insertQuery($query, [
                "customerId" => $customerId,
                "vorname" => Tools::get("contactPrename"),
                "nachname" => Tools::get("contactSurname"),
                "email" => Tools::get("emailaddress"),
                "durchwahl" => Tools::get("phoneExtension"),
                "mobiltelefonnummer" => Tools::get("mobileNumber"),
            ]);
        }

        $link = Link::getPageLink("kunde");
        $link .= "?id=" . $customerId;

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "link" => $link,
        ]);
    }

    public static function updateCustomer(): void
    {
        $query = "UPDATE kunde SET
				Vorname = :prename,
				Nachname = :lastname,
				Firmenname = :companyname,
				Email = :email,
                invoice_email = :invoiceEmail,
				Website = :website,
				TelefonFestnetz = :phoneLandline,
				TelefonMobil = :phoneMobile,
				Fax = :fax
			WHERE Kundennummer = :id;";
        DBAccess::updateQuery($query, [
            "prename" => Tools::get("prename") ?? "",
            "lastname" => Tools::get("lastname") ?? "",
            "companyname" => Tools::get("companyname") ?? "",
            "email" => Tools::get("email") ?? "",
            "invoiceEmail" => Tools::get("invoiceEmail") ?? "",
            "website" => Tools::get("website"),
            "phoneLandline" => Tools::get("phoneLandline") ?? "",
            "phoneMobile" => Tools::get("phoneMobile") ?? "",
            "fax" => Tools::get("fax"),
            "id" => (int) Tools::get("id"),
        ]);
        JSONResponseHandler::returnOK();
    }

    /**
     * @return Kunde[]
     */
    public static function getAllCustomerOverviews(): array
    {
        $query = "SELECT Kundennummer FROM kunde ORDER BY CONCAT(Firmenname, Nachname);";
        $data = DBAccess::selectQuery($query);

        $customers = [];
        foreach ($data as $row) {
            $id = (int) $row["Kundennummer"];
            $customers[] = new Kunde($id);
        }

        return $customers;
    }

    public static function getColors(): void
    {
        $query = "SELECT Auftragsnummer as id_order, color_name, hex_value, short_name, producer
			FROM color, color_auftrag, auftrag 
			WHERE Kundennummer = :kdnr 
				AND color.id = color_auftrag.id_color 
				AND color_auftrag.id_auftrag = Auftragsnummer";
        $data = DBAccess::selectQuery($query, [
            "kdnr" => Tools::get("id"),
        ]);

        $data = Color::convertHex($data);
        JSONResponseHandler::sendResponse($data);
    }

    public static function delete(): never
    {
        JSONResponseHandler::throwError(501, "not implemented");
    }

    public static function setNote(): void
    {
        $id = Tools::get("id");
        $note = Tools::get("note");
        DBAccess::updateQuery("UPDATE kunde SET note = :note WHERE Kundennummer = :customerId", [
            "note" => $note,
            "customerId" => $id,
        ]);
        JSONResponseHandler::returnOK();
    }

    public static function addVehicle(): void
    {
        $id = Tools::get("id");
        $licensePlate = Tools::get("licensePlate");
        $name = Tools::get("name");
        $orderId = Tools::get("orderId");

        $vehicleId = DBAccess::insertQuery("INSERT INTO fahrzeuge (Kennzeichen, Fahrzeug, Kundennummer) VALUES (:licensePlate, :name, :id)", [
            "licensePlate" => $licensePlate,
            "name" => $name,
            "id" => $id,
        ]);

        DBAccess::insertQuery("INSERT INTO fahrzeuge_auftraege (id_fahrzeug, id_auftrag) VALUES (:vehicleId, :orderId)", [
            "vehicleId" => $vehicleId,
            "orderId" => $orderId
        ]);

        OrderHistory::add($orderId, $vehicleId, OrderHistory::TYPE_VEHICLE, OrderHistory::STATE_ADDED);

        JSONResponseHandler::sendResponse([
            "id" => $vehicleId,
        ]);
    }

    public static function searchCustomers(): void
    {
        $query = Tools::get("query");
        $results = SearchController::search("type:kunde $query", 10);

        $html = "";
        foreach ($results as $result) {
            $id = (int) $result["data"]["Kundennummer"];
            $customer = new Kunde($id);
            $html .= TemplateController::getTemplate("customerCardTemplate", [
                "customer" => $customer,
            ]);
        }

        JSONResponseHandler::sendResponse([
            "status" => "OK",
            "template" => $html,
        ]);
    }
}
