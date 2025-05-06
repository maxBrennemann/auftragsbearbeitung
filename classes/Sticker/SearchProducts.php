<?php

namespace Classes\Sticker;

use Classes\Protocol;

class SearchProducts extends PrestashopConnection
{

    function __construct()
    {
        parent::__construct();
    }

    public static function search($query, $searchTypes)
    {
        $searchProduct = new SearchProducts();
        $products = [];
        $ids = [];

        foreach ($searchTypes as $search) {
            $url = "products/?display=[id,name,link_rewrite]&filter[$search]=%[$query]%";
            try {
                $xml = $searchProduct->getXML($url);

                $productsReference = $xml->children()->children();
                foreach ($productsReference as $product) {
                    $id = (int) $product->id;
                    $title = (string) $product->name->language[0];
                    $link = $_ENV["SHOPURL"] . "/home/$id-" . (string) $product->link_rewrite->language[0] . ".html";
                    if (!in_array($id, $ids)) {
                        $products[] = [
                            'id' => $id,
                            'name' => $title,
                            'link' => $link,
                        ];
                        $ids[] = $id;
                    }
                }
            } catch (\PrestaShopWebserviceException $e) {
                echo $e->getMessage();
            }
        }

        return $products;
    }

    /**
     * TODO: funktion muss alte data mit berücksichtigen und alt title nicht überschreiben
     */
    public static function getProductsByStickerId($idSticker): array|null
    {
        $searchProduct = new SearchProducts();
        $productMatches = [];
        $foundProducts = 0;
        $productLinks = [];

        try {
            $xml = $searchProduct->getXML("products?filter[reference]=$idSticker");
            $foundProducts = sizeof($xml->children()->children());

            foreach ($xml->children()->children() as $product) {
                $categories = [];
                $productId = (int) $product["id"];

                if ($productId == 0) {
                    continue;
                }

                $xmlProduct = $searchProduct->getXML("products/$productId");
                $title = (string) $xmlProduct->children()->children()->name->language[0];
                $statusActive = (int) $xmlProduct->children()->children()->active;
                $link = $_ENV["SHOPURL"] . "/home/$productId-" . (string) $xmlProduct->children()->children()->link_rewrite->language[0] . ".html";
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

                $data = [
                    "id" => $productId,
                    "title" => $title,
                    "link" => $link,
                    "altTitle" => "",
                    "status" => $statusActive,
                ];

                if (in_array(25, $categories)) {
                    $productMatches["textil"] = $data;
                } else if (in_array(62, $categories)) {
                    $productMatches["wandtattoo"] = $data;
                } else if (in_array(13, $categories)) {
                    $productMatches["aufkleber"] = $data;
                }
            }
        } catch (\PrestaShopWebserviceException $e) {
            Protocol::write($e->getMessage());
            return null;
        }

        return ["products" => $productMatches, "matches" => $foundProducts, "allLinks" => $productLinks];
    }
}
