<?php

require_once("classes/routes/Routes.php");

require_once("classes/project/Produkt.php");
require_once("classes/project/AttributeGroup.php");

class ProductRoutes extends Routes {

    /**
     * @uses AttributeGroup::getGroups()
     * @uses AttributeGroup::getAttributes()
     */
    protected static $getRoutes = [
        "/attribute/group/{id}" => "AttributeGroup::getAttributes",
        "/attribute/groups" => "AttributeGroup::getGroups",
    ];

    /**
     * @uses Produkt::createProduct()
     * @uses Produkt::addSource()
     * 
     * @uses AttributeGroup::addAttributeGroup()
     * @uses AttributeGroup::addAttribute()
     */
    protected static $postRoutes = [
        "/product" => "Produkt::createProduct", // TODO: schauen, was hier generiert wurde "ProductController@createProduct",
        "/product/source" => "Produkt::addSource",

        "/attribute" => "AttributeGroup::addAttributeGroup",
        "/attribute/{id}/value" => "AttributeGroup::addAttribute",
    ];

    /**
     * @uses Produkt::update()
     */
    protected static $putRoutes = [
        "/product/{id}/{type}" => "Produkt::update",
    ];

    public function __construct() {
        parent::__construct();
    }

}
