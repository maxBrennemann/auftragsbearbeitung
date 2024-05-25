<?php

require_once("classes/routes/Routes.php");

require_once("classes/project/Produkt.php");
require_once("classes/project/AttributeGroup.php");
require_once("classes/front/CategoryTree.php");

class ProductRoutes extends Routes {

    /**
     * @uses AttributeGroup::getGroups()
     * @uses AttributeGroup::getAttributes()
     * 
     * @uses CategoryTree::getParsedCategories()
     */
    protected static $getRoutes = [
        "/attribute/group/{id}" => "AttributeGroup::getAttributes",
        "/attribute/groups" => "AttributeGroup::getGroups",

        "/category" => "CategoryTree::getParsedCategories",
    ];

    /**
     * @uses Produkt::createProduct()
     * @uses Produkt::addSource()
     * 
     * @uses AttributeGroup::addAttributeGroup()
     * @uses AttributeGroup::addAttribute()
     * 
     * @uses Category::addNewCategory()
     */
    protected static $postRoutes = [
        "/product" => "Produkt::createProduct", // TODO: schauen, was hier generiert wurde "ProductController@createProduct",
        "/product/source" => "Produkt::addSource",

        "/attribute" => "AttributeGroup::addAttributeGroup",
        "/attribute/{id}/value" => "AttributeGroup::addAttribute",

        "/category" => "Category::addNewCategory",
    ];

    /**
     * @uses Produkt::update()
     * 
     * @uses Category::updateCategory()
     */
    protected static $putRoutes = [
        "/product/{id}/{type}" => "Produkt::update",

        "/category/{id}" => "Category::updateCategory",
    ];

    public function __construct() {
        parent::__construct();
    }

}
