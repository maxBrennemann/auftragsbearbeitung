<?php

require_once("classes/project/PrestaCommunicater.php");

class ProductCrawler extends PrestaCommunicater {

    /**
     * alle produkte durchgehen
     * alle ids checken und wenn nicht existert, dann wird ein neuer eintrag erstellt
     * daten werden eingetragen
     */

    
    function __construct() {
        parent::__construct();
    }

    public function crawlAll() {
        $xml = $this->getXML("products");
        $products = $xml->products->product;

        foreach ($products as $product) {
            $idProduct = $product["id"];
            $productXml = $this->getXML("products/$idProduct");

            $productData = $productXml->product;
            $productNumber = (int) $productData->reference;

            if ($productNumber != null || $productNumber != 0) {
                $checkIfExists = DBAccess::selectQuery("SELECT * FROM `module_sticker_sticker_data` WHERE id = $productNumber LIMIT 1");

                if ($checkIfExists != null) {
                    $idSticker = $checkIfExists[0]["id"];
                    echo "product $productNumber exists as sticker $idSticker";
                    $category = $this->getCategory($productData);
                    $this->updateCategory($productNumber, $category);
                } else {
                    $this->analyseProduct($productData);
                }
            }
        }
    }

    private function analyseProduct($productData) {
        $id = (int) $productData->reference;
        $title = (String) $productData->name->language[0];
        $category = $this->getCategory($productData);

        if ($category == 0) {
            return;
        }

        $creationDate = $productData->date_add;
        $creationDate = date("Y-m-d", strtotime($creationDate));

        $query = "REPLACE INTO `module_sticker_sticker_data` (`id`, `name`, `creation_date`) VALUES ($id, '$title', '$creationDate')";

        DBAccess::updateQuery($query);
        $this->updateCategory($id, $category);
    }

    /**
     * checkt, ob der Artikel in einer der drei getrackte Produktkategorien ist
     * 25: Textil
     * 62: Wandtattoo
     * 13: Aufkleber
     */
    private function getCategory($productData) {
        $categories = $productData->associations->categories;
        $idCategories = [];

        foreach ($categories->category as $category) {
            echo "test";
            $id = (int) $category->id;
            array_push($idCategories, $id);
        }

        Protocoll::prettyPrint($idCategories);

        if (in_array(25, $idCategories)) {
            return 25;
        } else if (in_array(62, $idCategories)) {
            return 62;
        } else if (in_array(13, $idCategories)) {
            return 13;
        }

        return 0;
    }

    private function updateCategory($id, $category) {
        switch ($category) {
            case 25:
                $query = "UPDATE `module_sticker_sticker_data` SET is_shirtcollection = 1 WHERE id = $id";
                break;
            case 62:
                $query = "UPDATE `module_sticker_sticker_data` SET is_walldecal = 1 WHERE id = $id";
                break;
            case 13:
                $query = "UPDATE `module_sticker_sticker_data` SET is_plotted = 1 WHERE id = $id";
                break;
            default:
                $query = "";
        }

        DBAccess::updateQuery($query);
    }

}

?>
