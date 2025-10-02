<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class ProductRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Project\AttributeGroup::getGroups()
     * @uses \Src\Classes\Project\AttributeGroup::getAttributes()
     *
     * @uses \Src\Classes\Project\Category::getJSONOneLayer()
     * @uses \Src\Classes\Project\Category::getJSONTree()
     */
    protected static $getRoutes = [
        "/attribute/group/{id}" => [\Src\Classes\Project\AttributeGroup::class, "getAttributes"],
        "/attribute/groups" => [\Src\Classes\Project\AttributeGroup::class, "getGroups"],

        "/category" => [\Src\Classes\Project\Category::class, "getJSONOneLayer"],
        "/category/tree" => [\Src\Classes\Project\Category::class, "getJSONTree"],
    ];

    /**
     * @uses \Src\Classes\Project\Produkt::createProduct()
     * @uses \Src\Classes\Project\Produkt::addSource()
     * @uses \Src\Classes\Project\Produkt::addCombinations()
     * @uses \Src\Classes\Project\Produkt::addFiles()
     *
     * @uses \Src\Classes\Project\AttributeGroup::addAttributeGroup()
     * @uses \Src\Classes\Project\AttributeGroup::addAttribute()
     *
     * @uses \Src\Classes\Project\Category::addNewCategory()
     */
    protected static $postRoutes = [
        "/product" => [\Src\Classes\Project\Produkt::class, "createProduct"],
        "/product/source" => [\Src\Classes\Project\Produkt::class, "addSource"],
        "/product/{id}/combinations" => [\Src\Classes\Project\Produkt::class, "addCombinations"],
        "/product/{id}/add-files" => [\Src\Classes\Project\Produkt::class, "addFiles"],

        "/attribute" => [\Src\Classes\Project\AttributeGroup::class, "addAttributeGroup"],
        "/attribute/{id}/value" => [\Src\Classes\Project\AttributeGroup::class, "addAttribute"],

        "/category" => [\Src\Classes\Project\Category::class, "addNewCategory"],
    ];

    /**
     * @uses \Src\Classes\Project\Produkt::update()
     * @uses \Src\Classes\Project\Produkt::addCombinations()
     *
     * @uses \Src\Classes\Project\AttributeGroup::updateAttribute()
     * @uses \Src\Classes\Project\AttributeGroup::updatePositions()
     *
     * @uses \Src\Classes\Project\Category::updateCategory()
     */
    protected static $putRoutes = [
        "/product/{id}/type/{type}" => [\Src\Classes\Project\Produkt::class, "update"],
        "/product/{id}/combinations" => [\Src\Classes\Project\Produkt::class, "addCombinations"],

        "/attribute/{id}/value/{valueId}" => [\Src\Classes\Project\AttributeGroup::class, "updateAttribute"],
        "/attribute/{id}/positions" => [\Src\Classes\Project\AttributeGroup::class, "updatePositions"],

        "/category/{id}" => [\Src\Classes\Project\Category::class, "updateCategory"],
    ];
}
