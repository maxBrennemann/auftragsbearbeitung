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
        $line = [];
        $line["availability"] = "In Stock";
        $line["condition"] = "New";
        $line["brand"] = "klebefux";
        $line["shipping_weight"] = "0.5kg";

        foreach ($types as $index => $type) {
            $line["item_group_id"] = $type . $stickerImage->getId();
            $line["title"] = $stickerImage->getAltTitle($type);
            $line["description"] = $stickerImage->getDescriptions($index + 1)["long"];

            $line["link"] = "";
            $line["image_link"] = "";

            $combinations = $stickerImage->getProductCombinations($type);
            foreach ($combinations as $key => $combination) {
                $line["id"] = $type . "_" . $stickerImage->getId() . "_" . $key;
                $line["price"] = $combination["price"];
                $line["color"] = $combination["color"];

                if (isset($combination["price"])) {
                    $line["size"] = $combination["price"];
                } else {
                    $line["size"] = "";
                }
                
                $line["material"] = "";//$combination["material"];
                $this->storeCSV($line);
            }
        }
    }

}

?>
