<?php 

/*
* Adressarten:
* 1 - Standeardadresse / Rechnungsadresse
* 2 - Lieferadresse, wenn keine Lieferadresse definiert ist, ist diese auch die Standardadresse
* 3 - zusätzliche Adresse, bspw. Heimadresse, anderer Standort oder Filiale, usw.
*/

class Adress {

	private $strasse = null;
	private $hausnummer = null;
    private $postleitzahl = null;
    private $ort = null;
    private $zusatz = null;
    private $art = null;

    function __construct() {
        
    }

    public static function loadAdress($adressId) {
        $adressInstance = new Adress();

        $data = DBAccess::selectAllByCondition("adress", "id", $adressId);
		if (!empty($data)) {
            $data = $data[0];
			$adressInstance->strasse = $data['strasse'];
			$adressInstance->hausnummer = $data['hausnr'];
			$adressInstance->postleitzahl = $data['plz'];
			$adressInstance->ort = $data['ort'];
            $adressInstance->zusatz = $data['zusatz'];
            $adressInstance->art = $data['art'];
        } else {
            return null;
        }

        return $adressInstance;
    }

    public static function createNewAdress($id_customer, $strasse, $hausnummer, $postleitzahl, $ort, $zusatz = "", $land = "Deutschland", $art = 1) {
        $adressInstance = new Adress();
        $adressInstance->strasse = $strasse;
        $adressInstance->hausnummer = $hausnummer;
        $adressInstance->postleitzahl = $postleitzahl;
        $adressInstance->ort = $ort;
        $adressInstance->zusatz = $zusatz;
        $adressInstance->art = $art;
        $adressInstance->land = $land;

        $query = "INSERT INTO adress (id_customer, ort, plz, strasse, hausnr, zusatz, country, art) VALUES ($id_customer, '$ort', $postleitzahl, '$strasse', '$hausnummer', '$zusatz', '$land', $art)";
        DBAccess::insertQuery($query);
        echo "ok";
        return $adressInstance;
    }

    public static function getAdressForm() {
        $html = "
            <div class=\"adressForm\">
                <h4>Neue Adressdaten</h4>
                <input id=\"adress_plz\" placeholder=\"PLZ\">
                <br>
                <input id=\"adress_ort\" placeholder=\"Ort\">
                <br>
                <input id=\"adress_strasse\" placeholder=\"Straße\">
                <br>
                <input id=\"adress_nr\" placeholder=\"Hausnummer\">
                <br>
                <input id=\"adress_zusatz\" placeholder=\"Adresszusatz\">
                <br>
                <input id=\"adress_land\" placeholder=\"Land\">
                <br>
                <button onclick=\"sendAdressForm();\">Abschicken</button>
            </div>
        ";
        return $html;
    }

}

?>