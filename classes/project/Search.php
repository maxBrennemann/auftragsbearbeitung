<?php

require_once('classes/DBAccess.php');
require_once('classes/project/ClientSocket.php');
require_once('classes/project/Produkt.php');

class Search {

	public static function searchForData($query, $type) {
		$query = "search $type $query";
		return ClientSocket::writeMessage($query, false);
	}

	public static function insertData($insertQuery) {
		ClientSocket::writeMessage($insertQuery, true);
	}
	
	/*
	 * returns a table with the search results
	 */
	public static function getSearchTable($searchQuery, $searchType, $retUrl = null, $getShortSummary = false) {
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
			case "order":
				$ids = self::searchInOrders($searchQuery);
				$query = "SELECT * FROM auftrag WHERE Auftragsnummer = ";
				$columnNames = DBAccess::selectColumnNames($searchType);
			break;
		}
		$data = array();

		if ($getShortSummary) {
			switch ($searchType) {
				case "kunde":
					$data = "";
					$ids = array_reverse($ids);
					foreach ($ids as $id) {
						$data .= (new Kunde($id[0]))->getHTMLShortSummary();
					}
					return $data;
				break;
				case "produkt":
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
					$data = "";
					$ids = array_reverse($ids);
					$data = Auftrag::getAuftragsListe($ids);

					//var_dump($ids);
					if (empty($data)) {
						$data = "<span>Keine Ergebnisse!</span>";
					}

					return $data;
				break;
			}
		}

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

	/*
	 * searches in customer data
	 */
	private static function searchInCustomers($searchQuery) {
		$query = "SELECT Kundennummer, Vorname, Nachname, Firmenname FROM kunde WHERE Vorname LIKE '%$searchQuery%' OR Nachname LIKE '%$searchQuery%' OR Firmenname LIKE '%$searchQuery%'";
		$kunden = DBAccess::selectQuery($query);
		$mostSimilar = array();

		foreach ($kunden as $kunde) {
			self::calculateSimilarity($mostSimilar, $searchQuery, $kunde['Vorname'] . " " . $kunde['Nachname'], $kunde['Kundennummer']);
			self::calculateSimilarity($mostSimilar, $searchQuery, $kunde['Firmenname'], $kunde['Kundennummer']);
		}

		self::sortByPercentage($mostSimilar);
		$mostSimilar = self::filterByPercentage($mostSimilar);

		return array_slice($mostSimilar, 0, 10);
	}

	/*
	 * searches in product data
	 */
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

	/*
	 * searches in order data
	 */
	private static function searchInOrders($searchQuery) {
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
	 * sorts results by similiarity
	 */
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
			/* 35 is the current similarity value, which is necessary to be displayed in search results*/
			if ((float) $product[1] > 35) {
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
		$searchQuery = strtolower($searchQuery);
		$text = strtolower($text);
		$pieces = explode(" ", $text);

		$cumulatedpercentage = 0;
		foreach ($pieces as $piece) {
			similar_text($searchQuery, $piece, $percentage);
			$percentage = round($percentage, 2);
			if ($percentage >= 15) {
				$cumulatedpercentage += $percentage;
			}
			//echo "% for search $text form $piece is $percentage <br>";
		}

		//echo "percentage for search $text is $cumulatedpercentage <br><br>";

		array_push($mostSimilar, array($nummer, $cumulatedpercentage));
	}

} 

?>