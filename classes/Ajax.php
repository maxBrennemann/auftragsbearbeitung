<?php

namespace Classes;

use Classes\Controller\SessionController;
use Classes\Project\Icon;
use Classes\Project\OrderHistory;
use Classes\Project\Statistics;
use Classes\Project\Step;
use Classes\Project\Table;
use Classes\Project\User;
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
use Classes\Routes\TableRoutes;
use Classes\Routes\TestingRoutes;
use Classes\Routes\TimeTrackingRoutes;
use Classes\Routes\UploadRoutes;
use Classes\Routes\UserRoutes;
use Classes\Routes\VariousRoutes;
use Classes\Sticker\AufkleberWandtattoo;
use Classes\Sticker\SearchProducts;
use Classes\Sticker\Sticker;
use Classes\Sticker\StickerCategory;
use Classes\Sticker\StickerCollection;
use Classes\Sticker\StickerImage;
use Classes\Sticker\StickerTagManager;
use Classes\Sticker\Textil;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Ajax
{
    public static function handleRequests(): void
    {
        $currentApiVersion = "v1";
        ResourceManager::outputHeaderJSON();

        $url = $_SERVER["REQUEST_URI"];
        $url = explode('?', $url, 2);
        $apiPath = str_replace($_ENV["REWRITE_BASE"] . $_ENV["SUB_URL"] . "/", "", $url[0]);
        $apiParts = explode("/", $apiPath);
        $apiVersion = $apiParts[2];
        $routeType = $apiParts[3];

        if ($currentApiVersion != $apiVersion) {
            JSONResponseHandler::throwError(404, "api version not supported");
        }

        $path = str_replace("api/" . $apiVersion . "/", "", $apiPath);

        /* TODO: implement better auth */
        if (!SessionController::isLoggedIn() && $routeType != "auth") {
            ResourceManager::outputHeaderJSON();
            JSONResponseHandler::throwError(401, "Unauthorized API access");
        }

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
            case "upload":
                UploadRoutes::handleRequest($path);
                break;
            case "user":
                UserRoutes::handleRequest($path);
                break;
            case "template":
                VariousRoutes::handleRequest($path);
                break;
            default:
                JSONResponseHandler::throwError(404, "Path not found");
        }
    }

    public static function manageRequests(string $reason, string $page): void
    {
        switch ($reason) {
            case "delete":
                /* using new table functionality */
                $type = $_POST['type'];
                Table::updateValue($type . "_table", "delete", $_POST['key']);

                /* when a step is deleted, its connection to the notification manager must be deleted and it must be shown in the order histor */
                if ($type == "schritte") {
                    $postennummer = Table::getIdentifierValue("schritte_table", $_POST['key']);
                    $bezeichnung = Table::getValueByIdentifierColumn("schritte_table", $_POST['key'], "Bezeichnung");

                    OrderHistory::add($_POST['auftrag'], $postennummer, OrderHistory::TYPE_STEP, OrderHistory::STATE_DELETED, $bezeichnung);

                    $query = "UPDATE user_notifications SET ischecked = 1 WHERE specific_id = $postennummer";
                    DBAccess::updateQuery($query);
                } elseif ($type == "posten") {
                    $postennummer = Table::getIdentifierValue("posten_table", $_POST['key']);
                    $beschreibung = Table::getValueByIdentifierColumn("posten_table", $_POST['key'], "Beschreibung");

                    OrderHistory::add($_POST['auftrag'], $postennummer, OrderHistory::TYPE_ITEM, OrderHistory::STATE_DELETED, $beschreibung);
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
            case "writeProductDescription":
                Sticker::setDescription();
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
            case "showSearch":
                $id = (int) $_POST["id"];
                $type = $_POST["type"];

                $products = DBAccess::selectQuery("SELECT a.id_product_reference, a.`title` as `name` FROM module_sticker_accessoires a WHERE a.id_sticker = :idSticker AND a.`type` = :type;", [
                    "idSticker" => $id,
                    "type" => $type,
                ]);

                echo \Classes\Controller\TemplateController::getTemplate("sticker/showSearch", [
                    "products" => $products
                ]);
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
        }
    }
}
