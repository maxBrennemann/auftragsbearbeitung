<?php
	session_start();

	require_once('settings.php');
	require_once('classes/DBAccess.php');
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Posten.php');
	$isArticle = false;
	
	/*
	* SQL Statements
	*/
	$sqlStatements = array(
		0 => "SELECT posten.Postennummer, leistung.Bezeichnung, leistung.Beschreibung, leistung.Preis, zeit.ZeitInMinuten, zeit.Stundenlohn FROM posten INNER JOIN leistung ON posten.Postennummer = leistung.Postennummer INNER JOIN zeit ON posten.Postennummer = zeit.Postennummer WHERE istStandard = 1;"
	);

	/*
	* Before: page was submitted via $_GET paramter, but now the REQUEST_URI is read;
	* $url is splitted into the REQUEST_URI and the parameter part
	*/
	$url = $_SERVER['REQUEST_URI'];
	$url = explode('?', $url, 2);
	$page = str_replace(REWRITE_BASE . "content/", "", $url[0]);
	$parts = explode('/', $page);
	$page = $parts[count($parts) - 1];
	if($parts[0] == 'artikel') {
		$isArticle = true;
	}

	/*
	* filters AJAX requests and delegates them to the right files
	*/
	if (isset($_POST['getReason'])) {
		if ($_POST['getReason'] == "fileRequest") {
			if (isset($_POST['file'])) {
				$file = Link::getResourcesLink($_POST['file'], "html", false);
				echo file_get_contents_utf8($file);
			}
		} else if ($_POST['getReason'] == "fillForm") {
			if (isset($_POST['file'])) {
				require_once("classes/project/FillForm.php");
				$filled = new FillForm(($_POST['file']));
				$filled->fill($_POST['nr']);
				$filled->show();
			}
		} else if ($_POST['getReason'] == "createTable") {
			$type = $_POST['type'];
			
			if (strcmp($type, "custom") == 0) {
				$data = DBAccess::selectQuery($sqlStatements[0]);
				$column_names = array(0 => array("COLUMN_NAME" => "Postennummer"), 1 => array("COLUMN_NAME" => "Bezeichnung"), 
						2 => array("COLUMN_NAME" => "Beschreibung"), 3 => array("COLUMN_NAME" => "Preis"), 
						4 => array("COLUMN_NAME" => "ZeitInMinuten"), 5 => array("COLUMN_NAME" => "Stundenlohn"));
				$table = new FormGenerator($type, "", "");
				echo $table->createTableByData($data, $column_names);
			} else {
				$column_names = DBAccess::selectQuery("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'${type}'");

				$showData = true;
				if (isset($_POST['showData'])) {
					$showData = false;
				}

				$table = FormGenerator::createTable($type, true, $showData, "");
				echo $table;
			}
		} else if ($_POST['getReason'] == "search") {
			require_once('classes/project/Search.php');
			$stype = $_POST['stype'];
			if (isset($_POST['urlid']) && $_POST['urlid'] == "1") {
				echo Search::getSearchTable($_POST['query'], $stype, Link::getPageLink("neuer-auftrag"));
			} else {
				echo Search::getSearchTable($_POST['query'], $stype);
			}
		} else if ($_POST['getReason'] == "createAuftrag") {
				$bez = $_POST['bez'];
				$bes = $_POST['bes'];
				$typ = $_POST['typ'];
				$ter = $_POST['ter'];
				$ang = $_POST['ang'];
				$kdn = $_POST['kdn'];

				$maxAuftragsnr = (int) DBAccess::selectQuery("SELECT MAX(Auftragsnummer) FROM auftrag")[0]['MAX(Auftragsnummer)'];
				$maxAuftragsnr++;
				$dat = date("Y-m-d");

				$insertQuery = "INSERT INTO auftrag (Auftragsnummer, Kundennummer, Auftragsbezeichnung, Auftragsbeschreibung, Auftragstyp, Datum, Termin, AngenommenDurch) ";
				$insertQuery .= "VALUES ($maxAuftragsnr, $kdn, '$bez', '$bes', '$typ', '$dat', '$ter', '$ang')";

				DBAccess::insertQuery($insertQuery);

				echo Link::getPageLink("auftrag") . "?id=$maxAuftragsnr";
		} else if ($_POST['getReason'] == "insTime") {
			$data = array();
			$data['ZeitInMinuten'] = $_POST['time'];
			$data['Stundenlohn'] = $_POST['wage'];
			$data['Beschreibung'] = $_POST['descr'];
			$data['Auftragsnummer'] = $_POST['auftrag'];
			Posten::insertPosten("zeit", $data);
		} else if ($_POST['getReason'] == "insertAnspr") {
			$vorname = $_POST['vorname'];
			$nachname = $_POST['nachname'];
			$email = $_POST['email'];
			$durchwahl = $_POST['durchwahl'];
			$nextId = $_POST['nextId'];
			DBAccess::insetQuery("INSERT INTO ansprechpartner (Kundennummer, Vorname, Nachname, Email, Durchwahl) VALUES($nextId, '$vorname', '$nachname', '$email', '$durchwahl')");
		} else {
			$selectQuery = "SELECT id, articleUrl, pageName FROM articles WHERE src = '$page'";
			$result = DBAccess::selectQuery($selectQuery);
		
			if($result == null) {
				$baseUrl = 'files/generated/';
				$result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM generated_articles WHERE src = '$page'");
			} else {
				$baseUrl = 'files/';
			}
		
			$result = $result[0];
			$articleUrl = $result["articleUrl"];
			include($baseUrl . $articleUrl);
		}
	} else {
		showPage($page, $isArticle);
	}
	
	
	function showPage($page, $isArticle) {
		$selectQuery = "SELECT id, articleUrl, pageName FROM articles WHERE src = '$page'";
		
		$result = DBAccess::selectQuery($selectQuery);
		
		if($result == null) {
			$baseUrl = 'files/generated/';
			$result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM generated_articles WHERE src = '$page'");
		} else {
			$baseUrl = 'files/';
		}
		
		$result = $result[0];
		$articleUrl = $result["articleUrl"];
		$pageName = $result["pageName"];
		
		if($articleUrl == NULL) {
			http_response_code(404);
			$articleUrl = "404.html";
		}
		
		include('files/header.php');
		include($baseUrl . $articleUrl);
		include('files/footer.php');
	}

/*
* https://stackoverflow.com/questions/2236668/file-get-contents-breaks-up-utf-8-characters
*/
function file_get_contents_utf8($fn) {
	$content = file_get_contents($fn);
	return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}
?>