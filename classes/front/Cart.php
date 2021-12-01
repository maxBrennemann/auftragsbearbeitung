<?php

require_once('classes/front/ProductController.php');

class Cart {

    public static function addToCart($productId) {
        if (isset($_SESSION)) {
            $products = isset($_SESSION["cart_Products"]) ? unserialize($_SESSION["cart_Products"]) : [];

            $product = new ProductController($productId);
            array_push($products, $product);

            $_SESSION["cart_Products"] = serialize($products);
        }
    }

}

?>