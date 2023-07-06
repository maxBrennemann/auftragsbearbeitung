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

    private static $file;
    private $currentFilename;
    private $idProducts;

    /**
     * the line array for the csv
     */
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

    /**
     * gets all sticker ids from database;
     */
    function __construct() {
        $query = "SELECT `id` FROM `module_sticker_sticker_data` ORDER BY `id` ASC";
        $this->idProducts = DBAccess::selectQuery($query);
    }

    /**
     * iterates over all products and creates the three variants if these exist in the shop
     * then it takes the data and puts it into a file
     */
    public function generateCSV() {
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
    public function getSpecificProductExport($idSticker) {
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

    private function fillLine($product): Array {
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
        } else if ($product instanceof Wandtattoo) {
            $variantLines =  $this->fillLineWandtattoo($product, $line);
        } else if ($product instanceof Textil) {
            $variantLines = $this->fillLineTextil($product, $line);
        }

        return $variantLines;
    }

    private function fillLineAufkleber($product, $line) {
        $combinationId = 0;
        $combinationLines = [];
        $sizeIds = $product->getSizeIds();
        $prices = $product->getPricesMatched();

        foreach ($sizeIds as $size) {
            if (isset($prices[$size]) && $prices[$size] != 0.00) {
                $line["price"] = $prices[$size];
            } else {
                self::$errorList[] = $size;
                continue;
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
                $combinationId++;
                $combinationLines[] = $line;
            }
        }

        return $combinationLines;
    }

    private function fillLineWandtattoo($product, $line) {
        $combinationId = 0;
        $combinationLines = [];
        $sizeIds = $product->getSizeIds();
        $prices = $product->getPricesMatched();

        foreach ($sizeIds as $size) {
            if (isset($prices[$size]) && $prices[$size] != 0.00) {
                $line["price"] = $prices[$size];
            } else {
                self::$errorList[] = $size;
                continue;
            }

            $line["size"] = $product->getSize($size);
            $line["id"] = "wandtattoo" . "_" . $product->getId() . "_" . $combinationId;
            $line["material"] = "Wandtattoofolie";
        }

        return $combinationLines;
    }

    private function fillLineTextil($product, $line) {
        $combinationId = 0;
        $combinationLines = [];

        if ($product->getIsColorable() == 1) {
            foreach ($product->getAttributes() as $color) {
                $line["price"] = $product->getPrice();
                $line["color"] = $product->textilColors[$combinationId]["name"];
    
                $line["id"] = "textil" . "_" . $product->getId() . "_" . $combinationId;
    
                $combinationLines[] = $line;
                $combinationId++;
            }
        } else {
            $line["price"] = $product->getPrice();
            $line["color"] = "zweifarbig";

            $line["id"] = "textil" . "_" . $product->getId() . "_" . $combinationId;
            return [$line];
        }

        return $combinationLines;
    }

    private function generateFile(array $lines, string $filename): void {
        $path = "files/generated/fb_export/";
        $file = fopen($path . $filename, 'w');
    
        /* initial line for facebook to recognize columns */
        fwrite($file, "id,title,description,availability,condition,price,link,image_link,brand,item_group_id,color,size,material,shipping_weight" . "\n");

        foreach ($lines as $line) {
            $string = implode(",", $line);
            fwrite($file, $string . "\n");
        }
    
        fclose($file);
    }

    public function getFilename(): String {
        return $this->currentFilename;
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

}

?>
