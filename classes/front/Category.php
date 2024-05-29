<?php

use MatthiasMullie\Minify\JS;

class Category
{

    private string $name;
    private int $parent = 0;
    private int $id;

    private array $children = [];

    private static array $categories = [];

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

    private static function parseCategories() {
        $categories = DBAccess::selectQuery("SELECT * FROM category");

        $categoryNodes = [];
        self::$categories = [];

        foreach ($categories as $c) {
            $categoryNodes[$c["id"]] = new Category($c["id"], $c["name"], $c["parent"]);
        }

        foreach ($categoryNodes as $node) {
            if ($node->parent == 0) {
                array_push(self::$categories, $node);
            } else {
                $categoryNodes[$node->parent]->addChild($node);
            }
        }

        return self::$categories;
    }

    public static function getOneLayerRepresentation()
    {
        self::parseCategories();

        $onelayerarray = [];

        foreach (self::$categories as $category) {
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

    public static function getJSONOneLayer() {
        return JSONResponseHandler::sendResponse(self::getOneLayerRepresentation());
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
