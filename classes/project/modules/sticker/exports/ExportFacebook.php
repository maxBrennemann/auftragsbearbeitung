<?php

require_once("classes/project/modules/sticker/StickerCollection.php");
require_once('classes/project/modules/sticker/PrestashopConnection.php');

class ExportFacebook extends PrestashopConnection {

    private static $csv;
    private static $file;

    function __construct() {}

    public static function exportAll() {
        $allProducts = DBAccess::selectQuery("SELECT `id` FROM `module_sticker_sticker_data`");
        /* TODO: absolute path must later be parameterized */
        self::$file = fopen('files/res/form/modules/sticker/catalog_products.csv', 'a');

        foreach ($allProducts as $product) {
            $id = (int) $product["id"];
            self::addProduct($id);
        }
        fclose(self::$file);
    }

    private static function readCSV() {
        $csvString = file_get_contents(self::$file);
        self::$csv = str_getcsv($csvString);
    }

    /**
     * saves new data line by line to the csv file
     */
    private static function storeCSV($line) {
        fputcsv(self::$file, $line);
    }

    public static function addProduct(int $id) {
        $line = [
            "id" => "",
            "title" => "",
            "description" => "",
            "availability" => "In Stock",
            "condition" => "New",
            "price" => "",
            "link" => "",
            "image_link" => "",
            "brand" => "klebefux",
            "item_group_id" => "",
            "color" => "",
            "size" => "",
            "material" => "",
            "shipping_weight" => "0.5kg",
        ];

        $stickerCollection = new StickerCollection($id);
        
        foreach ($stickerCollection as $product) {
            if ($product->isInShop()) {
                self::generate($product, $line, $product->getType());
            }
        }
    }

    private static function generate($product, $line, $type) {
        $line["item_group_id"] = $type . $product->getId();
        $line["title"] = $product->getName();
        $line["description"] = "Unsere Aufkleber und Textilien sind keine Lagerware. Diese werden nach der Bestellung individuell für Dich angefertigt. " . $product->getDescription();
        $line["link"] = $product->getShopLink();
        $line["image_link"] = self::getFirstImageLink($product);

        if ($product instanceof Aufkleber) {
            self::generateAufkleber($product, $line);
        } else if ($product instanceof Wandtattoo) {
            self::generateWandtattoo($product, $line);
        } else if ($product instanceof Textil) {
            self::generateTextil($product, $line);
        }
    }

    private static function getFirstImageLink($product) {
        $prestashopConnection = new PrestashopConnection();
        try  {
            $xml = $prestashopConnection->getXML("images/products/" . $product->getIdProduct());
            $resources = $xml->children()->children();
            $id = (int) $resources->tag->attributes()->id;
            return "https://klebefux.de/auftragsbearbeitung/images.php?product=" . $product->getIdProduct() . "&image=" . $id;
        } catch (PrestaShopWebserviceException $e) {
            echo 'Error:' . $e->getMessage();
        } 
    }

    private static function generateAufkleber($product, $line) {
        // all attributes zusammentragen
        // prices zusammentragen
        // über combinations iteraten
        // jeweilse eine zeile hinzufügen mit dem price match von stickerCombination        


        $combinationId = 0;
        foreach ($product->getSizeToPrice() as $price => $size) {
            $line["price"] = $price;
            $line["size"] = $size;

            if ($product->getIsMultipart()) {
                $line["id"] = "aufkleber" . "_" . $product->getId() . "_" . $combinationId;

                self::storeCSV($line);
                $combinationId++;
                continue;
            }

            /* iterate over all colors and add variants */
            foreach ($product->getColors() as $color) {
                $line["color"] = $color;

                if ($product->getIsShortTimeSticker()) {
                    $line["id"] = "aufkleber" . "_" . $product->getId() . "_" . $combinationId;
                    $line["material"] = "Werbefolie";

                    self::storeCSV($line);
                    $combinationId++;
                }
                
                if ($product->getIsLongTimeSticker()) {
                    $line["id"] = "aufkleber" . "_" . $product->getId() . "_" . $combinationId;
                    $line["material"] = "Hochleistungsfolie";

                    self::storeCSV($line);
                    $combinationId++;
                }
            }
        }
    }
    
    private static function generateWandtattoo($product, $line) {
        $combinationId = 0;
        foreach ($product->getSizeToPrice() as $price => $size) {
            $line["price"] = $price;
            $line["size"] = $size;
            $line["material"] = "Wandtattoofolie";

            if ($product->getIsMultipart()) {
                $line["id"] = "wandtattoo" . "_" . $product->getId() . "_" . $combinationId;

                self::storeCSV($line);
                $combinationId++;
                continue;
            }

            /* iterate over all colors and add variants */
            foreach ($product->getColors() as $color) {
                $line["color"] = $color;
                $line["id"] = "wandtattoo" . "_" . $product->getId() . "_" . $combinationId;

                self::storeCSV($line);
                $combinationId++;
            }
        }
    }

    private static function generateTextil($product, $line) {
        $combinationId = 0;
        foreach ($product->getColors() as $color) {
            $line["price"] = $product->getPrice();
            $line["color"] = $color;

            $line["id"] = "textil" . "_" . $product->getId() . "_" . $combinationId;

            self::storeCSV($line);
            $combinationId++;
        }

    }

}

?>
