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
		return $table->getTable();
	}

	public static function globalSearch($searchQuery) {
		/* suchen in:
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

		$results = array();

		$results[0] = self::searchInCustomers($searchQuery);
		$results[1] = self::searchInProducts($searchQuery);
		$results[2] = self::searchInOrders($searchQuery);
		$results[3] = self::searchInPosten($searchQuery);
		$results[4] = self::searchInWiki($searchQuery);

		$html = "";
		foreach ($results as $key => $r) {
			$name = "";

			switch ($key) {
				case 0:
					$name = "Kunden: ";
					break;
				case 1:
					$name = "Produkte: ";
					break;
				case 2:
					$name = "Aufträge: ";
					break;
				case 3:
					$name = "Posten: ";
					break;
				case 4:
					$name = "Wiki: ";
					break;
				default:
					$name = "Es ist ein Fehler aufgetreten";
			}

			$html .= "<h3>$name</h3>";
			foreach ($r as $item) {
				$data = null;
				$link = "";
				switch ($key) {
					case 0:
						$query = "SELECT Kundennummer AS id, CONCAT(COALESCE(Firmenname, ''), ' ', COALESCE(Vorname, ''), ' ', COALESCE(Nachname, '')) AS `message` FROM kunde WHERE Kundennummer = {$item[0]}";
						$data = DBAccess::selectQuery($query)[0];

						$link = Link::getPageLink("kunde") . "?id=" . $data["id"];
						break;
					case 1:
						$query = "SELECT Nummer AS id, CONCAT(COALESCE(Marke, ''), ' ', COALESCE(Bezeichnung, ''), ' ', COALESCE(Beschreibung, '')) AS `message` FROM produkt WHERE Nummer = {$item[0]}";
						$data = DBAccess::selectQuery($query)[0];

						$link = Link::getPageLink("produkt") . "?id=" . $data["id"];
						break;
					case 2:
						$query = "SELECT Auftragsnummer AS id, CONCAT(COALESCE(Auftragsbezeichnung, ''), ' ', COALESCE(Auftragsbeschreibung, '')) AS `message` FROM auftrag WHERE Auftragsnummer = {$item[0]}";
						$data = DBAccess::selectQuery($query)[0];

						$link = Link::getPageLink("auftrag") . "?id=" . $data["id"];
						break;
					case 3:
						$query = "SELECT Auftragsnummer AS id, COALESCE(Beschreibung, ' ') AS `message` FROM postendata WHERE Postennummer = {$item[0]}";
						$data = DBAccess::selectQuery($query)[0];

						$link = Link::getPageLink("auftrag") . "?id=" . $data["id"];
						break;
					case 4:
						$query = "SELECT id, COALESCE(title, ' ') AS `message` FROM wiki_articles WHERE id = {$item[0]}";
						$data = DBAccess::selectQuery($query)[0];

						$link = Link::getPageLink("wiki") . "?id=" . $data["id"];
						break;
				}

				$html .= "<a href=\"$link\">Zu: {$data['message']}</a><br>";
			}
		}

		return $html;
	}

	/*
	 * searches in customer data
	 */
	private static function searchInCustomers($searchQuery) {
		$query = "SELECT kunde.Kundennummer, Vorname, Nachname, Firmenname, kunde_extended.notizen AS Notiz FROM kunde, kunde_extended WHERE kunde_extended.kundennummer = kunde.Kundennummer AND (Vorname LIKE '%$searchQuery%' OR Nachname LIKE '%$searchQuery%' OR Firmenname LIKE '%$searchQuery%' OR kunde_extended.notizen LIKE '%$searchQuery%')";
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
	 * searches in posten
	 */
	private static function searchInPosten($searchQuery) {
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
	private static function searchInWiki($searchQuery) {
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
	private static function sortByPercentage(&$mostSimilar) {
		if (!function_exists("cmp")) {
			function cmp($a, $b) {
				return ($a[1] < $b[1]) ? -1 : (($a[1] > $b[1]) ? 1 : 0);
			}
		}

		/*print "<pre>";
		print_r($mostSimilar);
		print "</pre>";*/

		usort($mostSimilar, "cmp");
	}

	private static function filterByPercentage($mostSimilar) {
		if (!function_exists("inArray")) {
			function inArray($search, $arr) {
				foreach ($arr as $el) {
					if ($el[0] == $search) {
						return true;
					}
				}
				return false;
			}
		}

		$filteredArray = array();
		foreach ($mostSimilar as $product) {
			/* 35 is the current similarity value, which is necessary to be displayed in search results */
			if ((float) $product[1] > 35) {
				if (inArray($product[0], $filteredArray)) {
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

	private static function calculateSimilarity(&$mostSimilar, $searchQuery, $text, $number) {
		$searchQuery = strtolower($searchQuery);
		$text = strtolower($text);
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

			/*echo "query: $searchQuery, piece: $piece, sim: $sim, percentage: $percentage<br>";*/
		}

		array_push($mostSimilar, array($number, $cumulatedpercentage));
	}

} 

?>