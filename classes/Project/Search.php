<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

use Classes\Link;

class Search
{

	/*
	 * returns a table with the search results
	 */
	public static function getSearchTable($searchQuery, $searchType, $retUrl = null, $getShortSummary = false)
	{
		$ids = [];
		$query = "";
		$columnNames = [];

		switch ($searchType) {
			case "kunde":
				$ids = self::searchInCustomers($searchQuery);
				$query = "SELECT * FROM kunde WHERE Kundennummer = ";
				$columnNames = DBAccess::selectColumnNames($searchType);

				if (!$getShortSummary)
					break;

				$data = "";
				$ids = array_reverse($ids);
				foreach ($ids as $id) {
					$data .= (new Kunde($id[0]))->getHTMLShortSummary();
				}
				return $data;
				break;
			case "produkt":
				$ids = self::searchInProducts($searchQuery);
				$query = "SELECT Nummer, Bezeichnung, Beschreibung, Marke, CONCAT(FORMAT(Preis / 100,2,'de_DE'), ' €') AS Preis,  CONCAT(FORMAT(Einkaufspreis / 100,2,'de_DE'), ' €') AS Einkaufspreis FROM produkt WHERE Nummer = ";
				$columnNames = array(
					0 => array("COLUMN_NAME" => "Bezeichnung"),
					1 => array("COLUMN_NAME" => "Beschreibung"),
					2 => array("COLUMN_NAME" => "Marke"),
					3 => array("COLUMN_NAME" => "Preis"),
					4 => array("COLUMN_NAME" => "Einkaufspreis")
				);

				if (!$getShortSummary)
					break;

				$data = "";
				$ids = array_reverse($ids);
				foreach ($ids as $id) {
					$data .= Produkt::getHTMLShortSummary($id[0]);
				}

				if (empty($data)) {
					$data = "<span>Keine Ergebnisse!</span>";
				}

				return $data;
				break;
			case "order":
				$ids = self::searchInOrders($searchQuery);
				$query = "SELECT * FROM auftrag WHERE Auftragsnummer = ";
				$columnNames = DBAccess::selectColumnNames($searchType);

				if (!$getShortSummary)
					break;

				$data = "";
				$ids = array_reverse($ids);
				$data = Auftrag::getAuftragsliste($ids, 0);
				// TODO: fixen, da getAuftragsliste überarbeitet wurde

				if (empty($data)) {
					$data = "<span>Keine Ergebnisse!</span>";
				}

				return $data;

				break;
			case "posten":
				$ids = self::searchInPosten($searchQuery);
				$query = "SELECT * FROM postenData WHERE Auftragsnummer = ";
				$columnNames = DBAccess::selectColumnNames($searchType);

				if (!$getShortSummary)
					break;

				return "";
				break;
			case "wiki":
				$ids = self::searchInWiki($searchQuery);
				$query = "SELECT * FROM wiki WHERE id = ";
				$columnNames = DBAccess::selectColumnNames($searchType);

				if (!$getShortSummary)
					break;

				return "";
				break;
		}

		$data = array();

		foreach ($ids as $id) {
			$column = DBAccess::selectQuery($query . $id[0]);
			$column = $column[0];
			array_push($data, $column);
		}
		$data = array_reverse($data);

		$table = new Table();
		$table->createByData($data, $columnNames);

		/* TODO:quick fix, customer search results can be clicked */
		if ($searchType == "kunde") {
		}

		return $table->getTable();
	}

