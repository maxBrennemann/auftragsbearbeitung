<?php

namespace Classes\Routes;

class ProductRoutes extends Routes {

    /**
     * @uses AttributeGroup::getGroups()
     * @uses AttributeGroup::getAttributes()
     * 
     * @uses Category::getJSONOneLayer()
     * @uses Category::getJSONTree()
     */
    protected static $getRoutes = [
        "/attribute/group/{id}" => "AttributeGroup::getAttributes",
        "/attribute/groups" => "AttributeGroup::getGroups",

        "/category" => "Category::getJSONOneLayer",
        "/category/tree" => "Category::getJSONTree",
    ];

    /**
     * @uses Produkt::createProduct()
     * @uses Produkt::addSource()
     * @uses Produkt::addCombinations()
     * 
     * @uses AttributeGroup::addAttributeGroup()
     * @uses AttributeGroup::addAttribute()
     * 
     * @uses Category::addNewCategory()
     */
    protected static $postRoutes = [
        "/product" => "Produkt::createProduct", // TODO: schauen, was hier generiert wurde "ProductController@createProduct",
        "/product/source" => "Produkt::addSource",
        "/product/{id}/combinations" => "Produkt::addCombinations",

        "/attribute" => "AttributeGroup::addAttributeGroup",
        "/attribute/{id}/value" => "AttributeGroup::addAttribute",

        "/category" => "Category::addNewCategory",
    ];

    /**
     * @uses Produkt::update()
     * @uses Produkt::addCombinations()
     * 
     * @uses AttributeGroup::updateAttribute()
     * @uses AttributeGroup::updatePositions()
     * 
     * @uses Category::updateCategory()
     */
    protected static $putRoutes = [
        "/product/{id}/type/{type}" => "Produkt::update",
        "/product/{id}/combinations" => "Produkt::addCombinations",

        "/attribute/{id}/value/{valueId}" => "AttributeGroup::updateAttribute",
        "/attribute/{id}/positions" => "AttributeGroup::updatePositions",

        "/category/{id}" => "Category::updateCategory",
    ];

    public function __construct() {
        parent::__construct();
    }

}
