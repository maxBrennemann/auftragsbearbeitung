<?php

require_once("classes/project/Produkt.php");

class Ajax {
	
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
			case "searchProduct":
				require_once('classes/project/Search.php');
				$query = $_POST['query'];
				echo Search::getSearchTable($query, "produkt");
			break;
			case "globalSearch":
				require_once('classes/project/Search.php');
				$query = $_POST['query'];
				echo Search::globalSearch($query);
			break;
			case "saveList":
				$data = $_POST['data'];
				require_once('classes/project/Liste.php');
				Liste::saveData($data);
				echo Link::getPageLink("listmaker");
			break;
			case "saveListData":
				$listid = (int) $_POST['listId'];
				$listname = (int) $_POST['id'];
				$listvalue = $_POST['value'];
				$listtype = $_POST['type'];
				$orderId = $_POST['auftrag'];

				$types = [
					"radio" => 1,
					"checkbox" => 2,
					"text" => 3
				];

				$listtype = $types[$listtype];

				require_once('classes/project/Liste.php');
				Liste::storeListData($listid, $listname, $listtype, $listvalue, $orderId);

				echo "success";
			break;
			case "notification":
				require_once('classes/project/NotificationManager.php');
				echo NotificationManager::htmlNotification();
			break;
			case "updateNotification":
				require_once('classes/project/NotificationManager.php');
				NotificationManager::checkActuality();
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
				$dat = date("Y-m-d");

				$insertQuery = "INSERT INTO auftrag (Kundennummer, Auftragsbezeichnung, Auftragsbeschreibung, Auftragstyp, Datum, Termin, AngenommenDurch, AngenommenPer, Ansprechpartner)";
				$insertQuery .= "VALUES ($kdn, '$bez', '$bes', '$typ', '$dat', '$ter', $ang, $per, $ans)";

				$orderId = DBAccess::insertQuery($insertQuery);

				if (isset($_SESSION['offer_is_order']) && $_SESSION['offer_is_order'] == true) {
					$isLoadPosten = true;
				} else {
					$isLoadPosten = false;
				}

				$data = array("responseLink" => Link::getPageLink("auftrag") . "?id=$orderId", "loadFromOffer" => $isLoadPosten, "orderId" => $orderId);
				echo json_encode($data, JSON_FORCE_OBJECT);

