<?php

require_once("classes/project/modules/sticker/StickerCollection.php");
require_once('classes/project/modules/sticker/PrestashopConnection.php');

/**
 * steps:
 * 1: no more static
 * 2: use stored images so that there is no need for requests
 * 3: store images???
 * 4: file versioning with datetimes
 * 5: automatic export via cronjobs
 */
class ExportFacebook extends PrestashopConnection {

    private static $csv;
    private static $file;

    private $idProducts;

    private $line = [
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

    private static $errorList = [];

    function __construct() {
        $query = "SELECT `id` FROM `module_sticker_sticker_data` ORDER BY `id` ASC";
        $this->idProducts = DBAccess::selectQuery($query);
    }

    public function generateCSV() {
        $lines = [];
        foreach ($this->idProducts as $id) {
            $id = (int) $id["id"];

            $stickerCollection = new StickerCollection($id);
            if ($stickerCollection->getAufkleber()->isInShop()) {
                $lines[] = $this->fillLine($stickerCollection->getAufkleber());
            }
    
            if ($stickerCollection->getTextil()->isInShop()) {
                $lines[] = $this->fillLine($stickerCollection->getTextil());
            }
    
            if ($stickerCollection->getWandtattoo()->isInShop()) {
                $lines[] = $this->fillLine($stickerCollection->getWandtattoo());
            }
        }

        $filename = "exportFB_" . date("Y-m-d") . ".csv";
        $this->generateFile($lines, $filename);
    }

    private function fillLine($product): String {
        $type = $product->getType();
        $line = $this->line;
        $line["item_group_id"] = $type . $product->getId();
        $line["title"] = $product->getName();
        $line["description"] = "Unsere Aufkleber und Textilien sind keine Lagerware. Diese werden nach der Bestellung individuell für Dich angefertigt. " . $product->getDescription();
        $line["link"] = $product->getShopLink();
        $line["image_link"] = self::getFirstImageLink($product);

        if ($product instanceof Aufkleber) {
            $this->fillLineAufkleber($product, $line);
        } else if ($product instanceof Wandtattoo) {
            $this->fillLineWandtattoo($product, $line);
        } else if ($product instanceof Textil) {
            $this->fillLineTextil($product, $line);
        }

        return implode(",", $line);
    }

    private function fillLineAufkleber($product, &$line) {

    }

    private function fillLineWandtattoo($product, &$line) {

    }

    private function fillLineTextil($product, &$line) {

    }

    private function generateFile(array $strings, string $filename): void {
        // Open the file for writing, overwriting any existing file with the same name
        $file = fopen($filename, 'w');
    
        // Write each string to the file on a new line
        foreach ($strings as $string) {
            fwrite($file, $string . "\n");
        }
    
        // Close the file
        fclose($file);
    }

    public static function exportAll() {
        $allProducts = DBAccess::selectQuery("SELECT `id` FROM `module_sticker_sticker_data` ORDER BY `id` ASC");
        /* TODO: absolute path must later be parameterized */
        self::$file = fopen('files/res/form/modules/sticker/catalog_products.csv', 'a');

        foreach ($allProducts as $product) {
            $id = (int) $product["id"];
            self::addProduct($id);
        }
        fclose(self::$file);
        return self::$errorList;
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

        if ($stickerCollection->getAufkleber()->isInShop()) {
            self::generate($stickerCollection->getAufkleber(), $line);
        }

        if ($stickerCollection->getTextil()->isInShop()) {
            self::generate($stickerCollection->getTextil(), $line);
        }

        if ($stickerCollection->getWandtattoo()->isInShop()) {
            self::generate($stickerCollection->getWandtattoo(), $line);
        }
    }

    private static function generate($product, $line) {
        $type = $product->getType();
        $line["item_group_id"] = $type . $product->getId();
        $line["title"] = $product->getName();
        $line["description"] = "Unsere Aufkleber und Textilien sind keine Lagerware. Diese werden nach der Bestellung individuell für Dich angefertigt. " . $product->getDescription();
        $line["link"] = $product->getShopLink();
        $line["image_link"] = self::getFirstImageLink($product);

        if ($product instanceof Aufkleber) {
            self::generateAufkleber($product, $line);
        } else if ($product instanceof Wandtattoo) {
            //self::generateWandtattoo($product, $line);
        } else if ($product instanceof Textil) {
            //self::generateTextil($product, $line);
        }
    }

    private static function getFirstImageLink($product) {
        $prestashopConnection = new PrestashopConnection();
        try  {
            $xml = $prestashopConnection->getXML("images/products/" . $product->getIdProduct());
            $resources = $xml->children()->children();
            $declination = $resources->declination;
            $image = $declination[0];
            $id = (int) $image->attributes()->id;
            
            return "https://klebefux.de/auftragsbearbeitung/images.php?product=" . $product->getIdProduct() . "&image=" . $id;
        } catch (PrestaShopWebserviceException $e) {
            echo 'Error:' . $e->getMessage();
        } 
    }

    private static function generateAufkleber($product, &$line) {
        $combinationId = 0;
        $sizeIds = $product->getSizeIds();
        $prices = $product->getPricesMatched();

        foreach ($sizeIds as $size) {

            if (array_key_exists($size, $prices) && $prices[$size] != 0.00) {
                $line["price"] = $prices[$size];
            } else {
                self::$errorList[] = $size;
                continue;
            }

            $line["size"] = $product->getSize($size);

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
