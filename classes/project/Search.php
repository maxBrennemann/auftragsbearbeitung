<?php

require_once('classes/DBAccess.php');

class Search {
	
	public static function getSearchTable($searchQuery, $searchType, $retUrl = null) {
		$ids = array();
		$query = "";
		$columnNames = array();
		switch ($searchType) {
			case "kunde":
				$ids = self::searchInCustomers($searchQuery);
				$query = "SELECT * FROM kunde WHERE Kundennummer = ";
				$columnNames = DBAccess::selectColumnNames($searchType);
				break;
			case "produkt":
				$ids = self::searchInProducts($searchQuery);
				$query = "SELECT * FROM produkt WHERE Nummer = ";
				$columnNames = DBAccess::selectColumnNames($searchType);
				break;
		}
		$data = array();
		foreach ($ids as $id) {
			$column = DBAccess::selectQuery($query . $id[0]);
			$column = $column[0];
			array_push($data, $column);
		}
		$data = array_reverse($data);
		$f = new FormGenerator("", "", "");
		if ($retUrl != null) {
			return $f->createTableByDataRowLink($data, $columnNames, $searchType, $retUrl);
		}
		return $f->createTableByData($data, $columnNames);
	}

	private static function searchInCustomers($searchQuery) {
		$kunden = DBAccess::selectQuery("SELECT Kundennummer, Vorname, Nachname, Firmenname FROM kunde");
		$mostSimilar = array();

		foreach ($kunden as $kunde) {
			self::calculateSimilarity($mostSimilar, $searchQuery, $kunde['Vorname'] . " " . $kunde['Nachname'], $kunde['Kundennummer']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $kunde['Firmenname'], $kunde['Kundennummer']);
		}

		self::sortByPercentage($mostSimilar);
		$mostSimilar = self::filterByPercentage($mostSimilar);

		return array_slice($mostSimilar, 0, 10);
	}

	private static function searchInProducts($searchQuery) {
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

	private static function sortByPercentage(&$mostSimilar) {
		function cmp($a, $b) {
			return ($a[1] < $b[1]) ? -1 : (($a[1] > $b[1]) ? 1 : 0);
		}

		usort($mostSimilar, "cmp");
	}

	private static function filterByPercentage($mostSimilar) {
		function inArray($search, $arr) {
			foreach ($arr as $el) {
				if ($el[0] == $search) {
					return true;
				}
			}
			return false;
		}

		$filteredArray = array();
		foreach ($mostSimilar as $product) {
			if ((float) $product[1] > 20) {
				if (inArray($product[0], $filteredArray)) {
					if ($product[1] >= end($filteredArray)[1]) {
						$filteredArray[sizeof($filteredArray) - 1] = $product;
					}
				} else {
					array_push($filteredArray, $product);
				}
			}
		}

		return $filteredArray;
	}

	private static function calculateSimilarity(&$mostSimilar, $searchQuery, $text, $nummer) {
		similar_text($searchQuery, $text, $percentage);
		array_push($mostSimilar, array($nummer, $percentage));
	}

} 

?>