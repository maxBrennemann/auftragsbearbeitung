<?php

require_once("classes/project/Produkt.php");

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
					$shortSummary = false;
					if (isset($_POST['shortSummary'])) {
						if ($_POST['shortSummary'] == "true") {
							$shortSummary = true;
						}
					}
					echo Search::getSearchTable($_POST['query'], $stype, Link::getPageLink("neuer-auftrag"), $shortSummary);
				} else {
					echo Search::getSearchTable($_POST['query'], $stype);
				}
			break;
			case "saveList":
				$data = $_POST['data'];
				require_once('classes/project/Liste.php');
				Liste::saveData($data);
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

				//Statistics::auftragEroeffnen(new Auftrag($maxAuftragsnr));
			break;
			case "insTime":
				$data = array();
				$data['ZeitInMinuten'] = $_POST['time'];
				$data['Stundenlohn'] = $_POST['wage'];
				$data['Beschreibung'] = $_POST['descr'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				$data['ohneBerechnung'] = $_POST['ohneBerechnung'];
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
				$auftragsId =  $_POST['auftrag'];
				$fahrzeugId = DBAccess::insertQuery("INSERT INTO fahrzeuge (Kundennummer, Kennzeichen, Fahrzeug) VALUES($nummer, '$kfzKenn', '$fahrzeug')");
				require_once("classes/project/Fahrzeug.php");
				Fahrzeug::attachVehicle($fahrzeugId, $auftragsId);
				echo (new Auftrag($auftragsId))->getFahrzeuge();
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
				$data['ohneBerechnung'] = $_POST['ohneBerechnung'];
				Posten::insertPosten("leistung", $data);
			break;
			case "insertStep":
				$data = array();
				$data['Bezeichnung'] = $_POST['bez'];
				$data['Datum'] = date("Y-m-d");
				$data['Priority'] = $_POST['prio'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				require_once("classes/project/Schritt.php");
				require_once("classes/project/Auftrag.php");
				Schritt::insertStep($data);
				$auftrag = new Auftrag($data['Auftragsnummer']);
				echo $auftrag->getOpenBearbeitungsschritteAsTable();
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
				$date =  date("Y-m-d");
				$table->addParam("finishingDate", $date);
				$table->editRow($row, "istErledigt", "0");
			break;
			case "sendSource":
				Produkt::addSource();
			break;
			case "getSelect":
				Produkt::getSelectSource();	
			break;
			case "insertVerbesserung":
				if (isset($_POST['verbesserung'])) {
					$verbesserung = $_POST['verbesserung'];
					DBAccess::insertQuery("INSERT INTO verbesserungen (verbesserungen) VALUES ('$verbesserung')");
				}
			break;
			case "getServerMsg":
				echo $_SESSION['searchResult'];
			break;
			case "attachCar":
				$auftragsId = $_POST['auftrag'];
				$fahrzeugId = $_POST['fahrzeug'];
				require_once("classes/project/Fahrzeug.php");
				Fahrzeug::attachVehicle($fahrzeugId, $auftragsId);
				echo (new Auftrag($auftragsId))->getFahrzeuge();
			break;
			case "getAttributeMatcher":
				require_once("classes/project/AttributeGroup.php");
				AttributeGroup::getProductToAttributeMatcher();
			break;
			case "getAttributes":
				require_once("classes/project/AttributeGroup.php");
				$attGroupId = $_POST['attGroupId'];
				AttributeGroup::getAttributes($attGroupId);
			break;
			case "setNotes":
				$kdnr = $_POST['kdnr'];
				$note = $_POST['notes'];
				DBAccess::insertQuery("UPDATE kunde_extended SET notizen = '$note' WHERE kundennummer = $kdnr");
			break;
			case "addLeistung":
				$bezeichung = $_POST['bezeichung'];
				$description = $_POST['description'];
				$source = $_POST['source'];
				$aufschlag = $_POST['aufschlag'];
				DBAccess::insertQuery("INSERT INTO leistung (Bezeichnungm, Beschreibung, Quelle, Aufschlag) VALUES ('$bezeichung', '$description', '$source', $aufschlag)");
			break;
			case "addTimeOffer":
				$customerId = $_POST['customerId'];
				$time = $_POST['time'];
				$wage = $_POST['wage'];
				$descr = $_POST['descr'];
				$isFree = (int) $_POST['isFree'];
				$angebot = new Angebot($customerId);
				require_once("classes/project/Zeit.php");
				$zeitPosten = new Zeit($wage, $time, $descr);
				$angebot->addPosten($zeitPosten);
			break;
			case "addLeistungOffer":
				$customerId = $_POST['customerId'];
				$lei = $_POST['lei'];
				$bes = $_POST['bes'];
				$ekp = $_POST['ekp'];
				$pre = $_POST['pre'];
				$isFree = (int) $_POST['isFree'];
				$angebot = new Angebot($customerId);
				require_once("classes/project/Leistung.php");
				$leistungsPosten = new Leistung($lei, $bes, $pre, $ekp);
				$angebot->addPosten($leistungsPosten);
			break;
			case "storeOffer":
				$customerId = $_POST['customerId'];
				$angebot = new Angebot($customerId);
				$angebot->storeOffer();
			break;
			case "newColor":
				require_once('classes/project/Auftrag.php');
				$auftrag = $_POST['auftrag'];
				$farbname = $_POST['farbname'];
				$farbe = $_POST['farbe'];
				$bezeichnung = $_POST['bezeichnung'];
				$hersteller = $_POST['hersteller'];

				$query = "INSERT INTO farben (Kundennummer, Auftragsnummer, Farbe, Farbwert, Notiz, Hersteller) VALUES ";
				$query .= "((SELECT auftrag.Kundennummer AS Kundennummer FROM auftrag WHERE auftrag.Auftragsnummer = $auftrag), $auftrag, ";
				$query .= "'$farbname', '$farbe', '$bezeichnung', '$hersteller')";

				DBAccess::insertQuery($query);

				$auftrag = new Auftrag($auftrag);
				$data = array("farben" => $auftrag->getFarben(), "addFarben" => $auftrag->getAddColors());
				echo json_encode($data, JSON_FORCE_OBJECT);
			break;
			case "archivieren":
				require_once('classes/project/Auftrag.php');
				$auftrag = $_POST['auftrag'];
				$auftrag = new Auftrag($auftrag);
				$auftrag->archiveOrder();
			break;
			case 'loadTemplateOrder':
				require_once('classes/project/Angebot.php');
				$customerId = $_POST['customerId'];
				$angebot = new Angebot($customerId);
				echo $angebot->getHTMLTemplate();
			break;
			case 'loadCachedPosten':
				require_once('classes/project/Angebot.php');
				$customerId = $_POST['customerId'];
				$angebot = new Angebot($customerId);
				echo $angebot->loadCachedPosten(); 
			break;
			case "setData":
				if ($_POST['type'] == "kunde") {
					$number = (int) $_POST['number'];
					$kdnr = $_POST['kdnr'];
					for ($i = 0; $i < $number; $i++) {
						$dataKey = $_POST["dataKey$i"];
						$data = $_POST[$dataKey];
						echo "UPDATE kunde SET $dataKey = '$data' WHERE Kundennummer = $kdnr";
						DBAccess::updateQuery("UPDATE kunde SET $dataKey = '$data' WHERE Kundennummer = $kdnr");
					}
				}
			break;
			case "addAttVal":
				$attributeId = $_POST['att'];
				$value = $_POST['value'];
				DBAccess::insertQuery("INSERT INTO attribute (attribute_group_id, value) VALUES ($attributeId, '$value')");
				echo $attributeId;
			break;
			case "addAtt":
				$attribute = $_POST['name'];
				$descr = $_POST['descr'];
				DBAccess::insertQuery("INSERT INTO attribute_group (attribute_group, descr) VALUES ('$attribute', '$descr')");
				echo $attributeId;
			break;
			case "getList":
				require_once('classes/project/Liste.php');
				$lid = $_POST['listId'];
				$list = Liste::readList($lid);
				echo $list->toHTML();
			break;
			case "saveProduct":
				$attData = $_POST['attData'];
				$marke = $_POST['marke'];
				$quelle = $_POST['quelle'];
				$vkNetto = $_POST['vkNetto'];
				$ekNetto = $_POST['ekNetto'];
				$title = $_POST['title'];
				$desc = $_POST['desc'];
				Produkt::createProduct($title, $marke, $desc, $ekNetto, $vkNetto, $quelle, $attData);
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