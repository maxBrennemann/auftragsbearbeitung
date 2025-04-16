<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

/*
* Adressarten:
* (1) Standardadresse/ Rechnungsadresse
* (2) Lieferadresse
* (3) Filiale
* (4) Sonstige Adresse
*/

class Address
{

    private string $strasse = "";
    private string $hausnummer = "";
    private int $postleitzahl = 0;
    private string $ort = "";
    private string$zusatz = "";
    private int $art = 0;
    private string $land = "";

    function __construct() {}

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

    public static function loadAddress(int $addressId)
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
        } else {
            return null;
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
            $formattedAddresses[$address["id"]] = $address["strasse"] . " " . $address["hausnr"] . ", " . $address["plz"] . " " . $address["ort"];
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

    public static function createNewAddress($id_customer, $strasse, $hausnummer, $postleitzahl, $ort, $zusatz = "", $land = "Deutschland", $art = 3)
    {
        $addressInstance = new Address();
        $addressInstance->strasse = $strasse;
        $addressInstance->hausnummer = $hausnummer;
        $addressInstance->postleitzahl = $postleitzahl;
        $addressInstance->ort = $ort;
        $addressInstance->zusatz = $zusatz;
        $addressInstance->art = $art;
        $addressInstance->land = $land;

        $query = "INSERT INTO address (id_customer, ort, plz, strasse, hausnr, zusatz, country, art) VALUES ($id_customer, '$ort', $postleitzahl, '$strasse', '$hausnummer', '$zusatz', '$land', $art)";
        DBAccess::insertQuery($query);

        return $addressInstance;
    }
}
