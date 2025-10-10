<?php

namespace Src\Classes;

use Src\Classes\Controller\SessionController;
use Src\Classes\Project\Statistics;
use Src\Classes\Routes\CustomerRoutes;
use Src\Classes\Routes\InvoiceRoutes;
use Src\Classes\Routes\LoginRoutes;
use Src\Classes\Routes\NotesRoutes;
use Src\Classes\Routes\NotificationRoutes;
use Src\Classes\Routes\OrderItemRoutes;
use Src\Classes\Routes\OrderRoutes;
use Src\Classes\Routes\ProductRoutes;
use Src\Classes\Routes\SearchRoutes;
use Src\Classes\Routes\SettingsRoutes;
use Src\Classes\Routes\StickerRoutes;
use Src\Classes\Routes\TableRoutes;
use Src\Classes\Routes\TestingRoutes;
use Src\Classes\Routes\TimeTrackingRoutes;
use Src\Classes\Routes\UploadRoutes;
use Src\Classes\Routes\UserRoutes;
use Src\Classes\Routes\VariousRoutes;
use Src\Classes\Sticker\SearchProducts;
use Src\Classes\Sticker\StickerCategory;
use Src\Classes\Sticker\StickerCollection;
use Src\Classes\Sticker\StickerImage;
use Src\Classes\Sticker\StickerTagManager;
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
            case "getManual":
                $pageName = $_POST['pageName'];
                $intent = $_POST['intent'];
                $data = DBAccess::selectQuery("SELECT info FROM `manual` WHERE `page` = '$pageName' AND intent = '$intent'");
                echo json_encode($data, JSON_FORCE_OBJECT);
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
            case "setPriceclass":
                $priceclass = (int) $_POST["priceclass"];
                $id = (int) $_POST["id"];

                DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET price_class = :priceClass WHERE id = :id", ["priceClass" => $priceclass, "id" => $id]);
                echo "ok";
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
            case "showSearch":
                $id = (int) $_POST["id"];
                $type = $_POST["type"];

                $products = DBAccess::selectQuery("SELECT a.id_product_reference, a.`title` as `name` FROM module_sticker_accessoires a WHERE a.id_sticker = :idSticker AND a.`type` = :type;", [
                    "idSticker" => $id,
                    "type" => $type,
                ]);

                echo \Src\Classes\Controller\TemplateController::getTemplate("sticker/showSearch", [
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
            case "diagramme":
                Statistics::dispatcher();
                break;
        }
    }
}
