<?php

namespace Classes;

use Classes\Routes\CustomerRoutes;
use Classes\Routes\InvoiceRoutes;
use Classes\Routes\LoginRoutes;
use Classes\Routes\NotesRoutes;
use Classes\Routes\NotificationRoutes;
use Classes\Routes\OrderItemRoutes;
use Classes\Routes\OrderRoutes;
use Classes\Routes\ProductRoutes;
use Classes\Routes\SearchRoutes;
use Classes\Routes\SettingsRoutes;
use Classes\Routes\StickerRoutes;
use Classes\Routes\TimeTrackingRoutes;

use Upgrade\UpgradeManager;

use Classes\Project\FormGenerator;
use Classes\Project\Search;
use Classes\Project\Liste;
use Classes\Project\NotificationManager;
use Classes\Project\Auftrag;
use Classes\Project\Zeit;
use Classes\Project\Posten;
use Classes\Project\Kunde;
use Classes\Project\Fahrzeug;
use Classes\Project\Schritt;
use Classes\Project\Table;
use Classes\Project\Rechnung;
use Classes\Project\Auftragsverlauf;
use Classes\Project\Angebot;
use Classes\Project\Leistung;
use Classes\Project\Address;
use Classes\Project\ClientSettings;
use Classes\Project\Config;
use Classes\Project\CacheManager;
use Classes\Project\TimeTracking;
use Classes\Project\Statistics;
use Classes\Project\Icon;
use Classes\Project\User;

use Classes\Project\Modules\Sticker\Sticker;
use Classes\Project\Modules\Sticker\StickerImage;
use Classes\Project\Modules\Sticker\StickerCategory;
use Classes\Project\Modules\Sticker\StickerCollection;
use Classes\Project\Modules\Sticker\SearchProducts;
use Classes\Project\Modules\Sticker\Textil;
use Classes\Project\Modules\Sticker\Aufkleber;
use Classes\Project\Modules\Sticker\AufkleberWandtattoo;
use Classes\Project\Modules\Sticker\StickerTagManager;
use Classes\Project\Modules\Sticker\ProductCrawler;

use Classes\Project\Modules\Sticker\Exports\ExportFacebook;

class Ajax
{

	public static function handleRequests()
	{
		$currentApiVersion = "v1";
		ResourceManager::outputHeaderJSON();

		$url = $_SERVER['REQUEST_URI'];
		$url = explode('?', $url, 2);
		$apiPath = str_replace($_ENV["REWRITE_BASE"] . $_ENV["SUB_URL"] . "/", "", $url[0]);
		$apiParts = explode("/", $apiPath);
		$apiVersion = $apiParts[2];
		$routeType = $apiParts[3];

		if ($currentApiVersion != $apiVersion) {
			JSONResponseHandler::throwError(404, "api version not supported");
		}

		$path = str_replace("api/" . $apiVersion . "/", "", $apiPath);

		switch ($routeType) {
			case "customer":
				CustomerRoutes::handleRequest($path);
				break;
			case "invoice":
				InvoiceRoutes::handleRequest($path);
				break;
			case "login":
				LoginRoutes::handleRequest($path);
				break;
			case "notes":
				NotesRoutes::handleRequest($path);
				break;
			case "notification":
				NotificationRoutes::handleRequest($path);
				break;
			case "order-item":
				OrderItemRoutes::handleRequest($path);
				break;
			case "order":
				OrderRoutes::handleRequest($path);
				break;
			case "product":
			case "attribute":
			case "category":
				ProductRoutes::handleRequest($path);
				break;
			case "search":
				SearchRoutes::handleRequest($path);
				break;
			case "settings":
				SettingsRoutes::handleRequest($path);
				break;
			case "sticker":
				StickerRoutes::handleRequest($path);
				break;
			case "time-tracking":
				TimeTrackingRoutes::handleRequest($path);
				break;
			default:
				JSONResponseHandler::throwError(404, "Path not found");
				break;
		}
	}

