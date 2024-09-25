<?php

namespace Classes\Front;

use Classes\DBAccess;
use Classes\Tools;
use Classes\JSONResponseHandler;

class Category
{

    private string $name;
    private int $parent = 0;
    private int $id;

    private array $children = [];

    function __construct(int $id, string $name, int $parent = 0)
    {
        $this->name = $name;
        $this->parent = $parent;
        $this->id = $id;

        $this->children = [];
    }

    public function addChild($categoryNode)
    {
        array_push($this->children, $categoryNode);
    }

    /**
     * adds a new category to the database by the given name and parent
     */
    public static function addNewCategory()
    {
        $name = Tools::get("name");
        $parent = (int) Tools::get("parent");

        $query = "INSERT INTO category (`name`, `parent`) VALUES (:name, :parent)";
        $id = DBAccess::insertQuery($query, [
            "name" => $name,
            "parent" => $parent
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "id" => $id
        ]);
    }

    public function getOneLayerArray()
    {
        $onelayerarray = [];

        array_push($onelayerarray, $this);

        foreach ($this->children as $child) {
            $pushTo = $child->getOneLayerArray();
            foreach ($pushTo as $p) {
                array_push($onelayerarray, $p);
            }
        }

        return $onelayerarray;
    }

    private static function parseCategories()
    {
        $categories = DBAccess::selectQuery("SELECT * FROM category");

        $categoryTree = [];
        $nodes = [];

        foreach ($categories as $c) {
            $nodes[$c["id"]] = new Category($c["id"], $c["name"], $c["parent"]);
        }

        while (count($nodes) > 0) {
            foreach ($nodes as $id => $node) {
                if ($node->parent == 0) {
                    array_push($categoryTree, $node);
                    unset($nodes[$id]);
                } else {
                    foreach ($categoryTree as $c) {
                        if ($c->id == $node->parent) {
                            $parent = $c;
                            $parent->addChild($node);
                            unset($nodes[$id]);
                            break;
                        }
                    }
                }
            }
        }

        return $categoryTree;
    }

    public static function getOneLayerRepresentation()
    {
        $data = self::parseCategories();

        $onelayerarray = [];

        foreach ($data as $category) {
            $pushTo = $category->getOneLayerArray();
            foreach ($pushTo as $p) {
                array_push($onelayerarray, [
                    "id" => $p->id,
                    "name" => $p->name,
                    "parent" => $p->parent
                ]);
            }
        }

        return $onelayerarray;
    }

    public static function getCategoryTree($data)
    {
        $categoryTree = [];

        foreach ($data as $category) {
            array_push($categoryTree, [
                "id" => $category->id,
                "name" => $category->name,
                "parent" => $category->parent,
                "children" => self::getCategoryTree($category->children)
            ]);
        }

        return $categoryTree;
    }

    public static function getJSONOneLayer()
    {
        return JSONResponseHandler::sendResponse(self::getOneLayerRepresentation());
    }

    public static function getJSONTree()
    {
        $data = self::parseCategories();
        return JSONResponseHandler::sendResponse(self::getCategoryTree($data));
    }

    public static function updateCategory()
    {
        $id = Tools::get("id");
        $name = Tools::get("name");
        $parent = Tools::get("parent");

        $query = "UPDATE category SET `name` = :name, `parent` = :parent WHERE `id` = :id";
        DBAccess::updateQuery($query, [
            "name" => $name,
            "parent" => $parent,
            "id" => $id
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success"
        ]);
    }
}
