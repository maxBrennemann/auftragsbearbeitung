<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Category
{
    private string $name;
    private int $parent = 0;
    private int $id;

    /** @var Category[] */
    private array $children = [];

    public function __construct(int $id, string $name, int $parent = 0)
    {
        $this->name = $name;
        $this->parent = $parent;
        $this->id = $id;
    }

    public function addChild(Category $categoryNode): void
    {
        $this->children[] = $categoryNode;
    }

    /**
     * adds a new category to the database by the given name and parent
     */
    public static function addNewCategory(): void
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

    /**
     * @return Category[]
     */
    public function getOneLayerArray(): array
    {
        $onelayerarray = [];
        $onelayerarray[] = $this;

        foreach ($this->children as $child) {
            $pushTo = $child->getOneLayerArray();
            foreach ($pushTo as $p) {
                $onelayerarray[] = $p;
            }
        }

        return $onelayerarray;
    }

    /**
     * @return Category[]
     */
    private static function parseCategories(): array
    {
        $categories = DBAccess::selectQuery("SELECT * FROM category");

        $categoryTree = [];
        $nodes = [];

        foreach ($categories as $c) {
            $nodes[$c["id"]] = new Category((int) $c["id"], $c["name"], (int) $c["parent"]);
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

    /**
     * @return array<int, array<mixed>>
     */
    public static function getOneLayerRepresentation(): array
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

    /**
     * @param array<int, Category> $data
     * @return array<int, array<string, mixed>>
     */
    public static function getCategoryTree(array $data): array
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

    public static function getJSONOneLayer(): void
    {
        JSONResponseHandler::sendResponse(self::getOneLayerRepresentation());
    }

    public static function getJSONTree(): void
    {
        $data = self::parseCategories();
        JSONResponseHandler::sendResponse(self::getCategoryTree($data));
    }

    public static function updateCategory(): void
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