	/**
	 * suchen in:
	 * Produktbeschreibung und Produkttitel
	 * 
	 * Auftragsbeschreibung und Auftragsbezeichnung
	 * Auftragsnotizen
	 * Auftragsbearbeitungsschritten
	 * Auftragsposten
	 * 
	 * Kundennamen und Firmennamen
	 * Kundennotizen	 
	 */
	public static function globalSearch($searchQuery)
	{
		$results = array();

		$customerResults = self::searchInCustomers($searchQuery);
		if (!empty($customerResults)) {
			$results[0] = [
				"groupName" => "Kunden",
				"results" => $customerResults
			];
		}

		$productResults = self::searchInProducts($searchQuery);
		if (!empty($productResults)) {
			$results[1] = [
				"groupName" => "Produkte",
				"results" => $productResults
			];
		}

		$orderResults = self::searchInOrders($searchQuery);
		if (!empty($orderResults)) {
			$results[2] = [
				"groupName" => "Aufträge",
				"results" => $orderResults
			];
		}

		$postenResults = self::searchInPosten($searchQuery);
		if (!empty($postenResults)) {
			$results[3] = [
				"groupName" => "Posten",
				"results" => $postenResults
			];
		}

		$wikiResults = self::searchInWiki($searchQuery);
		if (!empty($wikiResults)) {
			$results[4] = [
				"groupName" => "Wiki",
				"results" => $wikiResults
			];
		}

		$html = "";
		foreach ($results as &$resultType) {
			$name = "";

			$html .= "<h3>$name</h3>";
			foreach ($resultType["results"] as &$item) {
				$data = null;
				$link = "";
				switch ($resultType["groupName"]) {
					case "Kunden":
						$query = "SELECT CONCAT(COALESCE(Firmenname, ''), ' ', COALESCE(Vorname, ''), ' ', COALESCE(Nachname, '')) AS `message` FROM kunde WHERE Kundennummer = :kdnr";
						$data = DBAccess::selectQuery($query, [
							"kdnr" => $item[0],
						])[0];

						$link = Link::getPageLink("kunde") . "?id=" . $item[0];
						break;
					case "Produkte":
						$query = "SELECT CONCAT(COALESCE(Marke, ''), ' ', COALESCE(Bezeichnung, ''), ' ', COALESCE(Beschreibung, '')) AS `message` FROM produkt WHERE Nummer = :id";
						$data = DBAccess::selectQuery($query, [
							"id" => $item[0],
						])[0];

						$link = Link::getPageLink("produkt") . "?id=" . $item[0];
						break;
					case "Aufträge":
						$query = "SELECT CONCAT(COALESCE(Auftragsbezeichnung, ''), ' ', COALESCE(Auftragsbeschreibung, '')) AS `message` FROM auftrag WHERE Auftragsnummer = :id";
						$data = DBAccess::selectQuery($query, [
							"id" => $item[0],
						])[0];

						$link = Link::getPageLink("auftrag") . "?id=" . $item[0];
						break;
					case "Posten":
						$query = "SELECT COALESCE(Beschreibung, ' ') AS `message` FROM postendata WHERE Postennummer = :id";
						$data = DBAccess::selectQuery($query, [
							"id" => $item[0],
						])[0];

						$link = Link::getPageLink("auftrag") . "?id=" . $item[0];
						break;
					case "Wiki":
						$query = "SELECT COALESCE(title, ' ') AS `message` FROM wiki_articles WHERE id = :id";
						$data = DBAccess::selectQuery($query, [
							"id" => $item[0],
						])[0];

						$link = Link::getPageLink("wiki") . "?id=" . $item[0];
						break;
				}

				$item["link"] = $link;
				$item["message"] = $data["message"];
			}
		}

		ob_start();
		insertTemplate('files/res/views/ajaxSearchView.php', [
			"resultGroups" => $results,
		]);
		echo ob_get_clean();
	}

	/*
	 * searches in customer data
	 */
	private static function searchInCustomers($searchQuery)
	{
		$query = "SELECT kunde.Kundennummer, Vorname, Nachname, Firmenname, note AS Notiz 
			FROM kunde
			WHERE (
				Vorname LIKE '%$searchQuery%' 
				OR Nachname LIKE '%$searchQuery%' 
				OR Firmenname LIKE '%$searchQuery%' 
				OR Notiz LIKE '%$searchQuery%'
			)";
		$kunden = DBAccess::selectQuery($query);
		$mostSimilar = array();

		foreach ($kunden as $kunde) {
			self::calculateSimilarity($mostSimilar, $searchQuery, $kunde['Vorname'] . " " . $kunde['Nachname'], $kunde['Kundennummer']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $kunde['Firmenname'], $kunde['Kundennummer']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $kunde['Notiz'], $kunde['Kundennummer']);
		}

		self::sortByPercentage($mostSimilar);
		$mostSimilar = self::filterByPercentage($mostSimilar);

		return array_slice($mostSimilar, 0, 10);
	}

	/*
	 * searches in product data
	 */
	private static function searchInProducts($searchQuery)
	{
		$products = DBAccess::selectQuery("SELECT Nummer, Bezeichnung, Beschreibung FROM produkt");
		$mostSimilar = array();

		foreach ($products as $product) {
			self::calculateSimilarity($mostSimilar, $searchQuery, $product['Bezeichnung'], $product['Nummer']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $product['Beschreibung'], $product['Nummer']);
		}

		self::sortByPercentage($mostSimilar);
		$mostSimilar = self::filterByPercentage($mostSimilar);

		return array_slice($mostSimilar, 0, 10);
	}

