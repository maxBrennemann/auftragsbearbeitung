<?php

namespace Classes\Sticker;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

use Classes\Sticker\Imports\ImportGoogleSearchConsole;

use Classes\Project\Icon;
use Classes\Project\UploadHandler;

use Classes\Link;

class StickerCollection implements \Iterator
{

    private $products = [];
    private $current = 0;
    private $position = 0;

    private $exports = [];

    private int $id;
    private Sticker $sticker;

    private $productMatches;
    private String $displayError = "";

    function __construct(int $id)
    {
        $this->id = $id;
        $this->sticker = new Sticker($id);

        $this->products[0] = new Aufkleber($this->id);
        $this->products[1] = new Wandtattoo($this->id);
        $this->products[2] = new Textil($this->id);
    }

    public function getName(): String
    {
        return $this->sticker->getName();
    }

    public function getCreationDate()
    {
        return $this->sticker->getCreationDate();
    }

    public function getDirectory()
    {
        return $this->sticker->getDirectory();
    }

    public function getIsMarked()
    {
        return $this->sticker->getIsMarked();
    }

    public function getIsRevised()
    {
        return $this->sticker->getIsRevised();
    }

    public function getAdditionalInfo()
    {
        return $this->sticker->getAdditionalInfo();
    }

    public function getExportStatus($export): bool
    {
        if ($this->exports == []) {
            $query = "SELECT * FROM module_sticker_exports WHERE `idSticker`= :idSticker";
            $data = DBAccess::selectQuery($query, ["idSticker" => $this->id]);

            $this->exports = $data[0];
        }

        return $this->exports[$export] != null;
    }

    /* Iterator */
    public function current(): mixed
    {
        return $this->getTarget($this->current);
    }

    public function key(): mixed
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

    public function createAll()
    {
        $this->getAufkleber();
        $this->getWandtattoo();
        $this->getTextil();
    }

    public function getAufkleber()
    {
        return $this->products[0];
    }

    public function getWandtattoo()
    {
        return $this->products[1];
    }

    public function getTextil()
    {
        return $this->products[2];
    }

    public function getTarget($type)
    {
        $target = null;
        switch ($type) {
            case "aufkleber":
                $target = $this->getAufkleber();
                break;
            case "wandtattoo":
                $target = $this->getWandtattoo();
                break;
            case "textil":
                $target = $this->getTextil();
                break;
        }

        return $target;
    }

    public function toggleActiveStatus()
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
     *  updates or uploads all products and writes connections 
     */
    public function uploadAll($overwriteImages)
    {
        $this->getAufkleber()->save($overwriteImages["aufkleber"]);
        $this->getWandtattoo()->save($overwriteImages["wandtattoo"]);
        $this->getTextil()->save($overwriteImages["textil"]);
    }

    /**
     * is called via AJAX to reduce page load
     */
    public function checkProductErrorStatus()
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
    public function getErrorMessage(): ?String
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

    public function getSearchConsoleStats($startDate, $endDate)
    {
        $url = "";
        $data = ImportGoogleSearchConsole::get($url, $startDate, $endDate);
        return $data;
    }

    public static function getStickerStatus()
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

    public static function addStickerCron()
    {
        $id = (int) Tools::get("id");
        $type = (int) Tools::get("stickerType");
        $overwrite = json_decode(Tools::get("overwrite"), true);

        $query = "INSERT INTO task_executions (job_name, `status`, started_at, metadata) VALUES (:jobName, :status, :startedAt, :metadata)";
        DBAccess::insertQuery($query, [
            "jobName" => "export_$type",
            "status" => "scheduled",
            "startedAt" => date("Y-m-d h:i:s"),
            "metadata" => json_encode([
                "stickerId" => $id,
                "type" => $type,
                "overwrite" => $overwrite["aufkleber"],
            ]),
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function exportSticker()
    {
        $id = (int) Tools::get("id");
        $type = (int) Tools::get("stickerType");
        $overwrite = json_decode(Tools::get("overwrite"), true);

        $message = "";
        $responseData = [];

        ob_start();

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
                $stickerCollection->uploadAll($overwrite);
                break;
        }

        $responseData["output"] = ob_get_clean();

        /* search for new stickers */
        $stickerSearch = SearchProducts::getProductsByStickerId($id);
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
            JSONResponseHandler::sendResponse([
                "status" => "success",
                "responseData" => $responseData,
            ]);
        } else {
            JSONResponseHandler::throwError(500, json_encode([
                "status" => "error",
                "responseData" => $responseData,
            ]));
        }
    }

    public static function addSticker()
    {
        $name = (string) Tools::get("name");
        Sticker::createNewSticker($name);
    }

    public static function getStickerOverview()
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

    public static function getStickerStates()
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

    public static function getStickerSizes()
    {
        $id = (int) Tools::get("id");
        $stickerCollection = new StickerCollection($id);
        $aufkleber = $stickerCollection->getAufkleber();
        $sizes = $aufkleber->getSizes();

        JSONResponseHandler::sendResponse([
            "sizes" => $sizes,
        ]);
    }

    public static function getPriceScheme()
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

    public static function addFiles()
    {
        $idSticker = Tools::get("id");
        $type = Tools::get("type");

        $uploadHandler = new UploadHandler("upload", [
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

    public static function setTitle()
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

    public static function setAltTitle()
    {
        $id = Tools::get("id");
        $type = Tools::get("type");
        $newTitle = Tools::get("title");

        $additionalData = DBAccess::selectQuery("SELECT additional_data FROM module_sticker_sticker_data WHERE id = :id LIMIT 1", ["id" => $id]);

        if (!$additionalData[0] === NULL) {
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

    public static function setCreationDate()
    {
        $id = Tools::get("id");
        $creation_date = Tools::get("date");

        DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET creation_date = :creation_date WHERE id = :id", [
            "creation_date" => $creation_date,
            "id" => $id
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function writeDirectory()
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

    public static function writeAdditonalInfo()
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

    public static function setExportStatus()
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
            return;
        }

        $export = DBAccess::selectQuery("SELECT `$type` FROM module_sticker_exports WHERE idSticker = :idSticker LIMIT 1", [
            "idSticker" => $id,
        ]);

        if ($export[0][$type] == NULL) {
            $query = "UPDATE module_sticker_exports SET `$type` = -1 WHERE idSticker = :idSticker";
            DBAccess::updateQuery(
                $query,
                [
                    "idSticker" => $id,
                ]
            );
        } else if ($export[0][$type] != NULL) {
            $query = "UPDATE module_sticker_exports SET `$type` = NULL WHERE idSticker = :idSticker";
            DBAccess::updateQuery($query, [
                "idSticker" => $id,
            ]);
        }

        JSONResponseHandler::returnOK();
    }

    public static function toggleStatus()
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
            return;
        }

        DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `$type` = NOT `$type` WHERE id = :id", [
            "id" => $id
        ]);

        JSONResponseHandler::returnOK();
    }
}
