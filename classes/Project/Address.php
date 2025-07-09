<?php

namespace Classes\Project;

use Classes\Models\Model;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

/*
* Adressarten:
* (1) Standardadresse/ Rechnungsadresse
* (2) Lieferadresse
* (3) Filiale
* (4) Sonstige Adresse
*/

class Address extends Model
{
    public const DEFAULT_ADDRESS = 1;
    public const DELIVERY_ADDRESS = 2;
    public const BRANCH_ADDRESS = 3;
    public const VARIOUS_ADDRESS = 4;

    private string $strasse = "";
    private string $hausnummer = "";
    private int $postleitzahl = 0;
    private string $ort = "";
    private string $zusatz = "";
    private int $art = 0;
    private string $land = "";

    public function __construct()
    {
    }

    public function getStrasse(): string
    {
        return $this->strasse;
    }

    public function getHausnummer()
    {
        return $this->hausnummer;
    }

    public function getPostleitzahl()
    {
        return $this->postleitzahl;
    }

    public function getOrt()
    {
        return $this->ort;
    }

    public static function loadAddress(int $addressId): Address
    {
        $addressInstance = new Address();

        $data = DBAccess::selectAllByCondition("address", "id", $addressId);
        if (!empty($data)) {
            $data = $data[0];
            $addressInstance->strasse = $data['strasse'];
            $addressInstance->hausnummer = $data['hausnr'];
            $addressInstance->postleitzahl = $data['plz'];
            $addressInstance->ort = $data['ort'];
            $addressInstance->zusatz = $data['zusatz'];
            $addressInstance->art = $data['art'];
        }

        return $addressInstance;
    }

    public static function loadAllAddresses(int $customerId): array
    {
        $data = DBAccess::selectQuery("SELECT * FROM `address` WHERE id_customer = :customerId ORDER BY art", [
            "customerId" => $customerId,
        ]);
        return $data;
    }

    public static function getAllAdressesFormatted(int $customerId): array
    {
        $addresses = self::loadAllAddresses($customerId);
        $formattedAddresses = [];

        foreach ($addresses as $address) {
            $id = (int) $address["id"];
            $formattedAddresses[$id] = $address["strasse"] . " " . $address["hausnr"] . ", " . $address["plz"] . " " . $address["ort"];
        }

        return $formattedAddresses;
    }

    public static function hasAddress(int $kdnr, int $addressId): bool
    {
        $query = "SELECT id FROM address WHERE id = $addressId AND id_customer = :customerId;";
        $result = DBAccess::selectQuery($query, [
            "customerId" => $kdnr,
        ]);

        if (empty($result)) {
            return false;
        }
        return true;
    }

    public static function createNewAddress($id_customer, $strasse, $hausnummer, $postleitzahl, $ort, $zusatz = "", $land = "Deutschland", $art = 3): Address
    {
        $query = "INSERT INTO address (id_customer, ort, plz, strasse, hausnr, zusatz, country, art) VALUES (:idCustomer, :ort, :plz, :strasse, :hausnummer, :zusatz, :land, :art)";
        $id = DBAccess::insertQuery($query, [
            "idCustomer" => $id_customer,
            "ort" => $ort,
            "plz" => $postleitzahl,
            "strasse" => $strasse,
            "hausnummer" => $hausnummer,
            "zusatz" => $zusatz,
            "land" => $land,
            "art" => $art,
        ]);

        return self::loadAddress($id);
    }

    public static function addAddress()
    {
        $customerId = Tools::get("id");
    }
}
