<?php

require_once("classes/project/StickerImage.php");

class ExportFacebook {

    private $csv;
    private $file;

    function __construct() {
        $this->file = fopen('files/res/form/modules/sticker/catalog_products.csv', 'a');
        //$this->readCSV();
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
        $types = ["aufkleber", "wandtattoo", "textil"];
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

        foreach ($types as $index => $type) {
            $doImport = false;
            switch ($type) {
                case "aufkleber":
                    if ($stickerImage->data["is_aufkleber"] == 1) {
                        $doImport = true;
                    }
                    break;
                case "wandtattoo":
                    if ($stickerImage->data["is_wandtattoo"] == 1) {
                        $doImport = true;
                    }
                    break;
                case "textil":
                    if ($stickerImage->data["is_textil"] == 1) {
                        $doImport = true;
                    }
                    break;
            }

            if ($doImport) {
                $line["item_group_id"] = $type . $stickerImage->getId();
                $line["title"] = $stickerImage->getName();
                $line["description"] = "Unsere Aufkleber und Textilien sind keine Lagerware. Diese werden nach der Bestellung individuell für Dich angefertigt. " . $stickerImage->getDescriptions($index + 1)["long"];
    
                $line["link"] = $stickerImage->getShopProducts($type, "link");
                $ids = $stickerImage->getDefaultImage($type);
                $line["image_link"] = "https://klebefux.de/auftragsbearbeitung/images.php?product={$ids["id"]}&image={$ids["image"]}";
    
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
    }

}

?>
