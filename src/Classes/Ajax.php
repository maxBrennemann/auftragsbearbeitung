<?php

namespace Src\Classes;

use Src\Classes\Controller\SessionController;
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
use Src\Classes\Routes\HookRoutes;
use Src\Classes\Sticker\SearchProducts;
use Src\Classes\Sticker\StickerCategory;
use Src\Classes\Sticker\StickerCollection;
use Src\Classes\Sticker\StickerImage;
use Src\Classes\Sticker\StickerTagManager;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Ajax
{
    private static string $apiVersion = "v1";
    /** @var array<string> */
    private static array $publicRoutes = [
        "auth",
    ];
    /** @var array<string> */
    private static array $authTokenRoutes = [
        "hooks",
    ];

    public static function handleRequests(): void
    {
        $response = self::getApiRequest();

        $path = $response["path"];
        $routeType = $response["routeType"];

        if (
            !SessionController::isLoggedIn()
            && !in_array($routeType, self::$publicRoutes)
            && !in_array($routeType, self::$authTokenRoutes)
        ) {
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
            case "project":
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
            case "manual":
            case "stats":
                VariousRoutes::handleRequest($path);
                break;
            case "hooks":
                HookRoutes::handleRequest($path);
                break;
            default:
                JSONResponseHandler::throwError(404, "Path not found");
        }
    }

    /**
     * @return array{path: string, routeType: string}
     */
    private static function getApiRequest(): array
    {
        ResourceManager::outputHeaderJSON();

        $url = $_SERVER["REQUEST_URI"];
        $url = explode('?', $url, 2);
        $apiPath = str_replace($_ENV["REWRITE_BASE"] . "/", "", $url[0]);
        $apiParts = explode("/", $apiPath);

        if (count($apiParts) < 4) {
            JSONResponseHandler::throwError(404, "Path not found");
        }

        $apiVersion = $apiParts[2];
        $routeType = $apiParts[3];

        if (self::$apiVersion != $apiVersion) {
            JSONResponseHandler::throwError(404, "api version not supported");
        }

        $path = str_replace("api/" . $apiVersion . "/", "", $apiPath);

        return [
            "path" => $path,
            "routeType" => $routeType,
        ];
    }

    public static function manageRequests(string $reason, string $page): void
    {
        switch ($reason) {
            case "setImageOrder":
                $order = Tools::get("order");

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
                $priceclass = (int) Tools::get("priceclass");
                $id = (int) Tools::get("id");

                DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET price_class = :priceClass WHERE id = :id", ["priceClass" => $priceclass, "id" => $id]);
                echo "ok";
                break;
            case "productVisibility":
                $id = (int) Tools::get("id");
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
                $title = (string) Tools::get("title");
                $idTagGroup = StickerTagManager::addTagGroup($title);
                echo json_encode([
                    "status" => "success",
                    "idTagGroup" => $idTagGroup,
                ]);
                break;
            case "showSearch":
                $id = (int) Tools::get("id");
                $type = Tools::get("type");

                $products = DBAccess::selectQuery("SELECT a.id_product_reference, a.`title` as `name` FROM module_sticker_accessoires a WHERE a.id_sticker = :idSticker AND a.`type` = :type;", [
                    "idSticker" => $id,
                    "type" => $type,
                ]);

                echo \Src\Classes\Controller\TemplateController::getTemplate("sticker/showSearch", [
                    "products" => $products
                ]);
                break;
            case "connectAccessoire":
                $idSticker = (int)Tools::get("id");
                $idProductReference = (int) Tools::get("articleId");
                $type = (string) Tools::get("type");
                $title = (string) Tools::get("title");
                $status = Tools::get("status") == "true";

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
                $idSticker = (int) Tools::get("id");
                $idProductReference = (int) Tools::get("idProductReference");
                $type = (string) Tools::get("type");

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
                $search = Tools::get("query");
                echo json_encode(SearchProducts::search($search, ["name", "description", "description_short"]));
                break;
            case "getCategoryTree":
                $startCategory = Tools::get("categoryId");
                echo json_encode(StickerCategory::getCategories($startCategory));
                break;
            case "getCategories":
                $id = (int) Tools::get("id");
                echo json_encode(StickerCategory::getCategoriesForSticker($id));
                break;
            case "getCategoriesSuggestion":
                $name = Tools::get("name");
                $id = (int) Tools::get("id");
                echo StickerCategory::getCategoriesSuggestion($name, $id);
                break;
            case "setCategories":
                $id = (int) Tools::get("id");
                $categories = Tools::get("categories");
                StickerCategory::setCategories($id, $categories);
                echo json_encode([
                    "status" => "success",
                ]);
                break;
        }
    }
}
