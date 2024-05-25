<?php

class Category
{

    public $name;
    public $parent;
    public $id;

    public $children = [];

    function __construct($name, $parent, $id)
    {
        $this->name = $name;
        $this->parent = (int) $parent;
        $this->id = (int) $id;
    }

    public function addChild($categoryNode)
    {
        array_push($this->children, $categoryNode);
    }

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
        $onelayerarray = array();

        array_push($onelayerarray, $this);

        foreach ($this->children as $child) {
            $pushTo = $child->getOneLayerArray();
            foreach ($pushTo as $p) {
                array_push($onelayerarray, $p);
            }
        }

        return $onelayerarray;
    }
    
}
