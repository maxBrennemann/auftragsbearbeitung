<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

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
        "/attribute/group/{id}" => [\Classes\Project\AttributeGroup::class, "getAttributes"],
        "/attribute/groups" => [\Classes\Project\AttributeGroup::class, "getGroups"],

        "/category" => [\Classes\Project\Category::class, "getJSONOneLayer"],
        "/category/tree" => [\Classes\Project\Category::class, "getJSONTree"],
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
        "/product" => [\Classes\Project\Produkt::class, "createProduct"],
        "/product/source" => [\Classes\Project\Produkt::class, "addSource"],
        "/product/{id}/combinations" => [\Classes\Project\Produkt::class, "addCombinations"],

        "/attribute" => [\Classes\Project\AttributeGroup::class, "addAttributeGroup"],
        "/attribute/{id}/value" => [\Classes\Project\AttributeGroup::class, "addAttribute"],

        "/category" => [\Classes\Project\Category::class, "addNewCategory"],
    ];

    /**
     * @uses \Classes\Project\Produkt::update()
     * @uses \Classes\Project\Produkt::addCombinations()
     * 
     * @uses \Classes\Project\AttributeGroup::updateAttribute()
     * @uses \Classes\Project\AttributeGroup::updatePositions()
     * 
     * @uses \Classes\Project\Category::updateCategory()
     */
    protected static $putRoutes = [
        "/product/{id}/type/{type}" => [\Classes\Project\Produkt::class, "update"],
        "/product/{id}/combinations" => [\Classes\Project\Produkt::class, "addCombinations"],

        "/attribute/{id}/value/{valueId}" => [\Classes\Project\AttributeGroup::class, "updateAttribute"],
        "/attribute/{id}/positions" => [\Classes\Project\AttributeGroup::class, "updatePositions"],

        "/category/{id}" => [\Classes\Project\Category::class, "updateCategory"],
    ];
}
