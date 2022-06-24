<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('classes/DBAccess.php');
require_once('classes/project/Image.php');

class Produkt { 
    
    private $preis = null;
    private $produktnummer = null;
    private $bezeichnung = null;
    private $beschreibung = null;

	function __construct($produktnummer) {
		$data = DBAccess::selectAllByCondition("produkt", "Nummer", $produktnummer);
		if (!empty($data)) {
			$data = $data[0];
			$this->preis = $data['Preis'];
			$this->produktnummer = $data['Nummer'];
			$this->bezeichnung = $data['Bezeichnung'];
			$this->beschreibung = $data['Beschreibung'];
		}
	}

    public function bekommePreis() {
        
    }

	public function getBezeichnung() {
		return $this->bezeichnung;
	}

	public function getBeschreibung() {
		return $this->beschreibung;
	}

	public function getPreis() {
		return $this->preis;
	}

	public function getPreisBrutto() {
		return number_format((float) $this->preis, 2, ",", ".");
	}

	public function getProductId() {
		return $this->produktnummer;
	}

	public function getProduktLink() {
		return Link::getFrontOfficeLink("produkt") . "?id=" . $this->produktnummer;
	}

	public function getHTMLData() {
		return "";
	}

	public function getImages() {
		$query = "SELECT DISTINCT id FROM dateien LEFT JOIN dateien_produkte ON dateien_produkte.id_datei = dateien.id WHERE dateien_produkte.id_produkt = $this->produktnummer";
		$data = DBAccess::selectQuery($query);

		$images = array();

		foreach ($data as $d) {
			array_push($images, new Image($d['id']));
		}

		/* checks if array is empty, if so, add default image */
		if (sizeof($images) == 0) {
			array_push($images, Image::setDefault());
		}

		return $images;
	}

	public function fillToArray($arr) {
		return "";
	}

	public static function createProduct($title, $marke, $desc, $ekNetto, $vkNetto, $quelle, $attData) {
		$productId = DBAccess::insertQuery("INSERT INTO produkt (Bezeichnung, Marke, Beschreibung, Einkaufspreis, Preis, einkaufs_id) VALUES ('$title', '$marke', '$desc', '$ekNetto', '$vkNetto', $quelle)");
		// attData muss noch implementiert werden

		//if ($attributeData != null) {
			$attributeData = json_decode($attData);
			$query = "INSERT INTO produkt_varianten (product_id, groesse, farb_id, menge, preis_ek, preis) VALUES ";

			foreach ($attributeData as $rowData => $rowValue) {
				$groesse = $rowValue->{'groesseId'};
				$farbe = $rowValue->{'farbeId'};
				$menge = $rowValue->{'menge'};
				$ek = str_replace(',', '.', $rowValue->{'ek'});
				$price =  str_replace(',', '.', $rowValue->{'price'});
				$query .= "($productId, {$groesse}, {$farbe}, {$menge}, '{$ek}', '{$price}'),";
			}

			$query = rtrim($query, ',');
			DBAccess::insertQuery($query);
		//}
	}

	public static function getSearchTable($searchQuery) {
		$productIds = self::searchInProducts($searchQuery);
		$query = "";
	}

	public static function searchInProducts($searchQuery) {
		$products = DBAccess::selectQuery("SELECT Nummer, Bezeichnung, Beschreibung FROM produkt");
		$mostSimilarProducts = array();

		foreach ($products as $product) {
			self::calculateSimilarity($mostSimilarProducts, $searchQuery, $product['Bezeichnung'], $product['Nummer']);
			self::calculateSimilarity($mostSimilarProducts, $searchQuery, $product['Beschreibung'], $product['Nummer']);
		}

		self::sortByPercentage($mostSimilarProducts);
		$mostSimilarProducts = self::filterByPercentage($mostSimilarProducts);

		return array_slice($mostSimilarProducts, 0, 10);
	}

	private static function sortByPercentage(&$mostSimilarProducts) {
		function cmp($a, $b) {
			return ($a[1] < $b[1]) ? -1 : (($a[1] > $b[1]) ? 1 : 0);
		}

		usort($mostSimilarProducts, "cmp");
	}

	private static function filterByPercentage($mostSimilarProducts) {
		$filteredArray = array();
		foreach ($mostSimilarProducts as $product) {
			if (end($filteredArray)[0] == $product[0]) {
				if ($product[1] > end($filteredArray)[1]) {
					$filteredArray[sizeof($filteredArray) - 1] = $product;
				}
			} else {
				array_push($filteredArray, $product);
			}
		}

		return $filteredArray;
	}

	private static function calculateSimilarity(&$mostSimilarProducts, $searchQuery, $text, $nummer) {
		similar_text($searchQuery, $text, $percentage);
		array_push($mostSimilarProducts, array($nummer, $percentage));
	}

	public static function addSource() {
		if (isset($_POST['name']) && isset($_POST['desc'])) {
			$name = $_POST['name'];
			$desc = $_POST['desc'];

			DBAccess::insertQuery("INSERT INTO einkauf (name, description) VALUES ('$name', '$desc')");
		}
	}

	public static function getSelectSource() {
		$quelle = DBAccess::selectQuery("SELECT name, id FROM einkauf");

		$string = "<select id=\"selectSource\" required><option value=\"-1\" selected disabled>Bitte auswählen</option>";
		foreach ($quelle as $q) {
			$string .= "<option value=\"" . $q['id'] . "\">" . $q['name'] . "</option>";
		}
				
		$string .= "<option value=\"addNew\">Neue Option hinzufügen</option></select>";
		echo $string;
	}

	public static function getHTMLShortSummary($productnumber) {
		$product = new Produkt($productnumber);
		$html = "<div><h3>{$product->bezeichnung}</h3><span>Anzahl <input value=\"1\" id=\"{$productnumber}_getAmount\"></span><button onclick=\"chooseProduct($productnumber)\">Auswählen</button></div>";
		echo $html;
	}

	/*
	 * returns all products
	 */
	public static function getAllProducts($categoryId = null) {
		$products = array();
		if ($categoryId == null) {
			$sql = "SELECT Nummer FROM produkt";
			$ids = DBAccess::selectQuery($sql);

			foreach ($ids as $id) {
				$id = $id["Nummer"];
				array_push($products, new Produkt($id));
			}
		} else {
			$sql = "SELECT Nummer FROM produkt WHERE categoryId = $categoryId";
		}

		return $products;
	}

}

?>