	public static function manageRequests($reason, $page)
	{
		switch ($reason) {
			case "createTable":
				$type = $_POST['type'];

				if (strcmp($type, "custom") == 0) {
					$query = "SELECT posten.Postennummer, leistung.Bezeichnung, leistung_posten.Beschreibung, leistung_posten.SpeziefischerPreis, zeit.ZeitInMinuten, zeit.Stundenlohn 
						FROM posten 
						INNER JOIN leistung_posten 
							ON posten.Postennummer = leistung_posten.Postennummer 
						INNER JOIN zeit 
							ON posten.Postennummer = zeit.Postennummer 
						INNER JOIN leistung 
							ON leistung_posten.Leistungsnummer = leistung.Nummer 
						WHERE istStandard = 1;";
					$data = DBAccess::selectQuery($query);
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

					$table = FormGenerator::createTable($type, true, $showData, $sendTo);
					echo $table;
				}
				break;
			case "search":
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
				$query = $_POST['query'];
				echo Search::getSearchTable($query, "produkt");
				break;
			case "globalSearch":
				$query = $_POST['query'];
				echo Search::globalSearch($query);
				break;
			case "saveList":
				$data = $_POST['data'];
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

				Liste::storeListData($listid, $listname, $listtype, $listvalue, $orderId);

				echo "success";
				break;
			case "notification":
				echo NotificationManager::htmlNotification();
				break;
			case "updateNotification":
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

				Tools::add("id", $auftragsId);
				Tools::add("vehicleId", $fahrzeugId);

				Fahrzeug::attachVehicle();
				echo (new Auftrag($auftragsId))->getFahrzeuge();
				break;
			case "insertStep":
				$data = array();
				$data['Bezeichnung'] = $_POST['bez'];
				$data['Datum'] = $_POST['datum'];
				$data['Priority'] = $_POST['prio'];
				$data['Auftragsnummer'] = $_POST['auftrag'];
				$data['hide'] = $_POST['hide'];

				$postenNummer = Schritt::insertStep($data);
				$auftrag = new Auftrag($data['Auftragsnummer']);
				echo $auftrag->getOpenBearbeitungsschritteTable();

				$assignedTo = strval($_POST['assignedTo']);
				if (strcmp($assignedTo, "none") != 0) {
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
					$table = unserialize($_SESSION['storedTable']);
					$auftragsId = $_POST['auftrag'];
					$row = $_POST['row'];
					$table->setIdentifier("Schrittnummer");
					$date = date("Y-m-d");
					$table->addParam("finishingDate", $date);
					$table->editRow($row, "istErledigt", "0");
				} else {
					$rechnung = $_POST['rechnung'];
					DBAccess::updateQuery("UPDATE auftrag SET Bezahlt = 1 WHERE Auftragsnummer = $rechnung");
					echo Rechnung::getOffeneRechnungen();
				}
				break;
			case "delete":
				/* using new table functionality */
				$type = $_POST['type'];
				Table::updateValue($type . "_table", "delete", $_POST['key']);

				/* when a step is deleted, its connection to the notification manager must be deleted and it must be shown in the order histor */
				if ($type == "schritte") {
					$postennummer = Table::getIdentifierValue("schritte_table", $_POST['key']);
					$bezeichnung = Table::getValueByIdentifierColumn("schritte_table", $_POST['key'], "Bezeichnung");

					$auftragsverlauf = new Auftragsverlauf($_POST['auftrag']);
					$auftragsverlauf->addToHistory($postennummer, 2, "deleted", $bezeichnung);

					$query = "UPDATE user_notifications SET ischecked = 1 WHERE specific_id = $postennummer";
					DBAccess::updateQuery($query);
				} else if ($type == "posten") {
					$postennummer = Table::getIdentifierValue("posten_table", $_POST['key']);
					$beschreibung = Table::getValueByIdentifierColumn("posten_table", $_POST['key'], "Beschreibung");

					$auftragsverlauf = new Auftragsverlauf($_POST['auftrag']);
					$auftragsverlauf->addToHistory($postennummer, 1, "deleted", $beschreibung);
				}
				break;
			case "update":
				/* using new table functionality */
				Table::updateValue("schritte_table", "update", $_POST['key']);
				/* adds an update step to the history by using orderId and identifier */
				$postennummer = Table::getIdentifierValue("schritte_table", $_POST['key']);
				Schritt::updateStep([
					"orderId" => $_POST['auftrag'],
					"postennummer" => $postennummer
				]);

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
			case "deleteSize":
				$key = $_POST["key"];
				$table = $_POST["table"];
				Table::updateValue($table, "delete", $_POST['key']);
				break;
			case "getServerMsg":
				echo $_SESSION['searchResult'];
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
				echo json_encode(Address::loadAllAddresses($kdnr));
				break;
			case "setOrderFinished":
				$auftrag = $_POST['auftrag'];
				DBAccess::insertQuery("UPDATE auftrag SET archiviert = -1 WHERE Auftragsnummer = $auftrag");
				break;
			case "existingColors":
				$auftrag = $_POST['auftrag'];
				$ids = json_decode($_POST['ids'], true);

				foreach ($ids as $id) {
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
				$customerId = $_POST['customerId'];
				$angebot = new Angebot($customerId);
				echo $angebot->getHTMLTemplate();
				break;
			case 'loadCachedPosten':
				$customerId = $_POST['customerId'];
				$angebot = new Angebot($customerId);
				echo $angebot->loadCachedPosten();
				break;
			case "getAddresses":
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
			case "getList":
				$lid = $_POST['listId'];
				$list = Liste::readList($lid);
				echo $list->toHTML();
				break;
			case "generateInvoicePDF":
				if (isset($_SESSION['tempInvoice'])) {
					$rechnung = unserialize($_SESSION['tempInvoice']);
					$rechnung->PDFgenerieren(true);
				}
				break;
			case "reloadPostenListe":
				$auftragsId = $_POST['id'];
				$auftrag = new Auftrag($auftragsId);

				$data = [
					0 => $auftrag->getAuftragspostenAsTable(),
					1 => $auftrag->getInvoicePostenTable()
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

				$key = $_POST['key'];
				$data = $_POST['data'];
				echo Table::updateTable_AddNewLine($key, $data);
				break;
			case "setCustomColor":
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
			case "sendToDB":
				$title = $_POST['title'];
				$content = $_POST['content'];
				DBAccess::insertQuery("INSERT INTO wiki_articles (title, content) VALUES ('$title', '$content')");
				break;
			case "updateDefaultWage":
				$defaultWage = $_POST["defaultWage"];
				Config::set("defaultWage", $defaultWage);
				echo json_encode([]);
				break;
			case "getManual":
				$pageName = $_POST['pageName'];
				$intent = $_POST['intent'];
				$data = DBAccess::selectQuery("SELECT info FROM `manual` WHERE `page` = '$pageName' AND intent = '$intent'");
				echo json_encode($data, JSON_FORCE_OBJECT);
				break;
			case "setDateInvoice":
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
				$id = (int) $_POST['id'];
				$text = $_POST['text'];

				$rechnung = unserialize($_SESSION['tempInvoice']);
				$rechnung->addText($id, $text);
				$_SESSION['tempInvoice'] = serialize($rechnung);
				break;
			case "invoiceRemoveText":
				$id = (int) $_POST['id'];

				$rechnung = unserialize($_SESSION['tempInvoice']);
				$rechnung->removeText($id);
				$_SESSION['tempInvoice'] = serialize($rechnung);
				break;
			case "setInvoiceParameters":

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
				$status = (string) $_POST["status"];
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
					NotificationManager::setNotificationsRead(-1);
				} else {
				}
				break;
			case "getBackup":
				$result = DBAccess::EXPORT_DATABASE($_ENV["HOST"], $_ENV["USERNAME"], $_ENV["PASSWORD"], $_ENV["DATABASE"]);
				$filePath = "files/generated/sql_backups/";
				$fileName = date("d-m-Y_h-i-s") . ".sql";
				file_put_contents(($filePath . $fileName), $result);

				$data = array("fileName" => $fileName, "url" => Link::getResourcesShortLink($fileName, "backup"), "status" => "ok");
				echo json_encode($data, JSON_FORCE_OBJECT);
				break;
			case "logout":
				Login::handleLogout();
				break;
			case "minifyFiles":
				MinifyFiles::minify();
				echo json_encode(["status" => "success"]);
				break;
			case "upgrade":
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
				Sticker::setDescription();
				break;
			case "writeSpeicherort":
				$id = (int) $_POST["id"];
				$content = (string) $_POST["content"];
				$content = urldecode($content);

				$query = "UPDATE module_sticker_sticker_data SET directory_name = :content WHERE id = :id;";
				DBAccess::updateQuery($query, ["id" => $id, "content" => $content]);
				echo "success";
				break;
			case "writeAdditionalInfo":
				$id = (int) $_POST["id"];
				$content = (string) $_POST["content"];

				$query = "UPDATE module_sticker_sticker_data SET additional_info = '$content' WHERE id = $id;";
				DBAccess::updateQuery($query);
				echo "success";
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

				try {
					StickerImage::setImageOrder($order);
					echo json_encode([
						"status" => "success",
					]);
				} catch (\Exception $e) {
					echo json_encode([
						"status" => "error",
						"message" => $e->getMessage(),
					]);
				}
				break;
			case "changeMotivDate":
				$id = (int) $_POST['id'];
				$creation_date = $_POST['date'];

				DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET creation_date = :creation_date WHERE id = :id", ["creation_date" => $creation_date, "id" => $id]);
				echo "success";
				break;
			case "toggleTextil":
				$id = (int) $_POST["id"];
				DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_shirtcollection` = NOT `is_shirtcollection` WHERE id = :id", ["id" => $id]);
				echo json_encode([
					"status" => "success",
				]);
				break;
			case "toggleWandtattoo":
				$id = (int) $_POST["id"];
				DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_walldecal` = NOT `is_walldecal` WHERE id = :id", ["id" => $id]);
				echo json_encode([
					"status" => "success",
				]);
				break;
			case "toggleRevised":
				$id = (int) $_POST["id"];
				DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_revised` = NOT `is_revised` WHERE id = :id", ["id" => $id]);
				echo "success";
				break;
			case "toggleBookmark":
				$id = (int) $_POST["id"];
				DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_marked` = NOT `is_marked` WHERE id = :id", ["id" => $id]);
				echo "success";
				break;
			case "makeSVGColorable":
				$id = (int) $_POST["id"];

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

				$textil = new Textil($id);
				$textil->toggleCustomizable();
				break;
			case "makeForConfig":
				$id = (int) $_POST["id"];

				$textil = new Textil($id);
				$textil->toggleConfig();
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
				$size = DBAccess::selectQuery("SELECT width, height FROM module_sticker_sizes WHERE id = :postennummer LIMIT 1", ["postennummer" => $postenNummer]);
				$width = $size[0]["width"];
				$height = $size[0]["height"];

				$aufkleberWandtatto = new AufkleberWandtattoo($id);
				$difficulty = $aufkleberWandtatto->getDifficulty();
				$price = $aufkleberWandtatto->getPrice($width, $height, $difficulty);

				DBAccess::updateQuery("UPDATE module_sticker_sizes SET price = :price, price_default = 1 WHERE id = :postennummer", [
					"price" => $price,
					"postennummer" => $postenNummer,
				]);
				echo $price;
				break;
			case "setAufkleberParameter":
				$id = (int) $_POST["id"];
				$data = $_POST["json"];
				$aufkleber = new Aufkleber($id);
				$aufkleber->saveSentData($data);
				break;
			case "setAufkleberTitle":
				$id = (int) $_POST["id"];
				$title = (string) $_POST["title"];

				$sticker = new Sticker($id);
				$response = $sticker->setName($title);

				echo $response["status"];
				break;
			case "productVisibility":
				$id = (int) $_POST["id"];
				$stickerCollection = new StickerCollection($id);
				$stickerCollection->toggleActiveStatus();
				break;
			case "getTagOverview":
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
				$title = (string) $_POST["title"];
				$idTagGroup = StickerTagManager::addTagGroup($title);
				echo json_encode([
					"status" => "success",
					"idTagGroup" => $idTagGroup,
				]);
				break;
			case "addNewUser":
				$username = (string) $_POST["username"];
				$password = (string) $_POST["password"];
				$email = (string) $_POST["email"];
				$prename = (string) $_POST["prename"];
				$lastname = (string) $_POST["lastname"];

				User::add($username, $email, $prename, $lastname, $password);

				echo json_encode([
					"status" => "success",
				]);
				break;
			case "crawlAll":
				$pc = new ProductCrawler();
				$pc->crawlAll();
				break;
			case "crawlTags":
				StickerTagManager::crawlAllTags();
				break;
			case "setAltTitle":
				$id = (int) $_POST["id"];
				$newTitle = (string) $_POST["newTitle"];
				$type = (string) $_POST["type"];

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
				Upload::deleteUnusedFiles();
				break;
			case "adjustFiles":
				Upload::adjustFileNames();
				break;
			case "createFbExport":
				$export = new ExportFacebook();
				$export->generateCSV();
				$filename = $export->getFilename();
				$fileLink = Link::getResourcesLink("files/generated/fb_export/" . $filename, "html");

				echo json_encode([
					"status" => "successful",
					"file" => $fileLink,
					"errorList" => "",//$errorList,
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
				$type = (string) $_POST["type"];
				$title = (string) $_POST["title"];
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
				$type = (string) $_POST["type"];

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
				$search = $_POST["query"];
				echo json_encode(SearchProducts::search($search, ["name", "description", "description_short"]));
				break;
			case "getCategoryTree":
				$startCategory = $_POST["categoryId"];
				echo json_encode(StickerCategory::getCategories($startCategory));
				break;
			case "getCategories":
				$id = (int) $_POST["id"];
				echo json_encode(StickerCategory::getCategoriesForSticker($id));
				break;
			case "getCategoriesSuggestion":
				$name = $_POST["name"];
				$id = (int) $_POST["id"];
				echo StickerCategory::getCategoriesSuggestion($name, $id);
				break;
			case "setCategories":
				$id = (int) $_POST["id"];
				$categories = $_POST["categories"];
				StickerCategory::setCategories($id, $categories);
				echo json_encode([
					"status" => "success",
				]);
				break;
			case "setExportStatus":
				$status = (string) $_POST["export"];
				$id = (int) $_POST["id"];

				$export = DBAccess::selectQuery("SELECT * FROM module_sticker_exports WHERE idSticker = :idSticker", ["idSticker" => $id]);
				//Protocol::prettyPrint($export);
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
			case "getIcon":
				$type = (string) $_POST["icon"];
				$icon = "";

				if (isset($_POST["custom"])) {
					$width = (int) $_POST["width"];
					$height = (int) $_POST["height"];

					/* classes from frontend come as commma separated string */
					$classes = (string) $_POST["classes"];
					$classes = explode(",", $classes);

					if (isset($_POST["title"])) {
						$title = (string) $_POST["title"];
					} else {
						$title = "";
					}

					if (isset($_POST["color"])) {
						$color = (string) $_POST["color"];
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
				ClientSettings::setFilterOrderPosten();
				break;
			case "indexAll":
				Search::indexAll();
				break;
			case "testsearch":
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
				CacheManager::deleteCache();

				echo json_encode([
					"status" => "success",
				]);
				break;
			case "diagramme":
				Statistics::dispatcher();
				break;
			case "getTimeTables":
				$idUser = $_SESSION['userid'];
				$timeTables = TimeTracking::getTimeTables((int) $idUser);

				echo json_encode([
					"status" => "success",
					"timeTables" => $timeTables,
				]);
				break;
			default:
				$selectQuery = "SELECT id, articleUrl, pageName FROM articles WHERE src = :page;";
				$result = DBAccess::selectQuery($selectQuery, ["page" => $page]);

				if ($result == null) {
					$baseUrl = 'files/generated/';
					$result = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM generated_articles WHERE src = :page", ["page" => $page]);
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
