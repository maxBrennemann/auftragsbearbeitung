<?php

namespace Classes\Routes;

class ProductRoutes extends Routes
{

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

        "/category" => [\Classes\Front\Category::class, "addNewCategory"],
    ];

    /**
     * @uses \Classes\Project\Produkt::update()
     * @uses \Classes\Project\Produkt::addCombinations()
     * 
     * @uses \Classes\Project\AttributeGroup::updateAttribute()
     * @uses \Classes\Project\AttributeGroup::updatePositions()
     * 
     * @uses \Classes\Front\Category::updateCategory()
     */
    protected static $putRoutes = [
        "/product/{id}/type/{type}" => [\Classes\Project\Produkt::class, "update"],
        "/product/{id}/combinations" => [\Classes\Project\Produkt::class, "addCombinations"],

        "/attribute/{id}/value/{valueId}" => [\Classes\Project\AttributeGroup::class, "updateAttribute"],
        "/attribute/{id}/positions" => [\Classes\Project\AttributeGroup::class, "updatePositions"],

        "/category/{id}" => [\Classes\Front\Category::class, "updateCategory"],
    ];

    public function __construct()
    {
        parent::__construct();
    }
}