				require_once("classes/project/NotificationManager.php");
				$link = $data["responseLink"];
				NotificationManager::addNotificationCheck(-1, 4, "Auftrag <a href=\"$link\">$orderId</a> wurde angelegt", $orderId);
				//Statistics::auftragEroeffnen(new Auftrag($orderId));
			break;
			case "insTime":
				$data = array();
				$data['ZeitInMinuten'] = $_POST['time'];
				$data['Stundenlohn'] = $_POST['wage'];
				$data['Beschreibung'] = $_POST['descr'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				$data['ohneBerechnung'] = $_POST['ohneBerechnung'];
				$data['discount'] = (int) $_POST['discount'];
				$data['addToInvoice'] = (int) $_POST['addToInvoice'];

				if (!isset($_POST['isOverwrite'])) {
					$_SESSION['overwritePosten'] = false;
				}

				$ids = Posten::insertPosten("zeit", $data);

				/* erweiterte Zeiterfassung */
				$zeiterfassung = $_POST['zeiterfassung'];
				if ($zeiterfassung != "empty") {
					require_once("classes/project/Zeit.php");
					Zeit::erweiterteZeiterfassung($zeiterfassung, $ids[1]);
				}

				$_SESSION['overwritePosten'] = false;
				echo (new Auftrag($_POST['auftrag']))->preisBerechnen();
			break;
			case "insertLeistung":
				$data = array();
				$data['Leistungsnummer'] = $_POST['lei'];
				$data['Beschreibung'] = $_POST['bes'];
				$data['Einkaufspreis'] = str_replace(",", ".", $_POST['ekp']);
				$data['SpeziefischerPreis'] = str_replace(",", ".", $_POST['pre']);
				$data['Auftragsnummer'] = $_POST['auftrag'];
				$data['ohneBerechnung'] = $_POST['ohneBerechnung'];
				$data['discount'] = (int) $_POST['discount'];
				$data['MEH'] = $_POST['meh'];
				$data['anzahl'] = str_replace(",", ".", $_POST['anz']);
				$data['addToInvoice'] = (int) $_POST['addToInvoice'];

				if (!isset($_POST['isOverwrite'])) {
					$_SESSION['overwritePosten'] = false;
				}
				
				Posten::insertPosten("leistung", $data);
				$_SESSION['overwritePosten'] = false;
				echo (new Auftrag($_POST['auftrag']))->preisBerechnen();
			break;
			case "insertProduct":
				$data = array();
				$data['amount'] = $_POST['amount'];
				$data['prodId'] = $_POST['product'];
				$data['ohneBerechnung'] = $_POST['ohneBerechnung'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				$data['addToInvoice'] = (int) $_POST['addToInvoice'];
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
				$data['discount'] = (int) $_POST['discount'];
				$data['addToInvoice'] = (int) $_POST['addToInvoice'];

				$_SESSION['overwritePosten'] = false;

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
			case "getAnspr":
				$kdnr = (int) $_POST['id'];
				$data = DBAccess::selectQuery("SELECT Nummer, Vorname, Nachname, Email FROM ansprechpartner WHERE Kundennummer = $kdnr");

				$data_html = "";
				foreach ($data as $line) {
					$v = $line['Vorname'];
					$n = $line['Nachname'];
					$e = $line['Email'];
					$id = $line['Nummer'];
					$data_html .= "<input id=\"anspr-$id\" type=\"radio\" name=\"anspr\" data-ansprid=\"$id\"><label for=\"anspr-$id\">$v $n - $e</label><br>";
				}

				$data_html .= "<button>Änderungen speichern</button>";
				echo $data_html;
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
			case "testDummy":
				echo "this is a test string";
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
					$query = "SELECT id_member FrOM members_mitarbeiter WHERE id_mitarbeiter = $assignedTo";
					$data = DBAccess::selectQuery($query);
					if ($data == null)
						return;
					else 
						$assignedTo = $data[0]["id_member"];

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
			case "editAnspr":
				$table = $_POST['name'];
				$key =  $_POST['key'];
				$data = json_decode($_POST['data']);

				$tableObj = unserialize($_SESSION[$table]);
				$rowId = Table::getIdentifierValue($table, $key);

				$index = 0;
				$query = "UPDATE `ansprechpartner` SET ";
				foreach ($data as $d) {
					$t = $tableObj->columnNames[$index]["COLUMN_NAME"];
					$query .= "`" . $t . "` = '" . $d . "', ";
					$index++;
				}

				$query = substr($query, 0, -2);
				$query .= " WHERE Nummer = $rowId";

				if (DBAccess::updateQuery($query) == 1) {
					echo "ok";
				} else {
					echo "error occured";
				}
			break;
			case "setAnspr":
				$idOrder = (int) $_POST["order"];
				$idAnspr = (int) $_POST["ansprId"];

				if (DBAccess::updateQuery("UPDATE auftrag SET Ansprechpartner = $idAnspr WHERE Auftragsnummer = $idOrder") == 1) {
					$ansprechpartner = (new Auftrag($idOrder))->bekommeAnsprechpartner();
					$data = [
						0 => "ok",
						1 => "Ansprechpartner: " . $ansprechpartner['Vorname'] . " " . $ansprechpartner['Nachname']
					];
					echo json_encode($data);
				} else {
					echo json_encode(["error occured"]);
				}
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
				$type = $_POST['type'];
				Table::updateValue($type . "_table", "delete", $_POST['key']);

				/* when a step is deleted, its connection to the notification manager must be deleted and it must be shown in the order histor */
				if ($type == "schritte") {
					$postennummer = Table::getIdentifierValue("schritte_table", $_POST['key']);
					$bezeichnung = Table::getValueByIdentifierColumn("schritte_table", $_POST['key'], "Bezeichnung");

					require_once("classes/project/Auftragsverlauf.php");
					$auftragsverlauf = new Auftragsverlauf($_POST['auftrag']);
					$auftragsverlauf->addToHistory($postennummer, 2, "deleted", $bezeichnung);

					$query = "UPDATE user_notifications SET ischecked = 1 WHERE specific_id = $postennummer";
					DBAccess::updateQuery($query);
				} else if ($type == "posten") {
					$postennummer = Table::getIdentifierValue("posten_table", $_POST['key']);
					$beschreibung = Table::getValueByIdentifierColumn("posten_table", $_POST['key'], "Beschreibung");

					require_once("classes/project/Auftragsverlauf.php");
					$auftragsverlauf = new Auftragsverlauf($_POST['auftrag']);
					$auftragsverlauf->addToHistory($postennummer, 1, "deleted", $beschreibung);
				}
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
			case "sendPostenPositions":
				$tableKey = $_POST["tablekey"];
				$order = $_POST["order"];
				$auftrag = $_POST["auftrag"];

				require_once('classes/project/Table.php');
				$order = json_decode($order);
				$counter = 1;
				foreach ($order as $row) {
					$id = Table::getIdentifierValue($tableKey, $row);

					/* must be rewritten later due to inefficiency */
					$query = "UPDATE posten SET position = $counter WHERE Auftragsnummer = $auftrag AND Postennummer = $id";
					DBAccess::updateQuery($query);
					$counter++;
				}

				echo "ok";
			break;
			case "deleteNote":
				$index = $_POST['number'];
				$order = $_POST['auftrag'];

				/* finds the possible note for deletion and sends back the left over ones */
				$possibleMatches = DBAccess::selectQuery("SELECT Nummer FROM notizen WHERE Auftragsnummer = $order");
				$match = $possibleMatches[$index]["Nummer"];
				DBAccess::deleteQuery("DELETE FROM notizen WHERE Nummer = $match");
				$order = new Auftrag($order);
				echo $order->getNotes();
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
					$timestamp = date("Y-m-d H:i:s");
					DBAccess::insertQuery("INSERT INTO verbesserungen (verbesserungen, erstelldatum) VALUES ('$verbesserung', '$timestamp')");
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
				DBAccess::updateQuery("UPDATE kunde_extended SET notizen = '$note' WHERE kundennummer = $kdnr");
				echo "ok";
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
				$zeitPosten = new Zeit($wage, $time, $descr, 0, 0);
				$angebot->addPosten($zeitPosten);
			break;
			case "addLeistungOffer":
				$customerId = $_POST['customerId'];
				$lei = $_POST['lei'];
				$bes = $_POST['bes'];
				$ekp = $_POST['ekp'];
				$pre = $_POST['pre'];
				$qty = $_POST['qty'];
				$meh = $_POST['meh'];
				$isFree = (int) $_POST['isFree'];
				$angebot = new Angebot($customerId);
				require_once("classes/project/Leistung.php");
				$leistungsPosten = new Leistung($lei, $bes, $pre, $ekp, $qty, $meh, 0, 0);
				$angebot->addPosten($leistungsPosten);
			break;
			case "storeOffer":
				/*$customerId = $_POST['customerId'];
				$angebot = new Angebot($customerId);
				$angebot->storeOffer();*/
				Angebot::setIsOrder();
			break;
			case "sendNewAddress":
				$kdnr = (int) $_POST['customer'];
				$plz = (int) $_POST['plz'];
				$ort = $_POST['ort'];
				$strasse = $_POST['strasse'];
				$hnr = $_POST['hnr'];
				$zusatz = $_POST['zusatz'];
				$land = $_POST['land'];
				Kunde::addAddress($kdnr, $strasse, $hnr, $plz, $ort, $zusatz, $land);
				require_once("classes/project/Address.php");
				echo json_encode(Address::loadAllAddresses($kdnr));
			break;
			case "setOrderFinished":
				$auftrag = $_POST['auftrag'];
				DBAccess::insertQuery("UPDATE auftrag SET archiviert = -1 WHERE Auftragsnummer = $auftrag");
			break;
			case "existingColors":
				require_once('classes/project/Auftrag.php');
				$auftrag = $_POST['auftrag'];
				$ids = json_decode($_POST['ids'], true);

				foreach($ids as $id) {
					$id = (int) $id;
					DBAccess::insertQuery("INSERT INTO color_auftrag (id_color, id_auftrag) VALUES ($id, $auftrag)");
				}
				
				$auftrag = new Auftrag($auftrag);
				$data = array("farben" => $auftrag->getFarben());
				echo json_encode($data, JSON_FORCE_OBJECT);
			break;
			case "newColor":
				require_once('classes/project/Auftrag.php');
				$auftrag = $_POST['auftrag'];
				$farbname = $_POST['farbname'];
				$farbwert = $_POST['farbwert'];
				$bezeichnung = $_POST['bezeichnung'];
				$hersteller = $_POST['hersteller'];

				$query = "INSERT INTO color (Farbe, Farbwert, Bezeichnung, Hersteller) VALUES ('$farbname', '$farbwert', '$bezeichnung', '$hersteller')";
				$id = DBAccess::insertQuery($query);
				
				DBAccess::insertQuery("INSERT INTO color_auftrag (id_color, id_auftrag) VALUES ($id, $auftrag)");

				$auftrag = new Auftrag($auftrag);
				$data = array("farben" => $auftrag->getFarben());
				echo json_encode($data, JSON_FORCE_OBJECT);
			break;
			case "removeColor":
				$colorId = $_POST['colorId'];
				$auftragsId = $_POST['auftrag'];
				
				DBAccess::deleteQuery("DELETE FROM color_auftrag WHERE id_color = $colorId AND id_auftrag = $auftragsId");

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
			case "getAddresses":
				require_once('classes/project/Address.php');
				$kdnr = (int) $_POST['kdnr']; 
				echo json_encode(Address::loadAllAddresses($kdnr));
			break;
			case "setData":
				if ($_POST['type'] == "kunde") {
					$number = (int) $_POST['number'];
					$kdnr = $_POST['kdnr'];
					for ($i = 0; $i < $number; $i++) {
						$dataKey = $_POST["dataKey$i"];
						$data = $_POST[$dataKey];

						/* maybe improve it later to be more flexible, currently it is just hardcoded for the exceptions */
						if ($dataKey == "ort" || $dataKey == "plz" || $dataKey == "strasse" || $dataKey == "hausnr") {
							/* gets from client the number of which address should be changed, must check the number with the array from Address class (same as client gets), then can update the correct row */
							$addressCount = (int) $_POST['addressCount'];
							require_once('classes/project/Address.php');
							$addressData = Address::loadAllAddresses($kdnr);
							$addressId = $addressData[$addressCount]["id"];
							DBAccess::updateQuery("UPDATE `address` SET $dataKey = '$data' WHERE id_customer = $kdnr AND id = $addressId");
						} else {
							//echo "UPDATE kunde SET $dataKey = '$data' WHERE Kundennummer = $kdnr";
							DBAccess::updateQuery("UPDATE kunde SET $dataKey = '$data' WHERE Kundennummer = $kdnr");
						}
					}
				}
				echo "ok";
			break;
			case "addAttVal":
				$attributeId = $_POST['att'];
				$value = $_POST['value'];
				$result = DBAccess::insertQuery("INSERT INTO attribute (attribute_group_id, `value`) VALUES ($attributeId, '$value')");
				echo $result;
			break;
			case "addAtt":
				$attribute = $_POST['name'];
				$descr = $_POST['descr'];
				$result = DBAccess::insertQuery("INSERT INTO attribute_group (attribute_group, `descr`) VALUES ('$attribute', '$descr')");
				echo $result;
			break;
			case "getList":
				require_once('classes/project/Liste.php');
				$lid = $_POST['listId'];
				$list = Liste::readList($lid);
				echo $list->toHTML();
			break;
			case "generateInvoicePDF":
				require_once("classes/project/Rechnung.php");
				if (isset($_SESSION['tempInvoice'])) {
					$rechnung = unserialize($_SESSION['tempInvoice']);
					$rechnung->PDFgenerieren(true);
				}
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

				$data = [
					0 => $auftrag->getAuftragspostenAsTable(),
					1 =>$auftrag->getInvoicePostenTable()
				];
				
				echo json_encode($data);
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
			case "saveTitle":
				$text = $_POST['text'];
				$auftrag = $_POST['auftrag'];
				DBAccess::updateQuery("UPDATE auftrag SET Auftragsbezeichnung = '$text' WHERE Auftragsnummer = $auftrag");
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
			case "addNewLine":
				require_once("classes/project/Table.php");

				$key = $_POST['key'];
				$data = $_POST['data'];
				echo Table::updateTable_AddNewLine($key, $data);
			break;
			case "addNoteOrder":
				$note = $_POST['note'];
				$orderId = (int) $_POST['auftrag'];
				$history_number = DBAccess::insertQuery("INSERT INTO notizen (Auftragsnummer, Notiz) VALUES ($orderId, '$note')");
				$auftrag = new Auftrag($orderId);
				echo $auftrag->getNotes();

				require_once("classes/project/Auftragsverlauf.php");
				$auftragsverlauf = new Auftragsverlauf($orderId);
				$auftragsverlauf->addToHistory($history_number, 7, "added", $note);
			break;
			case "setCustomColor":
				require_once("classes/project/ClientSettings.php");
				$color = $_POST['color'];
				$type = $_POST['type'];
				ClientSettings::setGrayScale($color, $type);
				echo "ok";
			break;
			case "getInfoText":
				$infoId = (int) $_POST['info'];
				$infoText = DBAccess::selectQuery("SELECT info FROM info_texte WHERE id = $infoId");
				echo $infoText[0]['info'];
			break;
			case "updateDate":
				$order = (int) $_POST['auftrag'];
				$date = $_POST['date'];
				$type = (int) $_POST['type'];
				$type = [
					1 => "Datum", 
					2 => "Termin", 
					3 => "Fertigstellung"
				][$type];

				DBAccess::updateQuery("UPDATE auftrag SET $type = '$date' WHERE Auftragsnummer = $order");
				echo "success";
			break;
			case "overwritePosten":
				$_SESSION['overwritePosten'] = true;
				$postennummer = Table::getIdentifierValue($_POST['table'], $_POST['postenId']);
				$_SESSION['overwritePosten_postennummer'] = $postennummer;

				$postenType = DBAccess::selectQuery("SELECT Posten FROM posten WHERE Postennummer = $postennummer")[0]["Posten"];
				$data = null;
				switch ($postenType) {
					case "zeit":
						$data = Zeit::getPostenData($postennummer);
						break;
					case "leistung":
						$data = Leistung::getPostenData($postennummer);
						break;
				}

				echo json_encode([
					"id" => $_SESSION['overwritePosten_postennummer'],
					"data" => $data
				]);
			break;
			case "updateProductValues":
				$productId = (int) $_POST['productId'];
				$content = $_POST['content'];
				$type = (int) $_POST['type'];

				switch($type) {
					case 1:
						DBAccess::updateQuery("UPDATE produkt SET Bezeichnung = '$content' WHERE Nummer = $productId");
						break;
					case 2:
						DBAccess::updateQuery("UPDATE produkt SET Beschreibung = '$content' WHERE Nummer = $productId");
						break;
					case 3:
						DBAccess::updateQuery("UPDATE produkt SET Preis = '$content' WHERE Nummer = $productId");
						break;
					default:
						echo "failiure";
						return;
				}
				
				echo "ok";
			break;
			case "frontAddToCart":
				$productId = (int) $_POST['productId'];
				require_once('classes/front/Cart.php');
				Cart::addToCart($productId);
			break;
			case "sendToDB":
				$title = $_POST['title'];
				$content = $_POST['content'];
				DBAccess::insertQuery("INSERT INTO wiki_articles (title, content) VALUES ('$title', '$content')");
			break;
			case "setInvoiceData":
				$orderId = (int) $_POST["rechnung"];
				//$id = DBAccess::selectQuery("SELECT id FROM invoice WHERE ");
			break;
			case "getManual":
				$pageName = $_POST['pageName'];
				$intent = $_POST['intent'];
				$data = DBAccess::selectQuery("SELECT info FROM `manual` WHERE `page` = '$pageName' AND intent = '$intent'");
				echo json_encode($data, JSON_FORCE_OBJECT);;
			break;
			case "setDateInvoice":
				require_once('classes/project/Rechnung.php');
				$date = $_POST['date'];
				$date = date('d.m.Y', strtotime($date));

				$rechnung = unserialize($_SESSION['tempInvoice']);
				$rechnung->setDate($date);
				$_SESSION['tempInvoice'] = serialize($rechnung);
				echo "ok";
			break;
			case "setDatePerformance":
				require_once('classes/project/Rechnung.php');
				$date = $_POST['date'];
				$date = date('d.m.Y', strtotime($date));

				$rechnung = unserialize($_SESSION['tempInvoice']);
				$rechnung->setDatePerformance($date);
				$_SESSION['tempInvoice'] = serialize($rechnung);
				echo "ok";
			break;
			case "invoiceAddText":
				require_once('classes/project/Rechnung.php');
				$id = (int) $_POST['id'];
				$text = $_POST['text'];

				$rechnung = unserialize($_SESSION['tempInvoice']);
				$rechnung->addText($id, $text);
				$_SESSION['tempInvoice'] = serialize($rechnung);
			break;
			case "invoiceRemoveText":
				require_once('classes/project/Rechnung.php');
				$id = (int) $_POST['id'];

				$rechnung = unserialize($_SESSION['tempInvoice']);
				$rechnung->removeText($id);
				$_SESSION['tempInvoice'] = serialize($rechnung);
			break;
			case "setInvoiceParameters":
				require_once('classes/project/Rechnung.php');

				$orderId = (int) $_POST["auftrag"];
				$address = (int) $_POST["address"];
				$invoiceDate = $_POST["invoiceDate"];
				$leistungsDate = $_POST["leistungDate"];

				$rechnung = unserialize($_SESSION['tempInvoice']);
				
				if ($address != 0) {
					echo $rechnung->setAddress($address);
				}

				/*
				if ($invoiceDate != "") {
					$rechnung->setInvoiceDate($invoiceDate);
				}

				if ($leistungsDate != "") {
					$rechnung->setLeistungsDate($leistungsDate);
				}
				*/

				$_SESSION['tempInvoice'] = serialize($rechnung);
				echo "ok";
			break;
			case "toggleCache":
				require_once('classes/project/CacheManager.php');
				$status = $_POST['status'];
				switch ($status) {
					case "on":
						if (CacheManager::cacheOn() == true)
							echo "ok";
						break;
					case "off":
						if (CacheManager::cacheOff() == true)
							echo "ok";
						break;
					default:
						echo "an unexpected error occured";
						break;
				}
			break;
			case "setNotificationsRead":
				$notificationIds = $_POST["notificationIds"];
				if ($notificationIds == "all") {
					require_once('classes/project/NotificationManager.php');
					NotificationManager::setNotificationsRead(-1);
				} else {

				}
			break;
			case "getBackup":
				$result = DBAccess::EXPORT_DATABASE(HOST, USERNAME, PASSWORD, DATABASE);
				$filePath = "files/generated/sql_backups/";
				$fileName = date("d-m-Y_h-i-s") . ".sql";
				file_put_contents(($filePath . $fileName), $result);

				$data = array("fileName" => $fileName, "url" => Link::getResourcesShortLink($fileName, "backup"), "status" => "ok");
				echo json_encode($data, JSON_FORCE_OBJECT);
			break;
			case "insertAttributeTable":
				$productId = (int) $_POST["productId"];
				$attributeTable = $_POST["attributes"];
				$data = json_decode($attributeTable, true);

				Produkt::addAttributeVariations($productId, $data);
				echo "ok";
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