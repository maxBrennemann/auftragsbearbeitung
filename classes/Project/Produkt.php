<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use Classes\Link;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Produkt
{

	private $price = null;
	private $produktnummer = null;
	private $bezeichnung = null;
	private $beschreibung = null;

	function __construct($produktnummer)
	{
		$data = DBAccess::selectAllByCondition("produkt", "Nummer", $produktnummer);
		if (!empty($data)) {
			$data = $data[0];
			$this->price = $data['Preis'];
			$this->produktnummer = $data['Nummer'];
			$this->bezeichnung = $data['Bezeichnung'];
			$this->beschreibung = $data['Beschreibung'];
		}
	}

	public function bekommePreis()
	{
	}

	public function getBezeichnung()
	{
		return $this->bezeichnung;
	}

	public function getBeschreibung()
	{
		return $this->beschreibung;
	}

	public function getPrice()
	{
		return $this->price;
	}

	public function getPriceWithTax()
	{
		return number_format((float) $this->price / 100, 2, ",", ".");
	}

	public function getProductId()
	{
		return $this->produktnummer;
	}

	public function getProduktLink()
	{
		return Link::getFrontOfficeLink("produkt") . "?id=" . $this->produktnummer;
	}

	public function getHTMLData()
	{
		return "";
	}

	public function getImages()
	{
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

	public function fillToArray($arr)
	{
		return "";
	}

	/**
	 * comment to this solution: https://stackoverflow.com/a/38467483, modified
	 */
	public function getAttributeTable()
	{
		$query = "SELECT product_combination.id, GROUP_CONCAT(attribute.value SEPARATOR ', ') AS `Werte` FROM attribute, product_combination JOIN product_attribute_combination ON product_attribute_combination.id_produkt_attribute = product_combination.id WHERE attribute.id = product_attribute_combination.attribute_id GROUP BY product_combination.id;";
		$data = DBAccess::selectQuery($query);
		return $data;
	}

	public static function createProduct(): int
	{
		$title = (string) Tools::get("title");
		$brand = (string) Tools::get("brand");
		$source = (string) Tools::get("source");
		$categoryId = (int) Tools::get("category");
		$price = (float) Tools::get("price");
		$purchasePrice = (float) Tools::get("purchasePrice");
		$description = (string) Tools::get("description");
		//$attributes = Tools::get("attributes");

		$newProductId = DBAccess::insertQuery("INSERT INTO produkt (Bezeichnung, Marke, Beschreibung, Einkaufspreis, Preis, einkaufs_id, id_category) VALUES (:title, :brand, :description, :purchasePrice, :price, :source, :idCategory)", [
			"title" => $title,
			"brand" => $brand,
			"description" => $description,
			"purchasePrice" => $purchasePrice,
			"price" => $price,
			"source" => $source,
			"idCategory" => $categoryId,
		]);

		JSONResponseHandler::sendResponse(["id" => $newProductId]);
		return $newProductId;
	}

	public static function addCombinations() {
		$productId = (int) Tools::get("id");
		$combinations = Tools::get("combinations");
		$combinations = json_decode($combinations);

		$data = array();
		foreach ($combinations as $c) {
			$id_product_attribute = DBAccess::insertQuery("INSERT INTO product_combination (id_produkt) VALUES ($productId)");
			
			foreach ($c as $value) {
				array_push($data, [$id_product_attribute, $value]);
			}
		}

		DBAccess::insertMultiple("INSERT INTO product_attribute_combination (id_produkt_attribute, attribute_id) VALUES ", $data);
	}

	public static function searchInProducts($searchQuery)
	{
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

	private static function sortByPercentage(&$mostSimilarProducts)
	{
		function cmp($a, $b)
		{
			return ($a[1] < $b[1]) ? -1 : (($a[1] > $b[1]) ? 1 : 0);
		}

		usort($mostSimilarProducts, "cmp");
	}

	private static function filterByPercentage($mostSimilarProducts)
	{
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

	private static function calculateSimilarity(&$mostSimilarProducts, $searchQuery, $text, $nummer)
	{
		similar_text($searchQuery, $text, $percentage);
		array_push($mostSimilarProducts, array($nummer, $percentage));
	}

	public static function addSource(): void
	{
		$name = Tools::get("name");
		$desc = Tools::get("desc");

		if ($name != null && $desc != null) {
			$id = DBAccess::insertQuery("INSERT INTO einkauf (name, description) VALUES (:name, :desc)", [
				"name" => $name,
				"desc" => $desc
			]);

			JSONResponseHandler::sendResponse(["id" => $id]);
			return;
		}

		JSONResponseHandler::throwError(400, "Name und Beschreibung müssen ausgefüllt sein");
	}

	public static function getSources(): array
	{
		return DBAccess::selectQuery("SELECT name, id FROM einkauf");
	}

	public static function getHTMLShortSummary($productnumber)
	{
		$product = new Produkt($productnumber);
		$html = "<div><h3>{$product->bezeichnung}</h3><span>Anzahl <input value=\"1\" id=\"{$productnumber}_getAmount\"></span><button onclick=\"chooseProduct($productnumber)\">Auswählen</button></div>";
		echo $html;
	}

	/*
	 * returns all products
	 */
	public static function getAllProducts($categoryId = null)
	{
		$products = array();
		if ($categoryId == null) {
			$sql = "SELECT Nummer FROM produkt";
		} else {
			$sql = "SELECT Nummer FROM produkt WHERE id_category = $categoryId";
		}

		$ids = DBAccess::selectQuery($sql);
		foreach ($ids as $id) {
			$id = $id["Nummer"];
			array_push($products, new Produkt($id));
		}

		return $products;
	}

	public static function getFiles($idProduct)
	{
		$files = DBAccess::selectQuery("SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ FROM dateien LEFT JOIN dateien_produkte ON dateien_produkte.id_datei = dateien.id WHERE dateien_produkte.id_produkt = $idProduct");

		for ($i = 0; $i < sizeof($files); $i++) {
			$link = Link::getResourcesShortLink($files[$i]['Datei'], "upload");

			if (getimagesize("upload/" . $files[$i]['Datei'])) {
				$html = "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\"><img class=\"img_prev_i\" src=\"$link\" width=\"40px\"><p class=\"img_prev\">{$files[$i]['originalname']}</p></a>";
			} else {
				$html = "<span><a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\">{$files[$i]['originalname']}</a></span>";
			}

			$files[$i]['Datei'] = $html;
		}

		$column_names = array(
			0 => array("COLUMN_NAME" => "Datei"),
			1 => array("COLUMN_NAME" => "Typ"),
			2 => array("COLUMN_NAME" => "Datum")
		);

		$t = new Table();
		$t->createByData($files, $column_names);
		$t->setType("dateien");
		$t->addActionButton("delete", $identifier = "id");

		return $t->getTable();
	}

	public static function update()
	{
		$productId = (int) Tools::get("id");
		$type = (string) Tools::get("type");
		$content = (string) Tools::get("content");

		$query = "";

		switch ($type) {
			case "productTitle":
				$query = "UPDATE produkt SET Bezeichnung = :content WHERE Nummer = :id";
				break;
			case "productDescription":
				$query = "UPDATE produkt SET Beschreibung = :content WHERE Nummer = :id";
				break;
			case "productPrice":
				$query = "UPDATE produkt SET Preis = :content WHERE Nummer = :id";
				break;
		}

		if ($query != "") {
			DBAccess::updateQuery($query, [
				"content" => $content,
				"id" => $productId
			]);

			JSONResponseHandler::sendResponse(["message" => "success"]);
		} else {
			JSONResponseHandler::throwError(400, "Type not found");
		}
	}
}
