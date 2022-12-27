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
            $line["item_group_id"] = $type . $stickerImage->getId();
            $line["title"] = $stickerImage->getName();
            $line["description"] = "test";//$stickerImage->getDescriptions($index + 1)["long"];

            $line["link"] = "https://klebefux.de/home/721-aufkleber-mir-glangts-i-geh-in-die-berge.html";
            $line["image_link"] = "https://klebefux.de/1363-thickbox_default/aufkleber-mir-glangts-i-geh-in-die-berge.jpg";

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

?>
