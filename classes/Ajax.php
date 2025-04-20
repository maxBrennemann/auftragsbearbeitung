<?php

namespace Classes;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

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
use Classes\Routes\UserRoutes;
use Classes\Routes\VariousRoutes;

use Classes\Project\FormGenerator;
use Classes\Project\Search;
use Classes\Project\Liste;
use Classes\Project\NotificationManager;
use Classes\Project\Auftrag;
use Classes\Project\Posten;
use Classes\Project\Fahrzeug;
use Classes\Project\Step;
use Classes\Project\Table;
use Classes\Project\Auftragsverlauf;
use Classes\Project\Address;
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

use Classes\Routes\TableRoutes;
use Classes\Routes\TestingRoutes;

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
			case "auth":
				LoginRoutes::handleRequest($path);
				break;
			case "notes":
				NotesRoutes::handleRequest($path);
				break;
			case "notification":
				NotificationRoutes::handleRequest($path);
				break;
			case "order-items":
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
			case "test":
				TestingRoutes::handleRequest($path);
				break;
			case "tables":
				TableRoutes::handleRequest($path);
				break;
			case "time-tracking":
				TimeTrackingRoutes::handleRequest($path);
				break;
			case "user":
				UserRoutes::handleRequest($path);
				break;
			case "template":
				VariousRoutes::handleRequest($path);
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

				Posten::insertPosten("compact", $data);
				echo (new Auftrag($_POST['auftrag']))->preisBerechnen();
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

				$postenNummer = Step::insertStep($data);
				$auftrag = new Auftrag($data['Auftragsnummer']);
				echo $auftrag->getOpenBearbeitungsschritteTable();

				$assignedTo = strval($_POST['assignedTo']);
				if (strcmp($assignedTo, "none") != 0) {
					NotificationManager::addNotification($userId = $assignedTo, $type = 1, $content = $_POST['bez'], $specificId = $postenNummer);
				}
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
				Step::updateStep([
					"orderId" => $_POST['auftrag'],
					"postennummer" => $postennummer
				]);

				$user = User::getCurrentUserId();
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
			case "setOrderFinished":
				$auftrag = $_POST['auftrag'];
				DBAccess::insertQuery("UPDATE auftrag SET archiviert = -1 WHERE Auftragsnummer = $auftrag");
				break;
			case "getAddresses":
				$kdnr = (int) $_POST['kdnr'];
				echo json_encode(Address::loadAllAddresses($kdnr));
				break;
			case "getList":
				$lid = $_POST['listId'];
				$list = Liste::readList($lid);
				echo $list->toHTML();
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
			case "getManual":
				$pageName = $_POST['pageName'];
				$intent = $_POST['intent'];
				$data = DBAccess::selectQuery("SELECT info FROM `manual` WHERE `page` = '$pageName' AND intent = '$intent'");
				echo json_encode($data, JSON_FORCE_OBJECT);
				break;
			case "setNotificationsRead":
				$notificationIds = $_POST["notificationIds"];
				if ($notificationIds == "all") {
					NotificationManager::setNotificationsRead(-1);
				} else {
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
			case "showSearch":
				$id = (int) $_POST["id"];
				$type = $_POST["type"];

				$products = DBAccess::selectQuery("SELECT a.id_product_reference, a.`title` as `name` FROM module_sticker_accessoires a WHERE a.id_sticker = :idSticker AND a.`type` = :type;", [
					"idSticker" => $id,
					"type" => $type,
				]);

				insertTemplate('classes/Project/Modules/Sticker/Views/showSearchView.php', ["products" => $products]);
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
				insertTemplate('classes/Project/Modules/Sticker/Views/chatGPTOptionsView.php', [
					"texts" => $result,
				]);
				$content = ob_get_clean();
				echo json_encode([
					"template" => $content,
				]);
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
			case "diagramme":
				Statistics::dispatcher();
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
