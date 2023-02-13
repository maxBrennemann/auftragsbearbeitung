<?php

class SearchProducts extends PrestashopConnection {

    function __construct() {
        parent::__construct();
    }

    public static function search($query, $searchTypes) {
        $searchProduct = new SearchProducts();
        $products = [];
        
        foreach ($searchTypes as $search) {
            $url = "products/?display=[id,name]&filter[$search]=%[$query]%";
            $xml = $searchProduct->getXML($url);

            $productsReference = $xml->children()->children();
            foreach ($productsReference as $product) {
                $id = (int) $product->id;
                $title = (String) $product->name->language[0];
                $link = SHOPURL . "/home/$id-" . (String) $product->link_rewrite->language[0] . ".html";
                $products[] = [
                    'id' => $id,
                    'name' => $title,
                    'link' => $link,
                ];
            }
        }

        return $products;
    }

    private static function filterDuplicates() {

    }

    // filter url https://max-web.tech/api/products/?display=[id]&filter[description]=%[aufkleber]%
}

?>