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

					if (isset($_POST['sendTo'])) {
						$sendTo = $_POST['sendTo'];
					} else {
						$sendTo = "";
					}

					$table = FormGenerator::createTable($type, true, $showData,$sendTo);
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
					$shortSummary = false;
					if (isset($_POST['shortSummary'])) {
						if ($_POST['shortSummary'] == "true") {
							$shortSummary = true;
						}
					}
					echo Search::getSearchTable($_POST['query'], $stype, null, $shortSummary);
				}
			break;
			case "saveList":
				$data = $_POST['data'];
				require_once('classes/project/Liste.php');
				Liste::saveData($data);
				echo Link::getPageLink("listmaker");
			break;
			case "notification":
				require_once('classes/project/NotificationManager.php');;
				echo NotificationManager::htmlNotification();
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

				if (isset($_SESSION['offer_is_order']) && $_SESSION['offer_is_order'] == true) {
					$isLoadPosten = true;
				} else {
					$isLoadPosten = false;
				}

				$data = array("responseLink" => Link::getPageLink("auftrag") . "?id=$maxAuftragsnr", "loadFromOffer" => $isLoadPosten, "orderId" => $maxAuftragsnr);
				echo json_encode($data, JSON_FORCE_OBJECT);

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
				echo (new Auftrag($_POST['auftrag']))->preisBerechnen();
			break;
			case "insertProduct":
				$amount = $_POST['time'];
				$prodId = $_POST['time'];
				$isFree = $_POST['ohneBerechnung'];
				$auftId = $_POST['auftrag'];

				$data = array();
				$data['amount'] = $_POST['amount'];
				$data['prodId'] = $_POST['product'];
				$data['ohneBerechnung'] = $_POST['ohneBerechnung'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				Posten::insertPosten("produkt", $data);
			break;
			case "insertProductCompact":
				$data = array();
				$data['amount'] = (int) $_POST['menge'];
				$data['marke'] = $_POST['marke'];
				$data['ekpreis'] = str_replace(",", ".", $_POST['ekpreis']);
				$data['vkpreis'] = str_replace(",", ".", $_POST['vkpreis']);
				$data['name'] = $_POST['name'];
				$data['beschreibung'] = $_POST['beschreibung'];
				$data['ohneBerechnung'] = $_POST['ohneBerechnung'];
				$data['Auftragsnummer'] = (int) $_POST['auftrag'];
				Posten::insertPosten("compact", $data);
				echo (new Auftrag($_POST['auftrag']))->preisBerechnen();
			break;
			case "insertAnspr":
				$vorname = $_POST['vorname'];
				$nachname = $_POST['nachname'];
				$email = $_POST['email'];
				$durchwahl = $_POST['durchwahl'];
				$nextId = $_POST['nextId'];
				DBAccess::insertQuery("INSERT INTO ansprechpartner (Kundennummer, Vorname, Nachname, Email, Durchwahl) VALUES($nextId, '$vorname', '$nachname', '$email', '$durchwahl')");

				$kunde = new Kunde($nextId);
				$table = new FormGenerator("ansprechpartner", "", "");
				$data = DBAccess::selectQuery("SELECT * FROM ansprechpartner WHERE Kundennummer = $nextId");
				$column_names = array(0 => array("COLUMN_NAME" => "Vorname"), 1 => array("COLUMN_NAME" => "Nachname"), 
								2 => array("COLUMN_NAME" => "Email"), 3 => array("COLUMN_NAME" => "Durchwahl"), 4 => array("COLUMN_NAME" => "Mobiltelefonnummer"));
				$ansprechpartner = $table->createTableByData($data, $column_names);
				echo $ansprechpartner;
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
				$data['Einkaufspreis'] = str_replace(",", ".", $_POST['ekp']);
				$data['SpeziefischerPreis'] = str_replace(",", ".", $_POST['pre']);
				$data['Auftragsnummer'] = $_POST['auftrag'];
				$data['ohneBerechnung'] = $_POST['ohneBerechnung'];
				Posten::insertPosten("leistung", $data);
				echo (new Auftrag($_POST['auftrag']))->preisBerechnen();
			break;
			case "insertStep":
				$data = array();
				$data['Bezeichnung'] = $_POST['bez'];
				$data['Datum'] = $_POST['datum'];
				$data['Priority'] = $_POST['prio'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				$data['hide'] = $_POST['hide'];
				require_once("classes/project/Schritt.php");
				require_once("classes/project/Auftrag.php");
				$postenNummer = Schritt::insertStep($data);
				$auftrag = new Auftrag($data['Auftragsnummer']);
				echo $auftrag->getOpenBearbeitungsschritteTable();

				$assignedTo = strval($_POST['assignedTo']);
				if (strcmp($assignedTo, "none") != 0) {
					require_once("classes/project/NotificationManager.php");
					NotificationManager::addNotification($userId = $assignedTo, $type = 1, $content = $_POST['bez'], $specificId = $postenNummer);
				}
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
				echo $Auftrag->getOpenBearbeitungsschritteTable();
			break;
			case "setTo":
				if (isset($_POST['auftrag'])) {
					require_once("classes/project/InteractiveFormGenerator.php");
					$table = unserialize($_SESSION['storedTable']);
					$auftragsId = $_POST['auftrag'];
					$row =  $_POST['row'];
					$table->setIdentifier("Schrittnummer");
					$date =  date("Y-m-d");
					$table->addParam("finishingDate", $date);
					$table->editRow($row, "istErledigt", "0");
				} else {
					require_once("classes/project/Rechnung.php");
					$rechnung = $_POST['rechnung'];
					DBAccess::updateQuery("UPDATE auftrag SET Bezahlt = 1 WHERE Auftragsnummer = $rechnung");
					echo Rechnung::getOffeneRechnungen();
				}
			break;
			case "delete":
				/* using new table functionality */
				require_once("classes/project/Table.php");
				Table::updateValue("schritte_table", "delete", $_POST['key']);
				$postennummer = Table::getIdentifierValue("schritte_table", $_POST['key']);

				/* when a step is deleted, its connection to the notification manager must be deleted and it must be shown in the order histor */
				require_once("classes/project/Auftragsverlauf.php");
				$auftragsverlauf = new Auftragsverlauf($_POST['auftrag']);
				$auftragsverlauf->addToHistory($postennummer, 2, "deleted");

				$query = "UPDATE user_notifications SET ischecked = 1 WHERE specific_id = $postennummer";
				DBAccess::updateQuery($query);
			break;
			case "update":
				/* using new table functionality */
				require_once("classes/project/Table.php");
				Table::updateValue("schritte_table", "update", $_POST['key']);
				/* adds an update step to the history by using orderId and identifier */
				$postennummer = Table::getIdentifierValue("schritte_table", $_POST['key']);
				Schritt::updateStep([
					"orderId" => $_POST['auftrag'],
					"postennummer" => $postennummer
				]);

				require_once("classes/project/NotificationManager.php");
				if (isset($_SESSION['userid'])) 
					$user = $_SESSION['userid'];
				NotificationManager::addNotificationCheck($user, 0, "Bearbeitungsschritt erledigt", $postennummer);
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
				DBAccess::insertQuery("INSERT INTO leistung (Bezeichnung, Beschreibung, Quelle, Aufschlag) VALUES ('$bezeichung', '$description', '$source', $aufschlag)");
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
				/*$customerId = $_POST['customerId'];
				$angebot = new Angebot($customerId);
				$angebot->storeOffer();*/
				Angebot::setIsOrder();
			break;
			case "addAdress":
				$strasse = $_POST['strasse'];
				$hausnummer = $_POST['hausnummer'];
				$postleitzahl = $_POST['postleitzahl'];
				$ort = $_POST['ort'];
				$zusatz = $_POST['zusatz'];
				$art = $_POST['art'];
				$id_customer = $_POST['kdnr'];

				Kunde::addAdress($id_customer, $strasse, $hausnummer, $postleitzahl, $ort, $zusatz, $art);
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

				$id = DBAccess::insertQuery($query);
				DBAccess::insertQuery("INSERT INTO farben_auftrag (id_farbe, id_auftrag) VALUES ($id, $auftrag)");

				$auftrag = new Auftrag($auftrag);
				$data = array("farben" => $auftrag->getFarben(), "addFarben" => $auftrag->getAddColors());
				echo json_encode($data, JSON_FORCE_OBJECT);
			break;
			case "removeColor":
				$colorId = $_POST['colorId'];
				$auftragsId = $_POST['auftrag'];
				
				DBAccess::deleteQuery("DELETE FROM farben_auftrag WHERE id_farbe = $colorId AND id_auftrag = $auftragsId");

				$auftrag = new Auftrag($auftragsId);
				echo $auftrag->getFarben();
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
			case "completeInvoice":
				require_once("classes/project/Rechnung.php");
				$orderId = (int) $_POST['auftrag'];
				$ids = $_POST['rows'];
				if ($ids == "0") {
					Rechnung::addAllPosten($orderId);
				} else {
					$ids = json_decode($ids);
					Rechnung::addPosten($orderId, $ids);
				}
				return Link::getPageLink();
			break;
			case "generateInvoicePDF":
				require_once("classes/project/Rechnung.php");
				$invoice = new Rechnung();
				$invoice->PDFgenerieren(true);
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
			case "reloadPostenListe":
				$auftragsId = $_POST['id'];
				$auftrag = new Auftrag($auftragsId);
				echo $auftrag->getAuftragspostenAsTable();
			break;
			case "loadPosten":
				if (isset($_SESSION['offer_is_order']) && $_SESSION['offer_is_order'] == true) {
					$offerId = $_SESSION['offer_id'];
					$orderId = $_POST['auftragsId'];
					$angebot = new Angebot();
					$angebot->storeOffer($orderId);
				}
			break;
			case "saveDescription":
				$text = $_POST['text'];
				$auftrag = $_POST['auftrag'];
				DBAccess::updateQuery("UPDATE auftrag SET Auftragsbeschreibung = '$text' WHERE Auftragsnummer = $auftrag");
				echo "saved";
			break;
			case "table":
				/*
				 * gets table data with action and key
				 * @return gives a messsage or specific values
				*/
				$table = $_POST['name'];
				$action = $_POST['action'];
				$key = $_POST['key'];

				require_once("classes/project/Table.php");
				$response = Table::updateValue($table, $action, $key);
				echo $response;
			break;
			case "addListToOrder":
				$listId = (int) $_POST['listId'];
				$orderId = (int) $_POST['auftrag'];
				$order = new Auftrag($orderId);
				$order->addList($listId);
			break;
			case "sendNewAdress":
				require_once('classes/project/Adress.php');
				$kdnr = (int) $_POST['customer'];
				$plz = (int) $_POST['plz'];
				$ort = $_POST['ort'];
				$strasse = $_POST['strasse'];
				$hnr = $_POST['hnr'];
				$zusatz = $_POST['zusatz'];
				$land = $_POST['land'];
				Adress::createNewAdress($kdnr, $strasse, $hnr, $plz, $ort, $zusatz, $land);
			break;
			case "addNewLine":
				require_once("classes/project/Table.php");

				$key = $_POST['key'];
				$data = $_POST['data'];
				echo Table::updateTable_AddNewLine($key, $data);
			break;
			case "addNoteOrder":
				$note = $_POST['note'];
				$orderId = (int) $_POST['auftrag'];
				DBAccess::insertQuery("INSERT INTO notizen (Auftragsnummer, Notiz) VALUES ($orderId, '$note')");
				$auftrag = new Auftrag($orderId);
				echo $auftrag->getNotes();
			break;
			case "setCustomColor":
				require_once("classes/project/ClientSettings.php");
				$color = $_POST['color'];
				$type = $_POST['type'];
				Settings::setGrayScale($color, $type);
				echo "ok";
			break;
			case "getInfoText":
				echo "dies ist ein testtext";
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