	/*
	 * searches in order data
	 */
	private static function searchInOrders($searchQuery)
	{
		$orders = DBAccess::selectQuery("SELECT auftrag.Auftragsnummer AS Nummer, Auftragsbezeichnung, Auftragsbeschreibung, GROUP_CONCAT(Bezeichnung SEPARATOR ', ') AS Schritte FROM auftrag, schritte WHERE auftrag.Auftragsnummer = schritte.Auftragsnummer GROUP BY auftrag.Auftragsnummer");
		$mostSimilar = array();

		foreach ($orders as $order) {
			self::calculateSimilarity($mostSimilar, $searchQuery, $order['Auftragsbezeichnung'], $order['Nummer']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $order['Auftragsbeschreibung'], $order['Nummer']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $order['Schritte'], $order['Nummer']);
		}

		self::sortByPercentage($mostSimilar);
		$mostSimilar = self::filterByPercentage($mostSimilar);

		return array_slice($mostSimilar, 0, 10);
	}

	/*
	 * searches in posten
	 */
	private static function searchInPosten($searchQuery)
	{
		$orders = DBAccess::selectQuery("SELECT Postennummer, Auftragsnummer, Beschreibung FROM postendata WHERE Beschreibung LIKE '%$searchQuery%'");
		$mostSimilar = array();

		foreach ($orders as $order) {
			self::calculateSimilarity($mostSimilar, $searchQuery, $order['Beschreibung'], $order['Postennummer']);
		}

		self::sortByPercentage($mostSimilar);
		$mostSimilar = self::filterByPercentage($mostSimilar);

		return array_slice($mostSimilar, 0, 10);
	}

	/*
	 * searches in wiki articles
	 */
	private static function searchInWiki($searchQuery)
	{
		$articles = DBAccess::selectQuery("SELECT id, content, title FROM wiki_articles WHERE title LIKE '%$searchQuery%' OR content LIKE '%$searchQuery%' OR keywords LIKE '%$searchQuery%'");
		$mostSimilar = array();

		foreach ($articles as $a) {
			self::calculateSimilarity($mostSimilar, $searchQuery, $a['content'], $a['id']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $a['title'], $a['id']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $a['keywords'], $a['id']);
		}

		self::sortByPercentage($mostSimilar);
		$mostSimilar = self::filterByPercentage($mostSimilar);

		return array_slice($mostSimilar, 0, 10);
	}

	/*
	 * sorts results by similiarity
	 */
	private static function sortByPercentage(&$mostSimilar)
	{
		usort($mostSimilar, function ($a, $b) {
			return ($a[1] < $b[1]) ? -1 : (($a[1] > $b[1]) ? 1 : 0);
		});
	}

	private static function inArray($search, $arr)
	{
		foreach ($arr as $el) {
			if ($el[0] == $search) {
				return true;
			}
		}
		return false;
	}

	private static function filterByPercentage($mostSimilar)
	{
		$filteredArray = array();
		foreach ($mostSimilar as $product) {
			/* 35 is the current similarity value, which is necessary to be displayed in search results */
			if ((float) $product[1] > 35) {
				if (self::inArray($product[0], $filteredArray)) {
					if ($product[1] >= end($filteredArray)[1]) {
						//$filteredArray[sizeof($filteredArray) - 1] = $product;
					}
				} else {
					array_push($filteredArray, $product);
				}
			}
		}

		return $filteredArray;
	}

	private static function calculateSimilarity(&$mostSimilar, $searchQuery, $text, $number)
	{
		$searchQuery = strtolower($searchQuery);
		$text = $text != null ? strtolower($text) : "";
		$pieces = explode(" ", $text);

		$cumulatedpercentage = 0.0;
		foreach ($pieces as $piece) {
			$sim = similar_text($searchQuery, $piece, $percentage);
			$percentage = round($percentage, 2);
			if ($percentage >= 20) {
				$cumulatedpercentage += $percentage;
			}

			if ($searchQuery == $piece) {
				$cumulatedpercentage += 150;
			}
		}

		array_push($mostSimilar, array($number, $cumulatedpercentage));
	}
}
