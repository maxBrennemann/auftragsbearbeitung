<?php

namespace Src\Classes\Sticker;

use Src\Classes\Link;
use Src\Classes\Project\Icon;
use Src\Classes\Project\UploadHandler;
use Src\Classes\Sticker\Imports\ImportGoogleSearchConsole;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

/**
 * @implements \Iterator<int, Sticker>
 */
class StickerCollection implements \Iterator
{
    /** @var array{0:Aufkleber, 1:Wandtattoo, 2:Textil} */
    private array $products;
    private int $current = 0;
    private int $position = 0;

    /** @var array<string, string> */
    private $exports = [];

    private int $id;
    private Sticker $sticker;

    /** @var array<string, mixed> */
    private $productMatches;
    private String $displayError = "";

    public function __construct(int $id)
    {
        $this->id = $id;
        $this->sticker = new Sticker($id);

        $this->products[0] = new Aufkleber($this->id);
        $this->products[1] = new Wandtattoo($this->id);
        $this->products[2] = new Textil($this->id);
    }

    public function getName(): string
    {
        return $this->sticker->getName();
    }

    public function getCreationDate(): string
    {
        return $this->sticker->getCreationDate();
    }

    public function getDirectory(): string
    {
        return $this->sticker->getDirectory();
    }

    public function getIsMarked(): bool
    {
        return $this->sticker->getIsMarked();
    }

    public function getIsRevised(): bool
    {
        return $this->sticker->getIsRevised();
    }

    public function getAdditionalInfo(): string
    {
        return $this->sticker->getAdditionalInfo();
    }

    public function getExportStatus(string $export): bool
    {
        if ($this->exports == []) {
            $query = "SELECT * FROM module_sticker_exports WHERE `idSticker`= :idSticker";
            $data = DBAccess::selectQuery($query, ["idSticker" => $this->id]);

            $this->exports = $data[0];
        }

        return $this->exports[$export] != null;
    }

