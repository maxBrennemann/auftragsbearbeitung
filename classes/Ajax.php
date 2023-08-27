<?php

require_once("classes/project/Produkt.php");
require_once("classes/project/Auftrag.php");

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
					$column_names = array(
						0 => array("COLUMN_NAME" => "Postennummer"),
						1 => array("COLUMN_NAME" => "Bezeichnung"), 
						2 => array("COLUMN_NAME" => "Beschreibung"),
						3 => array("COLUMN_NAME" => "Preis"), 
						4 => array("COLUMN_NAME" => "ZeitInMinuten"),
						5 => array("COLUMN_NAME" => "Stundenlohn")
					);
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
			case "addCustomer":
				$data = $_POST['data'];
				$data = json_decode($data, true);
				require_once('classes/project/Kunde.php');
				$customerId = Kunde::addCustomer($data);
				$link = Link::getPageLink("kunde");
				$link .= "?id=" . $customerId;

				echo json_encode([
					"status" => "success",
					"link" => $link,
				]);
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
				Auftrag::add();
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

				if (isset($_POST['isOverwrite']) && (int) $_POST['isOverwrite'] == 1) {
					$_SESSION['overwritePosten'] = false;
				}

				$ids = Posten::insertPosten("zeit", $data);

				/* erweiterte Zeiterfassung */
				$zeiterfassung = json_decode($_POST['zeiterfassung'], true);
				if (count($zeiterfassung) != 0) {
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
				$column_names = array(
					0 => array("COLUMN_NAME" => "Vorname"),
					1 => array("COLUMN_NAME" => "Nachname"),
					2 => array("COLUMN_NAME" => "Email"),
					3 => array("COLUMN_NAME" => "Durchwahl"),
					4 => array("COLUMN_NAME" => "Mobiltelefonnummer")
				);
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
				$column_names = array(
					0 => array("COLUMN_NAME" => "Bezeichnung"),
					1 => array("COLUMN_NAME" => "Priority")
				);

				$addClass = $_POST['addClass'];

				echo FormGenerator::createEmptyTable($column_names, $addClass);
			break;
			case "getAllSteps":
				$auftragsId = $_POST['auftrag'];
				$auftrag = new Auftrag($auftragsId);
				echo $auftrag->getBearbeitungsschritteAsTable();
			break;
			case "getOpenSteps":
				$auftragsId = $_POST['auftrag'];
				$auftrag = new Auftrag($auftragsId);
				echo $auftrag->getOpenBearbeitungsschritteTable();
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
			case "setInvoicePaid":
				$order = $_POST['order'];
				$invoice = $_POST['invoice'];
				DBAccess::updateQuery("UPDATE auftrag SET Bezahlt = 1 WHERE Auftragsnummer = :order AND Rechnungsnummer = :invoice", [
					"order" => $order,
					"invoice" => $invoice,
				]);
				echo json_encode([
					"status" => "success",
				]);
			break;
			case "setInvoiceData":
				$order = $_POST['id'];
				$invoice = $_POST['invoice'];
				$date = $_POST['date'];
				$paymentType = $_POST['paymentType'];

				DBAccess::updateQuery("UPDATE auftrag SET Bezahlt = 1 WHERE Auftragsnummer = :order AND Rechnungsnummer = :invoice", [
					"order" => $order,
					"invoice" => $invoice,
				]);

				DBAccess::updateQuery("UPDATE invoice SET payment_date = :paymentDate, payment_type = :paymentType WHERE order_id = :order", [
					"paymentDate" => $date,
					"paymentType" => $paymentType,
					"order" => $order,
				]);

				echo json_encode([
					"status" => "success",
				]);
			break;
			case "setTo":
				if (isset($_POST['auftrag'])) {
					require_once("classes/project/InteractiveFormGenerator.php");
					$table = unserialize($_SESSION['storedTable']);
					$auftragsId = $_POST['auftrag'];
					$row = $_POST['row'];
					$table->setIdentifier("Schrittnummer");
					$date = date("Y-m-d");
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
			case "deleteOrder":
				// TODO: implement db triggers for order deletion
				$id = (int) $_POST["id"];
				$query = "DELETE FROM auftrag WHERE Auftragsnummer = :id;";
				DBAccess::deleteQuery($query, ["id" => $id]);
				echo json_encode([
					"success" => true,
					"home" => Link::getPageLink(""),
				]);
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
			case "deleteSize":
				$key = $_POST["key"];
				$table = $_POST["table"];
				Table::updateValue($table, "delete", $_POST['key']);
			break;
			case "sendSource":
				Produkt::addSource();
			break;
			case "getSelect":
				Produkt::getSelectSource();	
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
				$kdnr = (int) $_POST['kdnr'];
				$note = $_POST['notes'];
				DBAccess::updateQuery("UPDATE kunde_extended SET notizen = :notes WHERE kundennummer = :customerId", [
					"notes" => $note,
					"customerId" => $kdnr,
				]);
				
				echo "ok";
			break;
			case "addLeistung":
				$bezeichnung = $_POST['bezeichnung'];
				$description = $_POST['description'];
				$source = $_POST['source'];
				$aufschlag = $_POST['aufschlag'];

				$newInserted = DBAccess::insertQuery("INSERT INTO leistung (Bezeichnung, Beschreibung, Quelle, Aufschlag) VALUES (:bez, :desc, :source, :aufschlag);", [
					"bez" => $bezeichnung,
					"desc" => $description,
					"source" => $source,
					"aufschlag" => $aufschlag,
				]);

				echo json_encode([
					"status" => "success",
					"leistungsId" => $newInserted,
				]);
			break;
			case "editLeistung":
				$id = (int) $_POST["id"];
				$bezeichnung = $_POST['bezeichnung'];
				$description = $_POST['description'];
				$source = $_POST['source'];
				$aufschlag = $_POST['aufschlag'];

				DBAccess::updateQuery("UPDATE leistung SET Bezeichnung = :bez, Beschreibung = :desc, Quelle = :source, Aufschlag = :aufschlag WHERE Nummer = :id;", [
					"bez" => $bezeichnung,
					"desc" => $description,
					"source" => $source,
					"aufschlag" => $aufschlag,
					"id" => $id,
				]);

				echo json_encode([
					"status" => "success",
				]);
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
				$auftrag = $_POST['auftrag'];
				$auftrag = new Auftrag($auftrag);
				$auftrag->archiveOrder();
			break;
			case "rearchive":
				$auftrag = $_POST['auftrag'];
				$auftrag = new Auftrag($auftrag);
				$auftrag->rearchiveOrder();
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
			case "saveOrderType";
				$idOrderType = (int) $_POST['type'];
				$idOrder = (int) $_POST['auftrag'];
				$query = "UPDATE `auftrag` SET `Auftragstyp` = :idOrderType WHERE `Auftragsnummer` = :idOrder";
				
				DBAccess::updateQuery($query, [
					"idOrder" => $idOrder,
					"idOrderType" => $idOrderType,
				]);
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

				require_once("classes/project/Auftragsverlauf.php");
				$auftragsverlauf = new Auftragsverlauf($orderId);
				$auftragsverlauf->addToHistory($history_number, 7, "added", $note);

				$notes = [
					[
						"Notiz" => $note,
						"Nummer" => $history_number,
					]
				];
		
				ob_start();
				insertTemplate('files/res/views/noteView.php', [
					"notes" => $notes,
					"icon" => Icon::getDefault("iconNotebook"),
				]);
				$content = ob_get_clean();

				echo json_encode([
					"status" => "success",
					"content" => $content,
				]);
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
				$infoText = DBAccess::selectQuery("SELECT info FROM info_texte WHERE id = :infoId;", [
					"infoId" => $infoId,
				]);

				if ($infoText == null) {
					echo "Kein Text vorhanden";
					return;
				}
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

				if ($date == "unset") {
					DBAccess::updateQuery("UPDATE auftrag SET $type = NULL WHERE Auftragsnummer = :order;", [
						"order" => $order,
					]);
				} else {
					DBAccess::updateQuery("UPDATE auftrag SET $type = :setDate WHERE Auftragsnummer = :order;", [
						"setDate" => $date,
						"order" => $order,
					]);
				}
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
			case "updateDefaultWage":
				$defaultWage = $_POST["defaultWage"];
				Envs::set("defaultWage", $defaultWage);
				echo json_encode([]);
			break;
			case "getManual":
				$pageName = $_POST['pageName'];
				$intent = $_POST['intent'];
				$data = DBAccess::selectQuery("SELECT info FROM `manual` WHERE `page` = '$pageName' AND intent = '$intent'");
				echo json_encode($data, JSON_FORCE_OBJECT);
			break;
			case "setDateInvoice":
				require_once('classes/project/Rechnung.php');
				$date = $_POST['date'];
				$date = date('d.m.Y', strtotime($date));

				$rechnung = unserialize($_SESSION['tempInvoice']);
				$rechnung->setDate($date);
				$_SESSION['tempInvoice'] = serialize($rechnung);

				echo json_encode([
					0 => "ok",
					1 => Rechnung::getAllInvoiceItems($_POST["id"], $rechnung)
				], JSON_FORCE_OBJECT);
			break;
			case "setDatePerformance":
				require_once('classes/project/Rechnung.php');
				$date = $_POST['date'];
				$date = date('d.m.Y', strtotime($date));

				$rechnung = unserialize($_SESSION['tempInvoice']);
				$rechnung->setDatePerformance($date);
				$_SESSION['tempInvoice'] = serialize($rechnung);

				echo json_encode([
					0 => "ok",
					1 => Rechnung::getAllInvoiceItems($_POST["id"], $rechnung)
				], JSON_FORCE_OBJECT);
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
			case "toggleMinify":
				$status = (String) $_POST["status"];
				if ($status == "off" || $status == "on") {
					DBAccess::updateQuery("UPDATE settings SET content = :status WHERE title = 'minifyStatus'", ["status" => $status]);
					echo "ok";
				} else {
					echo "error";
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
			case "login":
				$login = Login::handleLogin();
				if ($login == false) {
					echo json_encode(["status" => "error"]);
				} else {
					echo json_encode([
						"status" => "success",
						"deviceKey" => $login["deviceKey"],
						"loginKey" => Login::getLoginKey($login["deviceId"]),
					]);
				}
			break;
			case "logout":
				Login::handleLogout();
			break;
			case "checkAutoLogin":
				$loginKey = Login::handleAutoLogin();
				$status = $loginKey === false ? "failed" : "success";
				echo json_encode([
					"status" => $status,
					"loginKey" => $loginKey,
				]);
			break;
			case "minifyFiles":
				require_once("classes/MinifyFiles.php");
				MinifyFiles::minify();
				echo json_encode(["status" => "success"]);
			break;
			case "upgrade":
				require_once("upgrade/UpgradeManager.php");
				$query = $_POST["query"];
				switch ($query) {
					case 1:
						UpgradeManager::executeFirstCommand();
						break;
					case 2:
						$files = UpgradeManager::checkNewSQL();
						$result = [];
						foreach ($files as $file) {
							array_push($result, UpgradeManager::executeNewSQLQueries($file));
							//array_push($result, UpgradeManager::executeSecondCommand($file));
						}
						echo json_encode($result);
						break;
					case 3:
						require_once("classes/MinifyFiles.php");
						MinifyFiles::minify();
						echo json_encode(["result" => "all files are recompiled", "command" => "minify files"]);
						break;
					case 4:
						$commandRes = shell_exec("composer install");
						if ($commandRes === NULL) {
							echo json_encode(["result" => "an error occured", "command" => "composer install"]);
						} else {
							echo json_encode(["result" => $commandRes, "command" => "composer install"]);
						}
						break;
					case 5:
						$commandRes = shell_exec("composer update");
						if ($commandRes === NULL) {
							echo json_encode(["result" => "an error occured", "command" => "composer update"]);
						} else {
							echo json_encode(["result" => $commandRes, "command" => "composer update"]);
						}
						break;
					default:
						echo "an error occured";
				}
			break;
			case "writeProductDescription":
				require_once("classes/project/modules/sticker/Sticker.php");
				Sticker::setDescription();
			break;
			case "writeSpeicherort":
				$id = (int) $_POST["id"];
				$content = (String) $_POST["content"];
				$content = urldecode($content);

				$query = "UPDATE module_sticker_sticker_data SET directory_name = :content WHERE id = :id;";
				DBAccess::updateQuery($query, ["id" => $id, "content" => $content]);
				echo "success";
			break;
			case "writeAdditionalInfo":
				$id = (int) $_POST["id"];
				$content = (String) $_POST["content"];

				$query = "UPDATE module_sticker_sticker_data SET additional_info = '$content' WHERE id = $id;";
				DBAccess::updateQuery($query);
				echo "success";
			break;
			case "transferProduct":
				$id = (int) $_POST["id"];
				$type = (int) $_POST["type"];
				$overwrite = json_decode($_POST["overwrite"], true);
				$message = "";
				$responseData = [];
				
				require_once("classes/project/modules/sticker/StickerCollection.php");
				ob_start();
				try {
					switch ($type) {
						case 1:
							$aufkleber = new Aufkleber($id);
							$aufkleber->save($overwrite["aufkleber"]);
							break;
						case 2:
							$wandtattoo = new Wandtattoo($id);
							$wandtattoo->save($overwrite["wandtattoo"]);
							break;
						case 3:
							$textil = new Textil($id);
							$textil->save($overwrite["textil"]);
							break;
						case 4:
							/* TODO: iteration bei StickerCollection überarbeiten */
							$stickerCollection = new StickerCollection($id);
							$stickerCollection->getAufkleber()->save($overwrite["aufkleber"]);
							$stickerCollection->getWandtattoo()->save($overwrite["wandtattoo"]);
							$stickerCollection->getTextil()->save($overwrite["textil"]);
							break;
					}
				} catch (Exception $e) {
					$message = $e->getMessage();
				}
				$responseData["output"] = ob_get_clean();

				require_once("classes/project/modules/sticker/SearchProducts.php");

				try {
					$responseData = SearchProducts::getProductsByStickerId($id);

					$matchesJson = json_encode($responseData, JSON_UNESCAPED_UNICODE);
					DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET additional_data = :matchesJSON WHERE id = :idSticker", [
						"matchesJSON" => $matchesJson,
						"idSticker" => $id,
					]);
				} catch (Exception $e) {
					$message = $e->getMessage();
				}
				
				if ($message == "") {
					echo json_encode([
						"status" => "success",
						"responseData" => $responseData,
					]);
				} else {
					echo json_encode([
						"status" => "error",
						"message" => $message,
						"responseData" => $responseData,
					]);
				}
			break;
			case "getSizeTable":
				require_once("classes/project/modules/sticker/Aufkleber.php");
				$id = (int) $_POST["id"];

				$aufkleber = new Aufkleber($id);
				echo $aufkleber->getSizeTable();
			break;
			case "deleteImage":
				$imageId = (int) $_POST["imageId"];
				$query = "DELETE FROM dateien WHERE id = :idImage;";
				DBAccess::deleteQuery($query, ["idImage" => $imageId]);
				echo json_encode([
					"status" => "success",
				]);
			break;
			case "setImageOrder":
				$order = $_POST["order"];

				require_once("classes/project/modules/sticker/StickerImage.php");

				try {
					StickerImage::setImageOrder($order);
					echo json_encode([
						"status" => "success",
					]);
				} catch (Exception $e) {
					echo json_encode([
						"status" => "error",
						"message" => $e->getMessage(),
					]);
				}
			break;
			case "changePreiskategorie":
				$id = (int) $_POST['id'];
				$categoryId = $_POST['categoryId'];
				DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET price_type = '$categoryId' WHERE id = $id");
				echo "success";
			break;
			case "changeMotivDate":
				$id = (int) $_POST['id'];
				$creation_date = $_POST['date'];

				DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET creation_date = :creation_date WHERE id = :id", ["creation_date" => $creation_date, "id" => $id]);
				echo "success";
			break;
			case "getStickerStatus":
				$query = "SELECT id, additional_data FROM module_sticker_sticker_data ORDER BY id ASC";
				$data = DBAccess::selectQuery($query);
				$isInShopStatus = [];

				foreach ($data as $row) {
					$id = (int) $row["id"];
					$isInShopStatus[$id] = [];
					if ($row["additional_data"] == null) {
						continue;
					}
					$additionalData = json_decode($row["additional_data"], true);

					if (isset($additionalData["products"])) {
						$products = $additionalData["products"];

						if (isset($products["aufkleber"])) {
							$isInShopStatus[$id]["a"] = $products["aufkleber"]["id"];
						}
						if (isset($products["wandtattoo"])) {
							$isInShopStatus[$id]["w"] = $products["wandtattoo"]["id"];
						}
						if (isset($products["textil"])) {
							$isInShopStatus[$id]["t"] = $products["textil"]["id"];
						}
					}
				}
				echo json_encode($isInShopStatus);
			break;
			case "toggleTextil":
				$id = (int) $_POST["id"];
				DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_shirtcollection` = NOT `is_shirtcollection` WHERE id = $id");
				echo json_encode([
					"status" => "success",
				]);
			break;
			case "toggleWandtattoo":
				$id = (int) $_POST["id"];
				DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_walldecal` = NOT `is_walldecal` WHERE id = $id");
				echo json_encode([
					"status" => "success",
				]);
			break;
			case "toggleRevised":
				$id = (int) $_POST["id"];
				DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_revised` = NOT `is_revised` WHERE id = $id");
				echo "success";
			break;
			case "toggleBookmark":
				$id = (int) $_POST["id"];
				DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_marked` = NOT `is_marked` WHERE id = $id");
				echo "success";
			break;
			case "makeSVGColorable":
				$id = (int) $_POST["id"];

				require_once('classes/project/modules/sticker/Textil.php');
				$textil = new Textil($id);
				$textil->toggleIsColorable();
				$file = $textil->getCurrentSVG();

				if ($file == null) {
					echo json_encode(["status" => "no file found"]);
				} else {
					$url = Link::getResourcesShortLink($file["dateiname"], "upload");
					echo json_encode(["url" => $url]);
				}
			break;
			case "makeCustomizable":
				$id = (int) $_POST["id"];

				require_once('classes/project/modules/sticker/Textil.php');
				$textil = new Textil($id);
				$textil->toggleCustomizable();
			break;
			case "makeForConfig":
				$id = (int) $_POST["id"];

				require_once('classes/project/modules/sticker/Textil.php');
				$textil = new Textil($id);
				$textil->toggleConfig();
			break;
			case "createNewSticker":
				require_once('classes/project/modules/sticker/Sticker.php');
				$title = (String) $_POST["newTitle"];
				Sticker::createNewSticker($title);
			break;
			case "setPriceclass":
				$priceclass = (int) $_POST["priceclass"];
				$id = (int) $_POST["id"];

				DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET price_class = :priceClass WHERE id = :id", ["priceClass" => $priceclass, "id" => $id]);
				echo "ok";
			break;
			case "resetStickerPrice":
				$tableRowKey = $_POST["row"];
				$table = $_POST["table"];
				$id = (int) $_POST["id"];

				$postenNummer = Table::getIdentifierValue($table, $tableRowKey);
				$size = DBAccess::selectQuery("SELECT width, height FROM module_sticker_sizes WHERE id = $postenNummer LIMIT 1");
				$width = $size[0]["width"];
				$height = $size[0]["height"];

				require_once('classes/project/modules/sticker/AufkleberWandtattoo.php');
				$aufkleberWandtatto = new AufkleberWandtattoo($id);
				$difficulty = $aufkleberWandtatto->getDifficulty();
				$price = $aufkleberWandtatto->getPrice($width, $height, $difficulty);

				DBAccess::updateQuery("UPDATE module_sticker_sizes SET price = '$price', price_default = 1 WHERE id = $postenNummer");
				echo $price;
			break;
			case "setAufkleberParameter":
				require_once('classes/project/modules/sticker/Aufkleber.php');
				$id = (int) $_POST["id"];
				$data = $_POST["json"];
				$aufkleber = new Aufkleber($id);
				$aufkleber->saveSentData($data);
			break;
			case "setAufkleberTitle":
				require_once('classes/project/modules/sticker/Sticker.php');
				$id = (int) $_POST["id"];
				$title = (String) $_POST["title"];

				$sticker = new Sticker($id);
				$response = $sticker->setName($title);

				echo $response["status"];
				//echo json_encode($response);
			break;
			case "setAufkleberGroessen":
				$sizes = json_decode($_POST["sizes"], true);

				require_once('classes/project/modules/sticker/AufkleberWandtattoo.php');
				$id = (int) $_POST["id"];
				$aufkleberWandtatto = new AufkleberWandtattoo($id);
				foreach ($sizes["sizes"] as $size) {
					$aufkleberWandtatto->updateSizeTable($size);
				}
			break;
			case "addSize":
				$width = (int) $_POST["width"];
				$height = (int) $_POST["height"];
				$price = (int) $_POST["price"];
				$id = (int) $_POST["id"];
				$isDefault = (int) $_POST["isDefaultPrice"];

				$query = "INSERT INTO module_sticker_sizes (width, height, price, id_sticker, price_default) VALUES (:width, :height, :price, :id, :default)";
				$id = DBAccess::insertQuery($query, [
					"width" => $width,
					"height" => $height,
					"price" => $price,
					"id" => $id,
					"default" => $isDefault,
				]);

				echo json_encode([
					"status" => "success",
					"id" => $id,
				]);
			break;
			case "deleteSizeRow":
				$id = (int) $_POST["id"];
				$query = "DELETE FROM module_sticker_sizes WHERE id = :id;";
				DBAccess::deleteQuery($query, ["id" => $id]);
				echo "success";
			break;
			case "resetSizeRow":
				$id = (int) $_POST["id"];
				$size = DBAccess::selectQuery("SELECT width, height FROM module_sticker_sizes WHERE id = :id LIMIT 1", ["id" => $id]);
				$width = $size[0]["width"];
				$height = $size[0]["height"];

				require_once('classes/project/modules/sticker/AufkleberWandtattoo.php');
				$aufkleberWandtatto = new AufkleberWandtattoo($id);
				$difficulty = $aufkleberWandtatto->getDifficulty();
				$price = $aufkleberWandtatto->getPrice($width, $height, $difficulty);

				DBAccess::updateQuery("UPDATE module_sticker_sizes SET price = '$price', price_default = 1 WHERE id = $postenNummer");
				echo $price;
			break;
			case "setSizePrice":
				$idWidth = (int) $_POST["id"];
				$price = (int) $_POST["price"];
				
				$query = "UPDATE module_sticker_sizes SET price = :price, price_default = 0 WHERE id = :id;";
				DBAccess::insertQuery($query, [
					"price" => $price,
					"id" => $idWidth
				]);
			break;
			case "productVisibility":
				require_once('classes/project/modules/sticker/StickerCollection.php');
				$id = (int) $_POST["id"];
				$stickerCollection = new StickerCollection($id);
				$stickerCollection->toggleActiveStatus();
			break;
			case "addTag":
				require_once('classes/project/modules/sticker/StickerTagManager.php');
				StickerTagManager::addTag();
			break;
			case "removeTag":
				require_once('classes/project/modules/sticker/StickerTagManager.php');
				StickerTagManager::removeTag();
			break;
			case "getMoreTagSuggestions":
				$id = (int) $_POST["id"];
				$name = (String) $_POST["name"];
				$stickerTagManager = new StickerTagManager($id, $name);
				$stickerTagManager->getTagsHTML();
			break;
			case "getTagOverview":
				require_once('classes/project/modules/sticker/StickerTagManager.php');
				echo json_encode([
					"status" => "success",
					"tags" => StickerTagManager::countTagOccurences(),
				]);
			break;
			case "getTagGroups":
				$query = "SELECT g.id AS groupId, g.title AS groupName, t.id AS tagId, t.content AS tagName FROM module_sticker_sticker_tag_group g LEFT JOIN module_sticker_sticker_tag_group_match m ON g.id = m.idGroup LEFT JOIN module_sticker_tags t ON t.id = m.idTag;";
				$data = DBAccess::selectQuery($query);
				echo json_encode([
					"tagGroups" => $data,
				]);
			break;
			case "addNewTagGroup":
				$title = (String) $_POST["title"];
				require_once('classes/project/modules/sticker/StickerTagManager.php');
				$idTagGroup = StickerTagManager::addTagGroup($title);
				echo json_encode([
					"status" => "success",
					"idTagGroup" => $idTagGroup,
				]);
			break;
			case "addNewUser":
				$username = (String) $_POST["username"];
				$password = (String) $_POST["password"];
				$email = (String) $_POST["email"];
				$prename = (String) $_POST["prename"];
				$lastname = (String) $_POST["lastname"];

				require_once('classes/project/User.php');
				User::add($username, $email, $prename, $lastname, $password);

				echo json_encode([
					"status" => "success",
				]);
			break;
			case "crawlAll":
				require_once('classes/project/modules/sticker/ProductCrawler.php');
				$pc = new ProductCrawler();
				$pc->crawlAll();
			break;
			case "crawlTags":
				require_once('classes/project/modules/sticker/StickerTagManager.php');
				StickerTagManager::crawlAllTags();
			break;
			case "setAltTitle":
				$id = (int) $_POST["id"];
				$newTitle = (String) $_POST["newTitle"];
				$type = (String) $_POST["type"];

				$additionalData = DBAccess::selectQuery("SELECT additional_data FROM module_sticker_sticker_data WHERE id = :id LIMIT 1", ["id" => $id]);

				if ($additionalData[0] != null) {
					$additionalData = json_decode($additionalData[0]["additional_data"], true);
					
					$additionalData["products"][$type]["altTitle"] = $newTitle;
					$data = json_encode($additionalData);

					DBAccess::insertQuery("UPDATE module_sticker_sticker_data SET additional_data = :data WHERE id = :id", ["data" => $data, "id" => $id]);
					echo json_encode(["status" => "success"]);
				} else {
					echo json_encode(["status" => "no data found"]);
				}
			break;
			case "clearFiles":
				require_once('classes/Upload.php');
				Upload::deleteUnusedFiles();
			break;
			case "adjustFiles":
				require_once('classes/Upload.php');
				Upload::adjustFileNames();
			break;
			case "createFbExport":
				require_once('classes/project/modules/sticker/exports/ExportFacebook.php');

				$export = new ExportFacebook();
				$export->generateCSV();
				$filename = $export->getFilename();
				$fileLink = Link::getResourcesLink("files/generated/fb_export/" . $filename, "html");

				echo json_encode([
					"status" => "successful",
					"file" => $fileLink,
					"errorList" => $errorList,
				]);
			break;
			case "showSearch":
				$id = (int) $_POST["id"];
				$type = $_POST["type"];

				$products = DBAccess::selectQuery("SELECT a.id_product_reference, a.`title` as `name` FROM module_sticker_accessoires a WHERE a.id_sticker = :idSticker AND a.`type` = :type;", [
					"idSticker" => $id,
					"type" => $type,
				]);

				insertTemplate('classes/project/modules/sticker/views/showSearchView.php', ["products" => $products]);
			break;
			case "connectAccessoire":
				$idSticker = (int) $_POST["id"];
				$idProductReference = (int) $_POST["articleId"];
				$type = (String) $_POST["type"];
				$title = (String) $_POST["title"];
				$status = $_POST["status"] == "true";

				if ($status) {
					$query = "INSERT INTO `module_sticker_accessoires` (`id_sticker`, `type`, `id_product_reference`, `title`) VALUES (:idSticker, :type, :idProductReference, :title)";
					DBAccess::insertQuery($query, [
						"idSticker" => $idSticker,
						"type" => $type,
						"idProductReference" => $idProductReference,
						"title" => $title,
					]);
				} else {
					$query = "DELETE FROM `module_sticker_accessoires` WHERE `id_sticker` = :idSticker AND `id_product_reference` = :idProductReference AND `type` = :type";
					DBAccess::deleteQuery($query, [
						"idSticker" => $idSticker,
						"type" => $type,
						"idProductReference" => $idProductReference,
					]);
				}

				echo json_encode([
					"status" => "success",
				]);
			break;
			case "removeAccessoire":
				$idSticker = (int) $_POST["id"];
				$idProductReference = (int) $_POST["idProductReference"];
				$type = (String) $_POST["type"];

				$query = "DELETE FROM `module_sticker_accessoires` WHERE `id_sticker` = :idSticker AND `id_product_reference` = :idProductReference AND `type` = :type";
				DBAccess::deleteQuery($query, [
					"idSticker" => $idSticker,
					"type" => $type,
					"idProductReference" => $idProductReference,
				]);

				echo json_encode([
					"status" => "success",
				]);
			break;
			case "searchShop":
				require_once('classes/project/modules/sticker/SearchProducts.php');
				$search = $_POST["query"];
				echo json_encode(SearchProducts::search($search, ["name", "description", "description_short"]));
			break;
			case "getCategoryTree":
				$startCategory = $_POST["categoryId"];
				require_once('classes/project/modules/sticker/StickerCategory.php');
				echo json_encode(StickerCategory::getCategories($startCategory));
			break;
			case "getCategories":
				$id = (int) $_POST["id"];
				require_once('classes/project/modules/sticker/StickerCategory.php');
				echo json_encode(StickerCategory::getCategoriesForSticker($id));
			break;
			case "getCategoriesSuggestion":
				$name = $_POST["name"];
				$id = (int) $_POST["id"];
				require_once('classes/project/modules/sticker/StickerCategory.php');
				echo StickerCategory::getCategoriesSuggestion($name, $id);
			break;
			case "setCategories":
				$id = (int) $_POST["id"];
				$categories = $_POST["categories"];
				require_once('classes/project/modules/sticker/StickerCategory.php');
				StickerCategory::setCategories($id, $categories);
			break;
			case "setExportStatus":
				$status = (String) $_POST["export"];
				$id = (int) $_POST["id"];

				$export = DBAccess::selectQuery("SELECT * FROM module_sticker_exports WHERE idSticker = :idSticker", ["idSticker" => $id]);
				//Protocoll::prettyPrint($export);
				if ($export[0][$status] == NULL) {
					$query = "UPDATE module_sticker_exports SET $status = -1 WHERE idSticker = :idSticker";
					DBAccess::updateQuery($query, ["idSticker" => $id]);
				} else if ($export[0][$status] != NULL) {
					$query = "UPDATE module_sticker_exports SET $status = NULL WHERE idSticker = :idSticker";
					DBAccess::updateQuery($query, ["idSticker" => $id]);
				} else {
					echo "error";
				}
				
				echo "success";
			break;
			case "toggleShowTime":
				Envs::toggle("showTimeGlobal");

				echo json_encode([
					"status" => "success",
				]);
			break;
			case "getIcon":
				$type = (String) $_POST["icon"];
				$icon = "";

				if (isset($_POST["custom"])) {
					$width = (int) $_POST["width"];
					$height = (int) $_POST["height"];

					/* classes from frontend come as commma separated string */
					$classes = (String) $_POST["classes"];
					$classes = explode(",", $classes);

					if (isset($_POST["title"])) {
						$title = (String) $_POST["title"];
					} else {
						$title = "";
					}

					if (isset($_POST["color"])) {
						$color = (String) $_POST["color"];
					} else {
						$color = "#000000";
					}

					$icon = Icon::getColorized($type, $width, $height, $color, $classes, $title);
				} else {
					$icon = Icon::getDefault($type);
				}

				if ($icon != "") {
					echo json_encode([
						"status" => "success",
						"icon" => $icon,
					]);
				} else {
					echo json_encode([
						"status" => "not found",
					]);
				}
			break;
			case "generateText":
				$title = $_POST["title"];
				$text = $_POST["text"];
				$type = $_POST["type"];

				$additionalText = $_POST["additionalText"];
				$additionalStyle = $_POST["additionalStyle"];

				$id = (int) $_POST["id"];

				require_once('classes/project/modules/sticker/ChatGPTConnection.php');
				$connector = new ChatGPTConnection($id);
				$connector->getTextSuggestion($title, $type, $text, $additionalText, $additionalStyle);
			break;
			case "showGTPOptions":
				$stickerId = $_POST["id"];
				$stickerType = $_POST["type"];
				$text = $_POST["text"];

				$query = "SELECT id, chatgptResponse, DATE_FORMAT(creationDate, '%d. %M %Y') as creationDate, textType, additionalQuery, textStyle FROM module_sticker_chatgpt WHERE idSticker = :stickerId AND stickerType = :stickerType;";
				$result = DBAccess::selectQuery($query, [
					"stickerId" => $stickerId,
					"stickerType" => $stickerType
				]);

				ob_start();
				insertTemplate('classes/project/modules/sticker/views/chatGPTOptionsView.php', [
					"texts" => $result,
				]);
				$content = ob_get_clean();
				echo json_encode([
					"template" => $content,
				]);
			break;
			case "setRechnungspostenAusblenden":
				require_once('classes/project/ClientSettings.php');
				ClientSettings::setFilterOrderPosten();
			break;
			case "iterateText":
				$id = (int) $_POST["id"];
				$direction = $_POST["direction"];
				$current = (int) $_POST["current"];
				/* adapting to array index */
				$current--;

				$type = $_POST["type"];
				$text = $_POST["text"];

				if ($direction == "next") {
					$current++;
				} else if ($direction == "back") {
					$current--;
				}

				if ($current < 0) {
					$current = 0;
				}

				require_once('classes/project/modules/sticker/ChatGPTConnection.php');
				$chatGPTConnection = new ChatGPTConnection($id);
				$text = $chatGPTConnection->getText($type, $text, $current);

				$status = "success";
				if ($text == false) {
					$status = "error";
				}

				echo json_encode([
					"status" => $status,
					"text" => $text,
					"current" => $current,
				]);
			break;
			case "indexAll":
				require_once('classes/project/Search.php');
				Search::indexAll();
			break;
			case "testsearch":
				require_once('classes/project/Search.php');
				Search::search();
			break;
			case "updateImageDescription":
				$id = (int) $_POST["imageId"];
				$description = $_POST["description"];

				$query = "UPDATE module_sticker_image SET `description` = :description WHERE id_datei = :id;";
				DBAccess::updateQuery($query, [
					"description" => $description,
					"id" => $id,
				]);

				echo json_encode([
					"status" => "success",
				]);
			break;
			case "deleteCache":
				require_once('classes/project/CacheManager.php');
				CacheManager::deleteCache();

				echo json_encode([
					"status" => "success",
				]); 
			break;
			case "diagramme":
				require_once('classes/project/Statistics.php');
				Statistics::dispatcher();
			break;
			case "sendTimeTracking":
				require_once('classes/project/TimeTracking.php');
				TimeTracking::addEntry();
			break;
			case "getTimeTables":
				require_once('classes/project/TimeTracking.php');
				$idUser = $_SESSION['userid'];
				$timeTables = TimeTracking::getTimeTables((int) $idUser);

				echo json_encode([
					"status" => "success",
					"timeTables" => $timeTables,
				]);
			break;
			default:
				$selectQuery = "SELECT id, articleUrl, pageName FROM articles WHERE src = '$page'";
				$result = DBAccess::selectQuery($selectQuery);
		
				if ($result == null) {
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
