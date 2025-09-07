<?php

namespace Classes\Sticker\Exports;

use Classes\Sticker\Aufkleber;
use Classes\Sticker\PrestashopConnection;
use Classes\Sticker\StickerCollection;
use Classes\Sticker\Textil;
use Classes\Sticker\Wandtattoo;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

/**
 * generates a csv file for facebook product export
 */
class ExportFacebook extends PrestashopConnection
{
    private $currentFilename;
    private $idProducts;

    /**
     * the line array for the csv
     */
    private $line = [
        "id" => "", // aufkleber wandtattoo textil
        "title" => "", // name
        "description" => "", // description
        "availability" => "In Stock",
        "condition" => "New",
        "price" => "", // aufkleber wandtattoo textil
        "link" => "", // shop link
        "image_link" => "", // first image
        "brand" => "klebefux",
        "item_group_id" => "", // type + id
        "color" => "", // aufkleber textil
        "size" => "", // aufkleber wandtattoo
        "material" => "", // aufkleber wandtattoo
        "shipping_weight" => "0.5kg",
        "google_product_category" => "", // aufkleber wandtattoo textil
    ];

    /**
     * gets all sticker ids from database;
     */
    public function __construct()
    {
        $query = "SELECT `id` FROM `module_sticker_sticker_data` ORDER BY `id` ASC";
        $this->idProducts = DBAccess::selectQuery($query);
    }

    /**
     * iterates over all products and creates the three variants if these exist in the shop
     * then it takes the data and puts it into a file
     */
    public function generateCSV()
    {
        $lines = [];
        foreach ($this->idProducts as $id) {
            $id = (int) $id["id"];

            $productLines = $this->getSpecificProductExport($id);
            $lines = [...$lines, ...$productLines];
        }

        $filename = "exportFB_" . date("Y-m-d") . ".csv";
        $this->currentFilename = $filename;
        $this->generateFile($lines, $filename);
    }

    /**
     * generates the product csv lines for a specific sticker id
     */
    public function getSpecificProductExport($idSticker)
    {
        $stickerCollection = new StickerCollection($idSticker);

        $lines = [];
        if ($stickerCollection->getAufkleber()->isInShop()) {
            $productLines = $this->fillLine($stickerCollection->getAufkleber());
            $lines = [...$lines, ...$productLines];
        }

        if ($stickerCollection->getTextil()->isInShop()) {
            $productLines = $this->fillLine($stickerCollection->getTextil());
            $lines = [...$lines, ...$productLines];
        }

        if ($stickerCollection->getWandtattoo()->isInShop()) {
            $productLines = $this->fillLine($stickerCollection->getWandtattoo());
            $lines = [...$lines, ...$productLines];
        }

        return $lines;
    }

    private function fillLine($product): array
    {
        $variantLines = [];

        $type = $product->getType();
        $line = $this->line;
        $line["item_group_id"] = $type . $product->getId();
        $line["title"] = $product->getName();
        $line["description"] = "Unsere Aufkleber und Textilien sind keine Lagerware. Diese werden nach der Bestellung individuell für Dich angefertigt. " . $product->getDescription();
        $line["link"] = $product->getShopLink();
        $line["image_link"] = self::getFirstImageLink($product);

        if ($line["image_link"] == null) {
            $line["image_link"] = "";
        }

        if ($product instanceof Aufkleber) {
            $variantLines = $this->fillLineAufkleber($product, $line);
        } elseif ($product instanceof Wandtattoo) {
            $variantLines =  $this->fillLineWandtattoo($product, $line);
        } elseif ($product instanceof Textil) {
            $variantLines = $this->fillLineTextil($product, $line);
        }

        return $variantLines;
    }

