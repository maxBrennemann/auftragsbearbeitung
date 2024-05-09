<?php

require_once("classes/routes/Routes.php");

require_once("classes/project/Produkt.php");

class ProductRoutes extends Routes {

    protected static $getRoutes = [
        
    ];

    /**
     * @uses ProductController::createProduct()
     * @uses ProductController::addSource()
     */
    protected static $postRoutes = [
        "/product" => "Produkt::createProduct", // TODO: schauen, was hier generiert wurde "ProductController@createProduct",
        "/product/source" => "Produkt::addSource"
    ];

    public function __construct() {
        parent::__construct();
    }

}
