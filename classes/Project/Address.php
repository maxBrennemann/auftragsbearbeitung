<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

/*
* Adressarten:
* 1 - Standeardadresse / Rechnungsadresse
* 2 - Lieferadresse, wenn keine Lieferadresse definiert ist, ist diese auch die Standardadresse
* 3 - zusätzliche Adresse, bspw. Heimadresse, anderer Standort oder Filiale, usw.
*/

class Address
{

    private $strasse = null;
    private $hausnummer = null;
    private $postleitzahl = null;
    private $ort = null;
    private $zusatz = null;
    private $art = null;
    private $land = null;

    function __construct() {}

    public function getStrasse()
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

    public static function loadAddress($addressId)
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

    public static function loadAllAddresses($kdnr)
    {
        $data = DBAccess::selectQuery("SELECT * FROM `address` WHERE id_customer = $kdnr ORDER BY art");
        return $data;
    }

    public static function hasAddress($kdnr, $addressId)
    {
        $query = "SELECT id FROM address WHERE id = $addressId AND id_customer = $kdnr";
        $result = DBAccess::selectQuery($query);
        if (empty($result))
            return false;
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
