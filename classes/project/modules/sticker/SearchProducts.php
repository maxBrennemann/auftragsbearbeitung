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

}

?>