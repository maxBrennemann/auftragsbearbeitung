<?php

/* TODO: use new class structure */

require_once("classes/project/StickerImage.php");

class ExportFacebook {

    private static $csv;
    private static $file;

    function __construct() {}

    public static function exportAll() {
        $allProducts = DBAccess::selectQuery("SELECT `id` FROM `module_sticker_sticker_data`");
        /* TODO: absolute path must later be parameterized */
        self::$file = fopen('files/res/form/modules/sticker/catalog_products.csv', 'a');

        foreach ($allProducts as $product) {
            $id = (int) $product["id"];
            $sticker = new StickerImage($id);
            self::addProduct($sticker);
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

    public static function addProduct(StickerImage $stickerImage) {
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

        self::generateAufkleber($stickerImage, $line);
        self::generateWandtattoo($stickerImage, $line);
        self::generateTextil($stickerImage, $line);
    }

    private static function generateAufkleber($stickerImage, $line) {
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
            
            $line["material"] = ""; //$combination["material"];
            self::storeCSV($line);
        }
    }
    
    private static function generateWandtattoo($stickerImage, $line) {
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
            
            $line["material"] = ""; //$combination["material"];
            self::storeCSV($line);
        }
    }

    private static function generateTextil($stickerImage, $line) {
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
            
            $line["material"] = ""; //$combination["material"];
            self::storeCSV($line);
        }

    }

}

?>
