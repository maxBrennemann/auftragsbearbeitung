<?php

class Ajax {
	
	private static $insertIntoAuftrag = "INSERT INTO auftrag (Auftragsnummer, Kundennummer, Auftragsbezeichnung, Auftragsbeschreibung, Auftragstyp, Datum, Termin, AngenommenDurch, AngenommenPer, Ansprechpartner)";
	private static $sqlStatements = "SELECT posten.Postennummer, leistung.Bezeichnung, leistung_posten.Beschreibung, leistung_posten.SpeziefischerPreis, zeit.ZeitInMinuten, zeit.Stundenlohn FROM posten INNER JOIN leistung_posten ON posten.Postennummer = leistung_posten.Postennummer INNER JOIN zeit ON posten.Postennummer = zeit.Postennummer INNER JOIN leistung ON leistung_posten.Leistungsnummer = leistung.Nummer WHERE istStandard = 1;";

	public static function manageRequests($reason, $page) {
		switch ($reason) {
			case "fileRequest":
				if (isset($_POST['file'])) {
					$file = Link::getResourcesLink($_POST['file'], "html", false);
					echo file_get_contents_utf8($file);
				}
				break;
			case "fillForm":
				if (isset($_POST['file'])) {
					require_once("classes/project/FillForm.php");
					$filled = new FillForm(($_POST['file']));
					$filled->fill($_POST['nr']);
					$filled->show();
				}
				break;
			case "createTable":
				$type = $_POST['type'];
			
				if (strcmp($type, "custom") == 0) {
					$data = DBAccess::selectQuery(self::$sqlStatements);
					$column_names = array(0 => array("COLUMN_NAME" => "Postennummer"), 1 => array("COLUMN_NAME" => "Bezeichnung"), 
							2 => array("COLUMN_NAME" => "Beschreibung"), 3 => array("COLUMN_NAME" => "Preis"), 
							4 => array("COLUMN_NAME" => "ZeitInMinuten"), 5 => array("COLUMN_NAME" => "Stundenlohn"));
					$table = new FormGenerator($type, "", "");
					echo $table->createTableByData($data, $column_names);
				} else {
					$column_names = DBAccess::selectColumnNames($type);

					$showData = true;
					if (isset($_POST['showData'])) {
						$showData = false;
					}

					$table = FormGenerator::createTable($type, true, $showData, "");
					echo $table;
				}
				break;
			case "search":
				require_once('classes/project/Search.php');
				$stype = $_POST['stype'];
				if (isset($_POST['urlid']) && $_POST['urlid'] == "1") {
					echo Search::getSearchTable($_POST['query'], $stype, Link::getPageLink("neuer-auftrag"));
				} else {
					echo Search::getSearchTable($_POST['query'], $stype);
				}
				break;
			case "createAuftrag":
				$bez = $_POST['bez'];
				$bes = $_POST['bes'];
				$typ = $_POST['typ'];
				$ter = $_POST['ter'];
				$ang = $_POST['ang'];
				$kdn = $_POST['kdn'];
				$per = $_POST['per'];
				$ans = $_POST['ans'];

				$maxAuftragsnr = (int) DBAccess::selectQuery("SELECT MAX(Auftragsnummer) FROM auftrag")[0]['MAX(Auftragsnummer)'];
				$maxAuftragsnr++;
				$dat = date("Y-m-d");

				$insertQuery = self::$insertIntoAuftrag;
				$insertQuery .= "VALUES ($maxAuftragsnr, $kdn, '$bez', '$bes', '$typ', '$dat', '$ter', $ang, $per, $ans)";

				DBAccess::insertQuery($insertQuery);

				echo Link::getPageLink("auftrag") . "?id=$maxAuftragsnr";
				break;
			case "insTime":
				$data = array();
				$data['ZeitInMinuten'] = $_POST['time'];
				$data['Stundenlohn'] = $_POST['wage'];
				$data['Beschreibung'] = $_POST['descr'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				Posten::insertPosten("zeit", $data);
				break;
			case "insertAnspr":
				$vorname = $_POST['vorname'];
				$nachname = $_POST['nachname'];
				$email = $_POST['email'];
				$durchwahl = $_POST['durchwahl'];
				$nextId = $_POST['nextId'];
				DBAccess::insertQuery("INSERT INTO ansprechpartner (Kundennummer, Vorname, Nachname, Email, Durchwahl) VALUES($nextId, '$vorname', '$nachname', '$email', '$durchwahl')");
				break;
			case "insertCar":
				$kfzKenn = $_POST['kfz'];
				$fahrzeug = $_POST['fahrzeug'];
				$nummer =  $_POST['kdnr'];
				DBAccess::insertQuery("INSERT INTO fahrzeuge (Kundennummer, Kennzeichen, Fahrzeug) VALUES($nummer, '$kfzKenn', '$fahrzeug')");
				break;
			case "test":
				var_dump(unserialize($_SESSION['data']));
				break;
			case "insertLeistung":
				$data = array();
				$data['Leistungsnummer'] = $_POST['lei'];
				$data['Beschreibung'] = $_POST['bes'];
				$data['Einkaufspreis'] = $_POST['ekp'];
				$data['SpeziefischerPreis'] = $_POST['pre'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				Posten::insertPosten("leistung", $data);
				break;
			case "insertStep":
				$data = array();
				$data['Bezeichnung'] = $_POST['bez'];
				$data['Datum'] = date("Y-m-d");
				$data['Priority'] = $_POST['prio'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				require_once("classes/project/Schritt.php");
				Schritt::insertStep($data);
				break;
			case "addStep":
				$column_names = array(0 => array("COLUMN_NAME" => "Bezeichnung"), 1 => array("COLUMN_NAME" => "Priority"));

				$addClass = $_POST['addClass'];

				echo FormGenerator::createEmptyTable($column_names, $addClass);
				break;
			case "getAllSteps":
				require_once("classes/project/Auftrag.php");
				$auftragsId = $_POST['auftrag'];
				$Auftrag = new Auftrag($auftragsId);
				echo $Auftrag->getBearbeitungsschritteAsTable();
				break;
			case "getOpenSteps":
				require_once("classes/project/Auftrag.php");
				$auftragsId = $_POST['auftrag'];
				$Auftrag = new Auftrag($auftragsId);
				echo $Auftrag->getOpenBearbeitungsschritteAsTable();
				break;
			case "setTo":
				require_once("classes/project/InteractiveFormGenerator.php");
				$table = unserialize($_SESSION['storedTable']);
				$auftragsId = $_POST['auftrag'];
				$row =  $_POST['row'];
				$table->setIdentifier("Schrittnummer");
				$table->editRow($row, "istErledigt", "0");
				break;
			default:
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
				break;
		}
	}

}

?>