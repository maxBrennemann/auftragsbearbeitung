<?php

use ExportFacebook as GlobalExportFacebook;

require_once("classes/project/StickerImage.php");

class ExportFacebook {

    private $csv;
    private $file;

    function __construct() {
        $this->file = fopen('files/res/form/modules/sticker/catalog_products.csv', 'a');
        //$this->readCSV();
    }

    public static function exportAll() {
        $allProducts = DBAccess::selectQuery("SELECT id FROM module_sticker_sticker_data");
        foreach ($allProducts as $product) {
            $id = $product["id"];
            $sticker = new StickerImage($id);
            $export = new static();
            $export->addProduct($sticker);
        }
    }

    private function readCSV() {
        $csvString = file_get_contents($this->file);
        $this->csv = str_getcsv($csvString);
    }

    /**
     * saves new data line by line to the csv file
     */
    private function storeCSV($line) {
        fputcsv($this->file, $line);
    }

    public function addProduct(StickerImage $stickerImage) {
        $line = [
            "id" => "",
            "title" => "",
            "description" => "",
            "availability" => "",
            "condition" => "",
            "price" => "",
            "link" => "",
            "image_link" => "",
            "brand" => "",
            "item_group_id" => "",
            "color" => "",
            "size" => "",
            "material" => "",
            "shipping_weight" => "",
        ];

        $line["availability"] = "In Stock";
        $line["condition"] = "New";
        $line["brand"] = "klebefux";
        $line["shipping_weight"] = "0.5kg";

        $this->generateAufkleber($stickerImage, $line);
        $this->generateWandtattoo($stickerImage, $line);
        $this->generateTextil($stickerImage, $line);
    }

    private function generateAufkleber($stickerImage, $line) {
        $type = "aufkleber";
        if ($stickerImage->data["is_plotted"] == 0) {
            return;
        }

        $line["item_group_id"] = $type . $stickerImage->getId();
        $line["title"] = $stickerImage->getName();
        $line["description"] = "Unsere Aufkleber und Textilien sind keine Lagerware. Diese werden nach der Bestellung individuell für Dich angefertigt. " . $stickerImage->getDescriptions(1)["long"];

        $line["link"] = $stickerImage->getShopProducts($type, "link");
        $ids = $stickerImage->getDefaultImage($type);
        $line["image_link"] = SHOPURL . "/auftragsbearbeitung/images.php?product={$ids["id"]}&image={$ids["image"]}";

        $combinations = $stickerImage->getProductCombinations($type);
        foreach ($combinations as $key => $combination) {
            $line["id"] = $type . "_" . $stickerImage->getId() . "_" . $key;
            $line["price"] = $combination["price"];
            $line["color"] = $combination["color"];

            if (isset($combination["price"])) {
                $line["size"] = $combination["size"];
            } else {
                $line["size"] = "";
            }
            
            $line["material"] = "";//$combination["material"];
            $this->storeCSV($line);
        }
    }
    
    private function generateWandtattoo($stickerImage, $line) {
        $type = "wandtattoo";
        if ($stickerImage->data["is_walldecal"] == 0) {
            return;
        }

        $line["item_group_id"] = $type . $stickerImage->getId();
        $line["title"] = $stickerImage->getName();
        $line["description"] = "Unsere Aufkleber und Textilien sind keine Lagerware. Diese werden nach der Bestellung individuell für Dich angefertigt. " . $stickerImage->getDescriptions(2)["long"];

        $line["link"] = $stickerImage->getShopProducts($type, "link");
        $ids = $stickerImage->getDefaultImage($type);
        $line["image_link"] = SHOPURL . "/auftragsbearbeitung/images.php?product={$ids["id"]}&image={$ids["image"]}";

        $combinations = $stickerImage->getProductCombinations($type);
        foreach ($combinations as $key => $combination) {
            $line["id"] = $type . "_" . $stickerImage->getId() . "_" . $key;
            $line["price"] = $combination["price"];
            $line["color"] = $combination["color"];

            if (isset($combination["price"])) {
                $line["size"] = $combination["size"];
            } else {
                $line["size"] = "";
            }
            
            $line["material"] = "";//$combination["material"];
            $this->storeCSV($line);
        }
    }

    private function generateTextil($stickerImage, $line) {
        $type = "textil";
        if ($stickerImage->data["is_shirtcollection"] == 0) {
            return;
        }

        $line["item_group_id"] = $type . $stickerImage->getId();
        $line["title"] = $stickerImage->getName();
        $line["description"] = "Unsere Aufkleber und Textilien sind keine Lagerware. Diese werden nach der Bestellung individuell für Dich angefertigt. " . $stickerImage->getDescriptions(3)["long"];

        $line["link"] = $stickerImage->getShopProducts($type, "link");
        $ids = $stickerImage->getDefaultImage($type);
        $line["image_link"] = SHOPURL . "/auftragsbearbeitung/images.php?product={$ids["id"]}&image={$ids["image"]}";

        $combinations = $stickerImage->getProductCombinations($type);
        foreach ($combinations as $key => $combination) {
            $line["id"] = $type . "_" . $stickerImage->getId() . "_" . $key;
            $line["price"] = $combination["price"];
            $line["color"] = $combination["color"];

            if (isset($combination["price"])) {
                $line["size"] = $combination["size"];
            } else {
                $line["size"] = "";
            }
            
            $line["material"] = "";//$combination["material"];
            $this->storeCSV($line);
        }

    }

}

?>
