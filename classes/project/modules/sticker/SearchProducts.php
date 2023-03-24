<?php

require_once('classes/project/modules/sticker/PrestashopConnection.php');

class SearchProducts extends PrestashopConnection {

    function __construct() {
        parent::__construct();
    }

    public static function search($query, $searchTypes) {
        $searchProduct = new SearchProducts();
        $products = [];
        $ids = [];
        
        foreach ($searchTypes as $search) {
            $url = "products/?display=[id,name,link_rewrite]&filter[$search]=%[$query]%";
            $xml = $searchProduct->getXML($url);

            $productsReference = $xml->children()->children();
            foreach ($productsReference as $product) {
                $id = (int) $product->id;
                $title = (String) $product->name->language[0];
                $link = SHOPURL . "/home/$id-" . (String) $product->link_rewrite->language[0] . ".html";
                if (!in_array($id, $ids)) {
                    $products[] = [
                        'id' => $id,
                        'name' => $title,
                        'link' => $link,
                    ];
                    $ids[] = $id;
                }
            }
        }

        return $products;
    }

    public static function getProductsByStickerId($idSticker) {
        $searchProduct = new SearchProducts();
        $xml = $searchProduct->getXML("products?filter[reference]=$idSticker");

        $productMatches = [];
        $foundProducts = sizeof($xml->children()->children());
        $productLinks = [];

        foreach ($xml->children()->children() as $product) {
            $categories = [];
            $productId = (int) $product["id"];

            $xmlProduct = $searchProduct->getXML("products/$productId");
            $title = (String) $xmlProduct->children()->children()->name->language[0];
            $link = SHOPURL . "/home/$productId-" . (String) $xmlProduct->children()->children()->link_rewrite->language[0] . ".html";
            $categoriesXML = $xmlProduct->children()->children()->associations->categories->category;

            array_push($productLinks, $link);

            foreach ($categoriesXML as $category) {
                array_push($categories, (int) $category->id);
            }

            /*
             * TODO: hardcoded entfernen
             * Kategorie 25 ist die Textilkategorie, 
             * Kategorie ist die Wandtattookategorie,
             * Kategorie 13 ist die Aufkleberkategorie
             */
            $title = htmlspecialchars($title);

            if (in_array(25, $categories)) {
                $productMatches["textil"] = ["id" => $productId, "title" => $title, "link" => $link];
            } else if (in_array(62, $categories)) {
                $productMatches["wandtattoo"] = ["id" => $productId, "title" => $title, "link" => $link];
            } else if (in_array(13, $categories)) {
                $productMatches["aufkleber"] = ["id" => $productId, "title" => $title, "link" => $link];
            }
        }

        return ["products" => $productMatches, "matches" => $foundProducts, "allLinks" => $productLinks];
    }

}

?>