    private function fillLineAufkleber($product, $line)
    {
        $combinationId = 0;
        $combinationLines = [];
        $sizeIds = $product->getSizeIds();
        $prices = $product->getPricesMatched();
        $basePrice = $product->getBasePriceUnformatted();

        foreach ($sizeIds as $size) {
            if (isset($prices[$size]) && $prices[$size] != 0.00) {
                $line["price"] = $prices[$size] + $basePrice;
            }

            $line["size"] = $product->getSize($size);
            $line["id"] = "aufkleber" . "_" . $product->getId() . "_" . $combinationId;

            if ($product->getIsMultipart() == 1) {
                $combinationId++;
                $combinationLines[] = $line;
                continue;
            }

            /* iterate over all colors and add variants */
            foreach ($product->getColors() as $color) {
                $line["color"] = $product->getColorName($color);

                if ($product->getIsShortTimeSticker() == 1) {
                    $line["material"] = "Werbefolie";
                }

                if ($product->getIsLongTimeSticker() == 1) {
                    $line["material"] = "Hochleistungsfolie";
                }

                $line["id"] = "aufkleber" . "_" . $product->getId() . "_" . $combinationId;

                $line["google_product_category"] = "4054";

                $combinationId++;
                $combinationLines[] = $line;
            }
        }

        return $combinationLines;
    }

    private function fillLineWandtattoo($product, $line)
    {
        $combinationId = 0;
        $combinationLines = [];
        $sizeIds = $product->getSizeIds();
        $prices = $product->getPricesMatched();
        $basePrice = $product->getBasePriceUnformatted();

        foreach ($sizeIds as $size) {
            if (isset($prices[$size]) && $prices[$size] != 0.00) {
                $line["price"] = $prices[$size] + $basePrice;
            }

            $line["size"] = $product->getSize($size);
            $line["id"] = "wandtattoo" . "_" . $product->getId() . "_" . $combinationId;
            $line["material"] = "Wandtattoofolie";
            $line["google_product_category"] = "3221";

            $combinationId++;
            $combinationLines[] = $line;
        }

        return $combinationLines;
    }

    private function fillLineTextil($product, $line)
    {
        $combinationId = 0;
        $combinationLines = [];

        if ($product->getIsColorable() == 1) {
            foreach ($product->getAttributes() as $color) {
                $line["price"] = $product->getPrice();
                $line["color"] = $product->textilColors[$combinationId]["name"];

                $line["id"] = "textil" . "_" . $product->getId() . "_" . $combinationId;
                $line["google_product_category"] = "505384";

                $combinationLines[] = $line;
                $combinationId++;
            }
        } else {
            $line["price"] = $product->getPrice();
            $line["color"] = "zweifarbig";
            $line["google_product_category"] = "505384";

            $line["id"] = "textil" . "_" . $product->getId() . "_" . $combinationId;
            return [$line];
        }

        return $combinationLines;
    }

    /**
     * generates the csv file by using built in functions from php to avoid manual
     * handling of escape characters
     * https://stackoverflow.com/questions/4617935/is-there-a-way-to-include-commas-in-csv-columns-without-breaking-the-formatting
     */
    private function generateFile(array $lines, string $filename): void
    {
        $path = "generated/";
        $file = fopen($path . $filename, 'w');

        $firstLine = array_keys($this->line);
        $lines = [$firstLine, ...$lines];

        foreach ($lines as $entry) {
            fputcsv($file, $entry);
        }

        fclose($file);
    }

    public function getFilename(): String
    {
        return $this->currentFilename;
    }

    private static function getFirstImageLink($product)
    {
        $prestashopConnection = new PrestashopConnection();

        if ($product->getIdProduct() == 0) {
            return null;
        }

        try {
            $xml = $prestashopConnection->getXML("images/products/" . $product->getIdProduct());
            $resources = $xml->children()->children();
            $declination = $resources->declination;
            $image = $declination[0];
            $id = (int) $image->attributes()->id;

            return "https://klebefux.de/auftragsbearbeitung/images.php?product=" . $product->getIdProduct() . "&image=" . $id;
        } catch (\PrestaShopWebserviceException $e) {
            echo 'Error:' . $e->getMessage();
        }
    }

    public static function createExport()
    {
        $export = new ExportFacebook();
        $export->generateCSV();
        $filename = $export->getFilename();
        $fileLink = $_ENV["REWRITE_BASE"] . "files/assets/forms/generated/" . $filename;

        JSONResponseHandler::sendResponse([
            "status" => "successful",
            "file" => $fileLink,
            "errorList" => "", //$errorList,
        ]);
    }
}