    public function current(): Sticker
    {
        return $this->products[$this->current];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->products[$this->position]);
    }

    public function createAll(): void
    {
        $this->getAufkleber();
        $this->getWandtattoo();
        $this->getTextil();
    }

    public function getAufkleber(): Aufkleber
    {
        return $this->products[0];
    }

    public function getWandtattoo(): Wandtattoo
    {
        return $this->products[1];
    }

    public function getTextil(): Textil
    {
        return $this->products[2];
    }

    public function getTarget(string $type): Sticker
    {
        return match ($type) {
            "aufkleber" => $this->getAufkleber(),
            "wandtattoo" => $this->getWandtattoo(),
            "textil"     => $this->getTextil(),
            default      => throw new \InvalidArgumentException("Unknown type $type"),
        };
    }

    public function toggleActiveStatus(): void
    {
        $type = (string) $_POST["type"];

        $target = $this->getTarget($type);
        $target->toggleActiveStatus();

        echo json_encode([
            "status" => "success",
            "icon" => $target->getActiveStatus(),
        ]);
    }

    /**
     * updates or uploads all products and writes connections
     * @param array{aufkleber:bool, wandtattoo:bool, textil:bool} $overwriteImages
     */
    public function uploadAll(array $overwriteImages): void
    {
        $this->getAufkleber()->save($overwriteImages["aufkleber"]);
        $this->getWandtattoo()->save($overwriteImages["wandtattoo"]);
        $this->getTextil()->save($overwriteImages["textil"]);
    }

    /**
     * is called via AJAX to reduce page load
     */
    public function checkProductErrorStatus(): string
    {
        $this->productMatches = SearchProducts::getProductsByStickerId($this->id);

        if ($this->productMatches == null) {
            $this->displayError = "noConnection";
            return $this->displayError;
        }

        if (count($this->productMatches["allLinks"]) > 3) {
            $this->displayError = "tooManyProducts";
        }

        $matchesJson = json_encode($this->productMatches, JSON_UNESCAPED_UNICODE);
        DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET additional_data = :matchesJSON WHERE id = :idSticker", [
            "matchesJSON" => $matchesJson,
            "idSticker" => $this->id,
        ]);

        return $this->displayError;
    }

    /**
     * generates an error message in html
     */
    public function getErrorMessage(): ?string
    {
        $text = "";

        switch ($this->displayError) {
            case "tooManyProducts":
                $text = '<div class="defCont warning"><div class="warningHead">' . Icon::getDefault("iconWarning") . '<span>Es wurden mehr als drei Produkte zu diesem Motiv gefunden!</span></div>';

                $count = 1;
                foreach ($this->productMatches["allLinks"] as $l) {
                    $text .= '<a target="_blank" href="' . $l . '">Produkt ' . $count++ . '</a>';
                }

                $text .= "</div>";
                break;
            case "noConnection":
                $text = "Es konnte keine Verbindung zum Shop hergestellt werden.";
                break;
            default:
                $text = "";
        }

        return $text;
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @return array<int, mixed>
     */
    public function getSearchConsoleStats(string $startDate, string $endDate): array
    {
        $url = "";
        $data = ImportGoogleSearchConsole::get($url, $startDate, $endDate);
        return $data;
    }

    public static function getStickerStatus(): void
    {
        $id = (int) Tools::get("id");

        $sc = new StickerCollection($id);
        $errorStatus = $sc->checkProductErrorStatus();
        $errorData = $sc->getErrorMessage();

        JSONResponseHandler::sendResponse([
            "errorStatus" => $errorStatus,
            "errorData" => $errorData,
        ]);
    }

    public static function addStickerCron(): void
    {
        $id = (int) Tools::get("id");
        $type = Tools::get("stickerType");
        $overwrite = json_decode(Tools::get("overwrite"), true);

        $query = "INSERT INTO task_executions (job_name, `status`, started_at, metadata) VALUES (:jobName, :status, :startedAt, :metadata)";
        DBAccess::insertQuery($query, [
            "jobName" => "export_$type",
            "status" => "scheduled",
            "startedAt" => date("Y-m-d h:i:s"),
            "metadata" => json_encode([
                "stickerId" => $id,
                "type" => $type,
                "overwrite" => $overwrite[$type],
            ]),
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    /**
     * @param int $id
     * @param string $type
     * @param bool $overwrite
     * @return array{responseData: array<bool|string>, status: string}
     */
    public static function exportSticker(int $id, string $type, bool $overwrite): array
    {
        $message = "";
        $responseData = [];

        ob_start();

        switch ($type) {
            case "sticker":
                $aufkleber = new Aufkleber($id);
                $aufkleber->save($overwrite);
                break;
            case "walldecal":
                $wandtattoo = new Wandtattoo($id);
                $wandtattoo->save($overwrite);
                break;
            case "textile":
                $textil = new Textil($id);
                $textil->save($overwrite);
                break;
            case "all":
                $stickerCollection = new StickerCollection($id);
                $stickerCollection->uploadAll([
                    "aufkleber" => $overwrite,
                    "wandtattoo" => $overwrite,
                    "textil" => $overwrite
                ]);
                break;
        }

        $responseData["output"] = ob_get_clean();

        return [
            "status" => "success",
            "responseData" => $responseData,
        ];

        /* search for new stickers */
        /*$stickerSearch = SearchProducts::getProductsByStickerId($id);
        $responseData["search"] = $stickerSearch;
        if ($stickerSearch == null) {
            $message = "no new sticker found";
        } else {
            $matchesJson = json_encode($stickerSearch, JSON_UNESCAPED_UNICODE);
            DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET additional_data = :matchesJSON WHERE id = :idSticker", [
                "matchesJSON" => $matchesJson,
                "idSticker" => $id,
            ]);
        }

        if ($message == "") {
            return [
                "status" => "success",
                "responseData" => $responseData,
            ];
        } else {
            return [
                "status" => "error",
                "responseData" => $responseData,
            ];
        }*/
    }

    public static function addSticker(): void
    {
        $name = (string) Tools::get("name");
        Sticker::createNewSticker($name);
    }

    public static function getStickerOverview(): void
    {
        $orderBy = (string) Tools::get("orderBy");
        $order = (string) Tools::get("order");
        $order = $order == "asc" ? "ASC" : "DESC";

        $columns = [
            "id",
            "name",
            "directory_name",
            "is_plotted",
            "is_short_time",
            "is_long_time",
            "is_multipart",
            "is_walldecal",
            "is_shirtcollection",
            "is_revised",
            "is_marked",
        ];

        if (!in_array($orderBy, $columns)) {
            $orderBy = "id";
            $order = "ASC";
        }

        $query = "SELECT id, `name`, directory_name, 
                IF(is_plotted = 1, '✓', 'X') AS is_plotted, 
                IF(is_short_time = 1, '✓', 'X') AS is_short_time, 
                IF(is_long_time = 1, '✓', 'X') AS is_long_time, 
                IF(is_multipart = 1, '✓', 'X') AS is_multipart, 
                IF(is_walldecal = 1, '✓', 'X') AS is_walldecal, 
                IF(is_shirtcollection = 1, '✓', 'X') AS is_shirtcollection, 
                IF(is_revised = 1, '✓', '') AS is_revised, 
                IF(is_marked = 1, '★', '') AS is_marked
            FROM `module_sticker_sticker_data` ORDER BY $orderBy $order";
        $data = DBAccess::selectQuery($query);
        JSONResponseHandler::sendResponse($data);
    }

    public static function getStickerStates(): void
    {
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

            if (($additionalData["products"])) {
                $products = $additionalData["products"];

                if (isset($products["aufkleber"]) && isset($products["aufkleber"]["id"])) {
                    $isInShopStatus[$id]["a"] = $products["aufkleber"]["id"];
                }
                if (isset($products["wandtattoo"]) && isset($products["wandtattoo"]["id"])) {
                    $isInShopStatus[$id]["w"] = $products["wandtattoo"]["id"];
                }
                if (isset($products["textil"]) && isset($products["textil"]["id"])) {
                    $isInShopStatus[$id]["t"] = $products["textil"]["id"];
                }
            }
        }

        JSONResponseHandler::sendResponse($isInShopStatus);
    }

    public static function getStickerSizes(): void
    {
        $id = (int) Tools::get("id");
        $stickerCollection = new StickerCollection($id);
        $aufkleber = $stickerCollection->getAufkleber();
        $sizes = $aufkleber->getSizes();

        JSONResponseHandler::sendResponse([
            "sizes" => $sizes,
        ]);
    }

    public static function getPriceScheme(): void
    {
        $id = (int) Tools::get("id");
        $stickerCollection = new StickerCollection($id);
        $aufkleber = $stickerCollection->getAufkleber();
        $priceClass = $aufkleber->getPriceClass();
        $priceClass = $priceClass == 0 ? "price1" : "price2";

        JSONResponseHandler::sendResponse([
            "priceScheme" => $priceClass,
        ]);
    }

    public static function addFiles(): void
    {
        $idSticker = Tools::get("id");
        $type = Tools::get("type");

        $uploadHandler = new UploadHandler("default", [
            "image/png",
            "image/jpg",
            "image/jpeg",
            "image/svg+xml",
        ], 250000000);
        $files = $uploadHandler->uploadMultiple();

        $query = "INSERT INTO module_sticker_image (id_datei, id_motiv, image_sort) VALUES ";
        $data = [];
        $fileData = [];
        foreach ($files as $file) {
            /* data for db insertion */
            $data[] = [
                $file["id"],
                $idSticker,
                $type
            ];
            /* response to frontend */
            $fileData[] = [
                "id" => $file["id"],
                "link" => Link::getResourcesShortLink($file["saved_name"], "upload"),
            ];
        }

        DBAccess::insertMultiple($query, $data);
        JSONResponseHandler::sendResponse($fileData);
    }

    public static function setTitle(): void
    {
        $id = Tools::get("id");
        $title = Tools::get("title");

        $sticker = new Sticker($id);
        $response = $sticker->setName($title);

        if ($response["status"] == "success") {
            JSONResponseHandler::returnOK();
        } else {
            JSONResponseHandler::throwError(400, "An unspecified error occured.");
        }
    }

    public static function setAltTitle(): void
    {
        $id = Tools::get("id");
        $type = Tools::get("type");
        $newTitle = Tools::get("title");

        $additionalData = DBAccess::selectQuery("SELECT additional_data FROM module_sticker_sticker_data WHERE id = :id LIMIT 1", ["id" => $id]);

        if (!$additionalData[0] == null) {
            $additionalData = json_decode($additionalData[0]["additional_data"], true);

            $additionalData["products"][$type]["altTitle"] = $newTitle;
        } else {
            $additionalData = [];
            $additionalData["products"][$type]["altTitle"] = $newTitle;
        }

        $data = json_encode($additionalData);
        DBAccess::insertQuery("UPDATE module_sticker_sticker_data SET additional_data = :data WHERE id = :id", [
            "data" => $data,
            "id" => $id
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function setCreationDate(): void
    {
        $id = Tools::get("id");
        $creation_date = Tools::get("date");

        DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET creation_date = :creation_date WHERE id = :id", [
            "creation_date" => $creation_date,
            "id" => $id
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function writeDirectory(): void
    {
        $id = Tools::get("id");
        $content = (string) Tools::get("directory");
        $content = urldecode($content);

        $query = "UPDATE module_sticker_sticker_data SET directory_name = :content WHERE id = :id;";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "content" => $content
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function writeAdditonalInfo(): void
    {
        $id = Tools::get("id");
        $content = (string) Tools::get("content");

        $query = "UPDATE module_sticker_sticker_data SET additional_info = :content WHERE id = :id;";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "content" => $content
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function setExportStatus(): void
    {
        $id = Tools::get("id");
        $type = (string) Tools::get("type");
        $types = [
            "facebook",
            "google",
            "amazon",
            "etsy",
            "eBay",
            "pinterest",
        ];

        if (!in_array($type, $types)) {
            JSONResponseHandler::throwError(404, "Unsupported export type.");
        }

        $export = DBAccess::selectQuery("SELECT `$type` FROM module_sticker_exports WHERE idSticker = :idSticker LIMIT 1", [
            "idSticker" => $id,
        ]);

        if ($export[0][$type] == null) {
            $query = "UPDATE module_sticker_exports SET `$type` = -1 WHERE idSticker = :idSticker";
            DBAccess::updateQuery(
                $query,
                [
                    "idSticker" => $id,
                ]
            );
        } elseif ($export[0][$type] != null) {
            $query = "UPDATE module_sticker_exports SET `$type` = NULL WHERE idSticker = :idSticker";
            DBAccess::updateQuery($query, [
                "idSticker" => $id,
            ]);
        }

        JSONResponseHandler::returnOK();
    }

    public static function toggleStatus(): void
    {
        $id = Tools::get("id");
        $type = (string) Tools::get("type");
        $types = [
            "is_plotted",
            "is_short_time",
            "is_long_time",
            "is_walldecal",
            "is_multipart",
            "is_shirtcollection",
            "is_colorable",
            "is_customizable",
            "is_for_configurator",
            "is_revised",
            "is_marked",
        ];

        if (!in_array($type, $types)) {
            JSONResponseHandler::throwError(404, "Cannot change unsupported type.");
        }

        DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `$type` = NOT `$type` WHERE id = :id", [
            "id" => $id
        ]);

        JSONResponseHandler::returnOK();
    }
}
