<?php

namespace Classes\Front;

class Cart {

    public static function addToCart($productId) {
        if (isset($_SESSION)) {
            $products = isset($_SESSION["cart_Products"]) ? unserialize($_SESSION["cart_Products"]) : [];

            $exists = false;

            foreach ($products as $p) {
                if ($p->getProductId() == $productId) {
                    $p->incrementQuantity();
                    $exists = true;
                }
            }

            if (!$exists) {
                $product = new ProductController($productId);
                array_push($products, $product);
            }

            $_SESSION["cart_Products"] = serialize($products);
        }
    }

}
